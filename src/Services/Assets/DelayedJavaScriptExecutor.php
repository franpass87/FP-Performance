<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

use function add_filter;
use function add_action;
use function is_admin;
use function wp_doing_ajax;
use function preg_match;
use function preg_replace;

/**
 * Delayed JavaScript Executor
 * 
 * Ritarda l'esecuzione di JavaScript non critico fino a interazione utente
 * (scroll, mousedown, touchstart) o timeout
 * 
 * Differenza vs Defer:
 * - Defer: Carica script dopo DOM, esegue subito
 * - Delay: Carica script solo dopo interazione utente
 * 
 * Impatto: FCP/TTI drasticamente migliori
 * 
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class DelayedJavaScriptExecutor
{
    private const OPTION = 'fp_ps_delay_js';
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /** @var LoggerInterface|null Logger (injected) */
    private ?LoggerInterface $logger = null;
    
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
    }
    
    /**
     * Scripts che non devono mai essere ritardati
     */
    private const CRITICAL_SCRIPTS = [
        'jquery-core',
        'jquery',
        'wp-polyfill',
        'regenerator-runtime',
        'wp-a11y',
    ];
    
    /**
     * Impostazioni del servizio
     */
    public function getSettings(): array
    {
        return $this->getOption(self::OPTION, [
            'enabled' => false,
            'timeout' => 5000, // ms - fallback se utente non interagisce
            'excluded_keywords' => [], // Keywords negli URL da NON ritardare
            'delay_inline_scripts' => true, // Ritarda anche script inline
            'trigger_events' => ['scroll', 'mousedown', 'touchstart', 'click'], // Eventi trigger
        ]);
    }
    
    /**
     * Aggiorna impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        // BUGFIX: Valida che settings sia un array valido
        if (!is_array($settings) || empty($settings)) {
            return false;
        }
        
        $current = $this->getSettings();
        $new = array_merge($current, $settings);
        
        // Validazione timeout
        if (isset($new['timeout'])) {
            $new['timeout'] = max(1000, min(30000, (int) $new['timeout']));
        }
        
        // Valida trigger events
        if (isset($new['trigger_events']) && is_array($new['trigger_events'])) {
            $validEvents = ['scroll', 'mousedown', 'touchstart', 'click', 'mousemove', 'keydown'];
            $new['trigger_events'] = array_intersect($new['trigger_events'], $validEvents);
            
            // Almeno un evento
            if (empty($new['trigger_events'])) {
                $new['trigger_events'] = ['scroll', 'mousedown', 'touchstart'];
            }
        }
        
        $result = $this->setOption(self::OPTION, $new);
        
        if ($result) {
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_filter('script_loader_tag', [$this, 'delayScriptTag'], 10);
        remove_action('template_redirect', [$this, 'startBuffer'], 1);
        remove_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
        remove_action('wp_footer', [$this, 'enqueueDelayHandler'], 999);
        
        // Reinizializza
        $this->register();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Non attivare in admin o AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        // Filtra script tags
        add_filter('script_loader_tag', [$this, 'delayScriptTag'], 10, 3);
        
        // Output buffering per ritardare inline scripts
        if (!empty($settings['delay_inline_scripts'])) {
            add_action('template_redirect', [$this, 'startBuffer'], 1);
            add_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
        }
        
        // Enqueue script per gestire delay
        add_action('wp_footer', [$this, 'enqueueDelayHandler'], 999);
        
        $this->log('debug', 'DelayedJavaScriptExecutor registered', $settings);
    }
    
    /**
     * Ritarda script tag
     */
    public function delayScriptTag(string $tag, string $handle, string $src): string
    {
        // Non ritardare script critici
        if (in_array($handle, self::CRITICAL_SCRIPTS, true)) {
            return $tag;
        }
        
        $settings = $this->getSettings();
        
        // Controlla excluded keywords
        if (!empty($settings['excluded_keywords'])) {
            foreach ($settings['excluded_keywords'] as $keyword) {
                if (stripos($src, $keyword) !== false || stripos($handle, $keyword) !== false) {
                    return $tag; // Non ritardare
                }
            }
        }
        
        // BUGFIX: Cambia type a "fpdelayedscript" in modo più robusto
        // Rimuovi type esistente se presente
        $tag = preg_replace('/\stype=["\'][^"\']*["\']/', '', $tag);
        
        // Aggiungi type e data attribute in una volta
        $tag = str_replace('<script', '<script type="fpdelayedscript" data-fp-delayed="true"', $tag);
        
        return $tag;
    }
    
    /**
     * Avvia output buffering
     */
    public function startBuffer(): void
    {
        ob_start([$this, 'processBuffer']);
    }
    
    /**
     * Termina output buffering
     */
    public function endBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }
    
    /**
     * Processa buffer per ritardare inline scripts
     */
    public function processBuffer(string $html): string
    {
        if (empty($html)) {
            return $html;
        }
        
        // BUGFIX: Protezione contro HTML troppo grande (>10MB)
        if (strlen($html) > 10485760) {
            $this->log('warning', 'HTML too large for delay JS processing', ['size' => strlen($html)]);
            return $html;
        }
        
        $settings = $this->getSettings();
        
        if (empty($settings['delay_inline_scripts'])) {
            return $html;
        }
        
        // Pattern per inline scripts
        $pattern = '/<script\b(?![^>]*\btype=["\'](?:fpdelayedscript|application\/ld\+json)["\'])([^>]*)>(.*?)<\/script>/is';
        
        $result = preg_replace_callback($pattern, function($matches) {
            // BUGFIX: Verifica che i match esistano
            if (!isset($matches[1], $matches[2])) {
                return $matches[0];
            }
            $attributes = $matches[1];
            $content = $matches[2];
            
            // Non ritardare script con src (già gestiti da delayScriptTag)
            if (stripos($attributes, 'src=') !== false) {
                return $matches[0];
            }
            
            // Non ritardare script vuoti
            if (trim($content) === '') {
                return $matches[0];
            }
            
            // Non ritardare script critici (contengono wp.i18n, jQuery, etc.)
            $criticalPatterns = [
                '/wp\\.i18n/',
                '/wp\\.polyfill/',
                '/wpEmojiSettingsSupports/',
                '/document\\.documentElement\\.className/',
            ];
            
            foreach ($criticalPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return $matches[0]; // Non ritardare
                }
            }
            
            // Cambia type a fpdelayedscript
            if (stripos($attributes, 'type=') !== false) {
                $attributes = preg_replace('/type=["\'][^"\']*["\']/', 'type="fpdelayedscript"', $attributes);
            } else {
                $attributes .= ' type="fpdelayedscript"';
            }
            
            // Aggiungi data attribute
            $attributes .= ' data-fp-delayed="true"';
            
            return sprintf('<script%s>%s</script>', $attributes, $content);
            
        }, $html);
        
        // BUGFIX: Verifica errori regex
        if (preg_last_error() !== PREG_NO_ERROR) {
            $this->log('error', 'Delay JS regex error', ['error_code' => preg_last_error()]);
            return $html; // Ritorna HTML originale se errore
        }
        
        return $result;
    }
    
    /**
     * Enqueue handler script per eseguire delayed scripts
     */
    public function enqueueDelayHandler(): void
    {
        $settings = $this->getSettings();
        $timeout = $settings['timeout'] ?? 5000;
        $events = $settings['trigger_events'] ?? ['scroll', 'mousedown', 'touchstart'];
        
        // BUGFIX: wp_json_encode con fallback
        $eventsJson = wp_json_encode($events);
        if ($eventsJson === false) {
            $this->log('error', 'Failed to encode trigger events to JSON');
            $eventsJson = '["scroll","mousedown","touchstart"]'; // Fallback default
        }
        
        $script = <<<JAVASCRIPT
<script>
(function() {
    'use strict';
    
    // FP Performance Suite - Delayed JavaScript Executor
    const FPDelayedJS = {
        timeout: {$timeout},
        events: {$eventsJson},
        executed: false,
        
        init: function() {
            const self = this;
            
            // Registra event listeners
            this.events.forEach(function(eventName) {
                window.addEventListener(eventName, function() {
                    self.execute();
                }, { once: true, passive: true });
            });
            
            // Fallback timeout
            setTimeout(function() {
                self.execute();
            }, this.timeout);
        },
        
        execute: function() {
            if (this.executed) return;
            this.executed = true;
            
            // Trova tutti gli script ritardati
            const delayedScripts = document.querySelectorAll('script[type="fpdelayedscript"]');
            
            delayedScripts.forEach(function(script) {
                // Crea nuovo script element
                const newScript = document.createElement('script');
                
                // Copia attributi
                Array.from(script.attributes).forEach(function(attr) {
                    if (attr.name !== 'type' && attr.name !== 'data-fp-delayed') {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });
                
                // Copia contenuto o src
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.textContent = script.textContent;
                }
                
                // Set type correto
                newScript.type = 'text/javascript';
                
                // Sostituisci script
                script.parentNode.replaceChild(newScript, script);
            });
            
            // Trigger evento custom per altre funzionalità
            if (typeof Event === 'function') {
                window.dispatchEvent(new Event('fpDelayedScriptsExecuted'));
            }
        }
    };
    
    // Inizializza quando DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            FPDelayedJS.init();
        });
    } else {
        FPDelayedJS.init();
    }
    
})();
</script>
JAVASCRIPT;
        
        echo $script;
    }
    
    /**
     * Status del servizio
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => !empty($settings['enabled']),
            'timeout' => $settings['timeout'] ?? 5000,
            'delay_inline' => !empty($settings['delay_inline_scripts']),
            'events_count' => count($settings['trigger_events'] ?? []),
            'excluded_count' => count($settings['excluded_keywords'] ?? []),
        ];
    }
}

