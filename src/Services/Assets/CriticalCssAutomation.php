<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

/**
 * Critical CSS Automation
 * 
 * Genera e gestisce automaticamente il Critical CSS
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CriticalCssAutomation
{
    private const OPTION_KEY = 'fp_ps_critical_css_automation';
    private const CACHE_KEY = 'fp_critical_css_cache';
    private const CRITICAL_CSS_OPTION = 'fp_ps_critical_css';
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;
    
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     * @param LoggerInterface|null $logger Logger instance
     */
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
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
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

        // Genera critical CSS automaticamente
        if (!empty($settings['auto_generate'])) {
            add_action('save_post', [$this, 'generateOnSave'], 10, 2);
            add_action('switch_theme', [$this, 'regenerateAll']);
        }

        // Inline critical CSS - solo nel frontend
        if (!is_admin()) {
            add_action('wp_head', [$this, 'inlineCriticalCss'], 22);
        }

        $this->log('debug', 'Critical CSS Automation registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'auto_generate' => false,
            'viewport_width' => 1920,
            'viewport_height' => 1080,
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ];

        $options = $this->getOption(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        $result = $this->setOption(self::OPTION_KEY, $updated);
        
        if ($result) {
            $this->log('info', 'Critical CSS Automation settings updated', $updated);
        }

        return $result;
    }

    /**
     * Genera Critical CSS per un URL
     */
    public function generate(string $url): ?string
    {
        // Metodo 1: Usa Critical library PHP se disponibile
        if (class_exists('\\Critical\\Critical')) {
            return $this->generateWithCritical($url);
        }

        // Metodo 2: Estrai CSS above-the-fold manualmente
        return $this->extractAboveTheFoldCss($url);
    }

    /**
     * Genera usando Critical library
     */
    private function generateWithCritical(string $url): ?string
    {
        // Questa funzionalità richiede la libreria Critical
        // Per ora ritorniamo null, da implementare con la libreria
        $this->log('warning', 'Critical library not available');
        return null;
    }

    /**
     * Estrae CSS above-the-fold manualmente
     */
    private function extractAboveTheFoldCss(string $url): ?string
    {
        try {
            // Fetch HTML
            $response = wp_remote_get($url, [
                'timeout' => $this->getSettings()['timeout'],
                'user-agent' => $this->getSettings()['user_agent'],
            ]);

            if (is_wp_error($response)) {
                $this->log('error', 'Failed to fetch URL for Critical CSS', [
                    'url' => $url,
                    'error' => $response->get_error_message(),
                ]);
                return null;
            }

            $html = wp_remote_retrieve_body($response);

            // Estrai tutti i CSS inline e link
            $css = $this->extractCssFromHtml($html);

            // Filtra solo le regole critiche (semplificato)
            $criticalCss = $this->filterCriticalRules($css);

            return $criticalCss;

        } catch (\Exception $e) {
            $this->log('error', 'Critical CSS extraction failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ], $e);
            return null;
        }
    }

    /**
     * Estrae CSS dall'HTML
     */
    private function extractCssFromHtml(string $html): string
    {
        $css = '';

        // SICUREZZA: Estrai CSS inline con limite per prevenire performance bottleneck
        $maxInlineCss = 10; // Limite per prevenire memory issues
        preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches);
        $inlineCount = 0;
        foreach ($matches[1] as $inlineCss) {
            if ($inlineCount >= $maxInlineCss) {
                break; // Preveniamo memory issues
            }
            $css .= $inlineCss . "\n";
            $inlineCount++;
        }

        // SICUREZZA: Estrai CSS da link con limite per prevenire performance bottleneck
        $maxLinks = 5; // Limite per prevenire memory issues
        preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $linkMatches);
        $linkCount = 0;
        foreach ($linkMatches[0] as $link) {
            if ($linkCount >= $maxLinks) {
                break; // Preveniamo memory issues
            }
            if (preg_match('/href=["\']([^"\']+)["\']/', $link, $href)) {
                $cssUrl = $href[1];
                
                // Solo CSS locali
                if ($this->isLocalUrl($cssUrl)) {
                    $cssContent = $this->fetchCss($cssUrl);
                    if ($cssContent) {
                        $css .= $cssContent . "\n";
                    }
                }
            }
            $linkCount++;
        }

        return $css;
    }

    /**
     * Filtra regole CSS critiche
     */
    private function filterCriticalRules(string $css): string
    {
        // Questa è una versione semplificata
        // In produzione, dovresti usare un parser CSS appropriato

        $critical = '';

        // Selettori critici comuni
        $criticalSelectors = [
            'html',
            'body',
            'header',
            'nav',
            'main',
            '.site-header',
            '.site-title',
            '.site-nav',
            '.hero',
            'h1',
            'h2',
            'p',
            'a',
            'img',
            'button',
        ];

        // SICUREZZA: Estrai regole per selettori critici con limite per prevenire memory leaks
        $maxRules = 100; // Limite per prevenire memory leaks
        $ruleCount = 0;
        
        foreach ($criticalSelectors as $selector) {
            if ($ruleCount >= $maxRules) {
                break; // Preveniamo memory leaks
            }
            
            $pattern = '/' . preg_quote($selector, '/') . '\s*\{[^}]+\}/';
            if (preg_match_all($pattern, $css, $matches)) {
                foreach ($matches[0] as $rule) {
                    if ($ruleCount >= $maxRules) {
                        break;
                    }
                    $critical .= $rule . "\n";
                    $ruleCount++;
                }
            }
        }

        // Minifica
        $critical = $this->minifyCss($critical);

        return $critical;
    }

    /**
     * Minifica CSS
     */
    private function minifyCss(string $css): string
    {
        // Rimuovi commenti
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Rimuovi spazi extra
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Rimuovi spazi prima e dopo caratteri speciali
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        
        return trim($css);
    }

    /**
     * Verifica se URL è locale
     */
    private function isLocalUrl(string $url): bool
    {
        $homeUrl = home_url();
        return strpos($url, $homeUrl) !== false || strpos($url, '//') === false;
    }

    /**
     * Fetch CSS da URL
     */
    private function fetchCss(string $url): ?string
    {
        // Converti URL relativo in assoluto
        if (strpos($url, '//') === false) {
            $url = home_url($url);
        }

        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return null;
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Genera Critical CSS al salvataggio post
     */
    public function generateOnSave(int $postId, \WP_Post $post): void
    {
        // Solo per post pubblicati
        if ($post->post_status !== 'publish') {
            return;
        }

        // Solo per post type pubblici
        if (!is_post_type_viewable($post->post_type)) {
            return;
        }

        $url = get_permalink($postId);
        if (!$url) {
            return;
        }

        $criticalCss = $this->generate($url);

        if ($criticalCss) {
            update_post_meta($postId, '_fp_critical_css', $criticalCss);
            update_post_meta($postId, '_fp_critical_css_generated', time());

            $this->log('info', 'Critical CSS generated for post', ['post_id' => $postId]);
        }
    }

    /**
     * Rigenera tutto al cambio tema
     */
    public function regenerateAll(): void
    {
        // Pulisci cache
        delete_transient(self::CACHE_KEY);

        // Rigenera per homepage
        $homeUrl = home_url();
        $criticalCss = $this->generate($homeUrl);

        if ($criticalCss) {
            $this->setOption(self::CRITICAL_CSS_OPTION, $criticalCss);
        }

        $this->log('info', 'Critical CSS regenerated after theme switch');
    }

    /**
     * Inline Critical CSS in head
     */
    public function inlineCriticalCss(): void
    {
        // Prendi Critical CSS specifico per pagina
        $criticalCss = '';

        if (is_singular()) {
            $postId = get_the_ID();
            $postCriticalCss = get_post_meta($postId, '_fp_critical_css', true);
            
            if ($postCriticalCss) {
                $criticalCss = $postCriticalCss;
            }
        }

        // Fallback a Critical CSS globale
        if (empty($criticalCss)) {
            $criticalCss = $this->getOption(self::CRITICAL_CSS_OPTION, '');
        }

        if (empty($criticalCss)) {
            return;
        }

        echo '<style id="fp-critical-css">' . $criticalCss . '</style>';
    }

    /**
     * Ottiene Critical CSS per un post
     */
    public function getCriticalCss(?int $postId = null): ?string
    {
        if ($postId) {
            $css = get_post_meta($postId, '_fp_critical_css', true);
            if ($css) {
                return $css;
            }
        }

        return $this->getOption(self::CRITICAL_CSS_OPTION, null);
    }

    /**
     * Imposta Critical CSS manualmente
     */
    public function setCriticalCss(string $css, ?int $postId = null): bool
    {
        if ($postId) {
            update_post_meta($postId, '_fp_critical_css', $css);
            update_post_meta($postId, '_fp_critical_css_generated', time());
            return true;
        }

        return $this->setOption(self::CRITICAL_CSS_OPTION, $css);
    }

    /**
     * Pulisce Critical CSS
     */
    public function clearCriticalCss(?int $postId = null): bool
    {
        if ($postId) {
            delete_post_meta($postId, '_fp_critical_css');
            delete_post_meta($postId, '_fp_critical_css_generated');
            return true;
        }

        return $this->deleteOption(self::CRITICAL_CSS_OPTION);
    }

    /**
     * Ottiene statistiche
     */
    public function getStats(): array
    {
        global $wpdb;

        // SICUREZZA: Usiamo prepare per prevenire SQL injection
        $postsWithCriticalCss = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                '_fp_critical_css'
            )
        );

        $globalCriticalCss = $this->getOption(self::CRITICAL_CSS_OPTION, '');

        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'global_css_size' => strlen($globalCriticalCss),
            'posts_with_critical_css' => (int) $postsWithCriticalCss,
            'auto_generation' => !empty($this->getSettings()['auto_generate']),
        ];
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
            'stats' => $this->getStats(),
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }

    /**
     * Delete option value (with fallback)
     * 
     * @param string $key Option key
     * @return bool
     */
    private function deleteOption(string $key): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->delete($key);
        }
        return delete_option($key);
    }
}

