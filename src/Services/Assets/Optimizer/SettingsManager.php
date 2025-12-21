<?php

namespace FP\PerfSuite\Services\Assets\Optimizer;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\ErrorHandler;
use function get_option;
use function wp_parse_args;

/**
 * Gestisce le impostazioni dell'Asset Optimizer
 * 
 * REFACTORED: Versione semplificata e robusta per risolvere errori 500
 * 
 * @package FP\PerfSuite\Services\Assets\Optimizer
 * @author Francesco Passeri
 */
class SettingsManager
{
    private const OPTION = 'fp_ps_assets';
    private DataSanitizer $sanitizer;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param DataSanitizer $sanitizer Data sanitizer
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(DataSanitizer $sanitizer, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->sanitizer = $sanitizer;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Ottiene le impostazioni correnti
     */
    public function get(): array
    {
        $defaults = [
            'enabled' => false,
            'minify_html' => false,
            'defer_js' => false,
            'async_js' => false,
            'async_css' => false,
            'remove_emojis' => false,
            'dns_prefetch' => [],
            'preload' => [],
            'preconnect' => [],
            'heartbeat_admin' => 60,
            'combine_css' => false,
            'combine_js' => false,
            'critical_css_handles' => [],
            'exclude_css' => '',
            'exclude_js' => '',
            'minify_inline_css' => false,
            'minify_inline_js' => false,
            'remove_comments' => false,
            'optimize_google_fonts' => false,
            'preload_critical_assets' => false,
            'critical_assets_list' => [],
        ];
        
        $options = $this->getOption(self::OPTION, []);
        
        // Assicurati che sia un array
        if (!is_array($options)) {
            $options = [];
        }

        // Sanitize URL lists
        if (isset($options['dns_prefetch']) && !is_array($options['dns_prefetch'])) {
            $options['dns_prefetch'] = $this->sanitizer->sanitizeUrlList($options['dns_prefetch']);
        }
        if (isset($options['preload']) && !is_array($options['preload'])) {
            $options['preload'] = $this->sanitizer->sanitizeUrlList($options['preload']);
        }
        if (isset($options['preconnect']) && !is_array($options['preconnect'])) {
            $options['preconnect'] = $this->sanitizer->sanitizeUrlList($options['preconnect']);
        }
        if (isset($options['critical_css_handles']) && !is_array($options['critical_css_handles'])) {
            $options['critical_css_handles'] = $this->sanitizer->sanitizeHandleList($options['critical_css_handles']);
        }
        if (isset($options['critical_assets_list']) && !is_array($options['critical_assets_list'])) {
            $options['critical_assets_list'] = $this->sanitizer->sanitizeUrlList($options['critical_assets_list']);
        }

        // Normalizza valori booleani
        $booleanKeys = ['enabled', 'minify_html', 'defer_js', 'async_js', 'async_css', 'remove_emojis', 
                        'combine_css', 'combine_js', 'minify_inline_css', 'minify_inline_js', 
                        'remove_comments', 'optimize_google_fonts', 'preload_critical_assets'];
        
        foreach ($booleanKeys as $key) {
            if (isset($options[$key])) {
                $options[$key] = (bool) $options[$key];
            }
        }
        
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     * REFACTORED: Versione semplificata e robusta
     */
    public function update(array $settings): bool
    {
        try {
            // Ottieni impostazioni correnti direttamente dal database per evitare loop infiniti
            // Non usare get() perché potrebbe causare problemi con hook o cache
            $current = [];
            try {
                global $wpdb;
                if (isset($wpdb) && is_object($wpdb) && !empty($wpdb->options)) {
                    $option_value = $wpdb->get_var($wpdb->prepare(
                        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                        self::OPTION
                    ));
                    
                    if ($option_value !== null) {
                        $current = maybe_unserialize($option_value);
                        if (!is_array($current)) {
                            $current = [];
                        }
                    }
                } else {
                    // Fallback a get_option solo se $wpdb non è disponibile
                    $current = get_option(self::OPTION, []);
                    if (!is_array($current)) {
                        $current = [];
                    }
                }
            } catch (\Throwable $e) {
                ErrorHandler::handle($e, "SettingsManager::update get current settings failed");
                $current = [];
            }
            
            // Definisci chiavi booleane
            $booleanKeys = ['enabled', 'minify_html', 'defer_js', 'async_js', 'async_css', 'remove_emojis', 
                            'combine_css', 'combine_js', 'minify_inline_css', 'minify_inline_js', 
                            'remove_comments', 'optimize_google_fonts', 'preload_critical_assets'];
            
            // Costruisci array nuovo con merge sicuro
            $new = $current;
            
            // Aggiorna valori booleani
            foreach ($booleanKeys as $key) {
                if (array_key_exists($key, $settings)) {
                    $new[$key] = (bool) $settings[$key];
                }
            }
            
            // Aggiorna altri campi
            if (array_key_exists('dns_prefetch', $settings)) {
                $new['dns_prefetch'] = is_array($settings['dns_prefetch']) 
                    ? $settings['dns_prefetch'] 
                    : $this->sanitizer->sanitizeUrlList($settings['dns_prefetch']);
            }
            
            if (array_key_exists('preload', $settings)) {
                $new['preload'] = is_array($settings['preload']) 
                    ? $settings['preload'] 
                    : $this->sanitizer->sanitizeUrlList($settings['preload']);
            }
            
            if (array_key_exists('preconnect', $settings)) {
                $new['preconnect'] = is_array($settings['preconnect']) 
                    ? $settings['preconnect'] 
                    : $this->sanitizer->sanitizeUrlList($settings['preconnect']);
            }
            
            if (array_key_exists('heartbeat_admin', $settings)) {
                $new['heartbeat_admin'] = (int) $settings['heartbeat_admin'];
            }
            
            if (array_key_exists('critical_css_handles', $settings)) {
                $new['critical_css_handles'] = is_array($settings['critical_css_handles']) 
                    ? $settings['critical_css_handles'] 
                    : $this->sanitizer->sanitizeHandleList($settings['critical_css_handles']);
            }
            
            if (array_key_exists('exclude_css', $settings)) {
                $new['exclude_css'] = (string) $settings['exclude_css'];
            }
            
            if (array_key_exists('exclude_js', $settings)) {
                $new['exclude_js'] = (string) $settings['exclude_js'];
            }
            
            if (array_key_exists('critical_assets_list', $settings)) {
                $new['critical_assets_list'] = is_array($settings['critical_assets_list']) 
                    ? $settings['critical_assets_list'] 
                    : $this->sanitizer->sanitizeUrlList($settings['critical_assets_list']);
            }
            
            // Normalizza tutti i valori booleani nell'array finale
            foreach ($booleanKeys as $key) {
                if (isset($new[$key])) {
                    $new[$key] = (bool) $new[$key];
                }
            }
            
            // Assicurati che gli array siano array (con controllo esistenza)
            if (!isset($new['dns_prefetch']) || !is_array($new['dns_prefetch'])) {
                $new['dns_prefetch'] = [];
            }
            if (!isset($new['preload']) || !is_array($new['preload'])) {
                $new['preload'] = [];
            }
            if (!isset($new['preconnect']) || !is_array($new['preconnect'])) {
                $new['preconnect'] = [];
            }
            if (!isset($new['critical_css_handles']) || !is_array($new['critical_css_handles'])) {
                $new['critical_css_handles'] = [];
            }
            if (!isset($new['critical_assets_list']) || !is_array($new['critical_assets_list'])) {
                $new['critical_assets_list'] = [];
            }
            
            // Assicurati che i valori stringa siano stringhe
            if (!isset($new['exclude_css'])) {
                $new['exclude_css'] = '';
            }
            if (!isset($new['exclude_js'])) {
                $new['exclude_js'] = '';
            }
            if (!isset($new['heartbeat_admin'])) {
                $new['heartbeat_admin'] = 0;
            }
            
            // Verifica se ci sono cambiamenti usando serializzazione per confronto sicuro
            $hasChanges = false;
            try {
                $current_serialized = maybe_serialize($current);
                $new_serialized = maybe_serialize($new);
                
                if ($current_serialized !== $new_serialized) {
                    $hasChanges = true;
                }
            } catch (\Throwable $e) {
                // Se la serializzazione fallisce, considera come se ci fossero cambiamenti
                ErrorHandler::handle($e, "SettingsManager::update serialization check failed");
                $hasChanges = true;
            }
            
            if (!$hasChanges) {
                return true;
            }
            
            // Verifica che l'array sia serializzabile prima di salvare
            try {
                $test_serialized = serialize($new);
                if ($test_serialized === false) {
                    ErrorHandler::handle(
                        new \Exception("Array not serializable in update()"),
                        "SettingsManager::update serialization failed"
                    );
                    return false;
                }
            } catch (\Throwable $e) {
                ErrorHandler::handle($e, "SettingsManager::update serialization check exception");
                return false;
            }
            
            // Salva usando metodo semplificato
            return $this->saveOption(self::OPTION, $new);
            
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, "SettingsManager::update failed");
            return false;
        }
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
     * Salva opzione in modo sicuro e diretto
     * REFACTORED: Versione semplificata che usa $wpdb direttamente
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function saveOption(string $key, $value): bool
    {
        try {
            // Verifica che il valore sia serializzabile
            if (!is_scalar($value) && !is_array($value) && !is_null($value)) {
                ErrorHandler::handle(
                    new \Exception("Invalid value type for key: {$key}, type: " . gettype($value)),
                    "saveOption invalid type"
                );
                return false;
            }
            
            // Verifica che l'array sia serializzabile prima di salvare
            if (is_array($value)) {
                try {
                    $test_serialized = serialize($value);
                    if ($test_serialized === false) {
                        ErrorHandler::handle(
                            new \Exception("Array not serializable for key: {$key}"),
                            "saveOption serialization failed"
                        );
                        return false;
                    }
                } catch (\Throwable $e) {
                    ErrorHandler::handle($e, "saveOption serialization check failed for key: {$key}");
                    return false;
                }
            }
            
            // Prova prima con il repository se disponibile
            if ($this->optionsRepo !== null) {
                try {
                    $result = $this->optionsRepo->set($key, $value);
                    if ($result) {
                        wp_cache_delete($key, 'options');
                        wp_cache_delete('alloptions', 'options');
                        return true;
                    }
                } catch (\Throwable $e) {
                    ErrorHandler::handle($e, "Options repository set failed for key: {$key}");
                    // Continua con fallback
                }
            }
            
            // Invalida cache prima
            wp_cache_delete($key, 'options');
            wp_cache_delete('alloptions', 'options');
            
            // FIX CRITICO: Usa $wpdb direttamente per bypassare completamente update_option()
            // Questo evita qualsiasi problema con sanitize_option() o hook che potrebbero causare errori fatali
            global $wpdb;
            
            if (!isset($wpdb) || !is_object($wpdb) || empty($wpdb->options)) {
                ErrorHandler::handle(
                    new \Exception("wpdb not available or options table not set"),
                    "saveOption wpdb check failed"
                );
                return false;
            }
            
            // Serializza il valore
            $serialized_value = maybe_serialize($value);
            
            if ($serialized_value === false && $value !== false) {
                ErrorHandler::handle(
                    new \Exception("Value not serializable for key: {$key}"),
                    "saveOption maybe_serialize failed"
                );
                return false;
            }
            
            // Verifica se l'opzione esiste già - con gestione errori robusta
            $option_exists = false;
            try {
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s",
                    $key
                ));
                
                if ($wpdb->last_error) {
                    ErrorHandler::handle(
                        new \Exception("Database query error: {$wpdb->last_error}"),
                        "saveOption get_var failed"
                    );
                    return false;
                }
                
                $option_exists = (bool) $count;
            } catch (\Throwable $e) {
                ErrorHandler::handle($e, "saveOption get_var exception for key: {$key}");
                return false;
            }
            
            // Esegui update o insert con gestione errori robusta
            try {
                if ($option_exists) {
                    // Opzione esistente: aggiorna solo se il valore è diverso
                    $current_serialized = $wpdb->get_var($wpdb->prepare(
                        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
                        $key
                    ));
                    
                    // Se il valore è identico, non aggiornare (comportamento di update_option)
                    if ($current_serialized === $serialized_value) {
                        return true;
                    }
                    
                    // Aggiorna l'opzione esistente
                    $result = $wpdb->update(
                        $wpdb->options,
                        ['option_value' => $serialized_value],
                        ['option_name' => $key],
                        ['%s'],
                        ['%s']
                    );
                    
                    // $wpdb->update() restituisce il numero di righe aggiornate (0 se nessuna modifica, false se errore)
                    if ($result === false) {
                        if (!empty($wpdb->last_error)) {
                            ErrorHandler::handle(
                                new \Exception("Database update error: {$wpdb->last_error}"),
                                "saveOption update failed"
                            );
                            return false;
                        }
                        // Se non c'è errore ma result è false, potrebbe essere che i valori sono identici
                        // Ma abbiamo già verificato questo sopra, quindi è un errore
                        ErrorHandler::handle(
                            new \Exception("Update returned false without error"),
                            "saveOption update false"
                        );
                        return false;
                    }
                } else {
                    // Opzione non esistente: creala
                    $result = $wpdb->insert(
                        $wpdb->options,
                        [
                            'option_name' => $key,
                            'option_value' => $serialized_value,
                            'autoload' => 'no',
                        ],
                        ['%s', '%s', '%s']
                    );
                    
                    // $wpdb->insert() restituisce false se errore, altrimenti numero di righe inserite
                    if ($result === false) {
                        if (!empty($wpdb->last_error)) {
                            ErrorHandler::handle(
                                new \Exception("Database insert error: {$wpdb->last_error}"),
                                "saveOption insert failed"
                            );
                            return false;
                        }
                        ErrorHandler::handle(
                            new \Exception("Insert returned false without error"),
                            "saveOption insert false"
                        );
                        return false;
                    }
                }
            } catch (\Throwable $e) {
                ErrorHandler::handle($e, "saveOption database operation exception for key: {$key}");
                return false;
            }
            
            // Invalida cache dopo
            wp_cache_delete($key, 'options');
            wp_cache_delete('alloptions', 'options');
            
            return true;
            
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, "saveOption failed for key: {$key}");
            return false;
        }
    }
}
