<?php

declare(strict_types=1);

namespace FP\PerfSuite\Services\Compatibility;

/**
 * Integrazione Automatica con Tutti i Plugin FP
 * Esclude automaticamente le REST API di tutti i plugin FP dalla cache
 */
final class FPPluginsIntegration
{
    /**
     * Lista di tutti i plugin FP e i loro pattern REST API
     */
    private const FP_PLUGINS = [
        'fp-experiences' => '/wp-json/fp-exp/',
        'fp-restaurant-reservations' => '/wp-json/fp-resv/',
        'fp-multilanguage' => '/wp-json/fp-ml/',
        'fp-newspaper' => '/wp-json/fp-news/',
        'fp-publisher' => '/wp-json/fp-pub/',
        'fp-seo-manager' => '/wp-json/fp-seo/',
        'fp-digital-marketing-suite' => '/wp-json/fp-dms/',
        'fp-privacy-and-cookie-policy' => '/wp-json/fp-privacy/',
        'fp-git-updater' => '/wp-json/fp-git-updater/',
    ];
    
    /**
     * Registra gli hook per l'esclusione automatica
     */
    public function register(): void
    {
        // Esclusione dalla cache di FP Performance
        add_filter('fp_ps_cache_exclude_uris', [$this, 'exclude_fp_plugins_from_cache'], 5, 1);
        
        // Esclusione da query monitor / slow query detection
        add_filter('fp_ps_slow_query_exclude_plugins', [$this, 'exclude_fp_plugins_from_monitoring'], 5, 1);
        
        // Esclusione da asset optimization aggressiva
        add_filter('fp_ps_aggressive_optimize_exclude', [$this, 'exclude_fp_plugins_assets'], 5, 1);
        
        // Esclusione da JavaScript delay/defer
        add_filter('fp_ps_delay_js_exclude', [$this, 'exclude_fp_plugins_js'], 5, 1);
        
        // Esclusione da CSS critical/inline
        add_filter('fp_ps_critical_css_exclude', [$this, 'exclude_fp_plugins_css'], 5, 1);
    }
    
    /**
     * Escludi REST API di tutti i plugin FP dalla cache
     *
     * @param array<string> $exclude_uris
     * @return array<string>
     */
    public function exclude_fp_plugins_from_cache(array $exclude_uris): array
    {
        foreach (self::FP_PLUGINS as $plugin => $api_pattern) {
            // Aggiungi pattern REST API
            $exclude_uris[] = $api_pattern;
            $exclude_uris[] = ltrim($api_pattern, '/');
            
            // Aggiungi anche versioni con wildcard
            $exclude_uris[] = $api_pattern . '*';
        }
        
        // Aggiungi pattern generici per sicurezza
        $exclude_uris[] = '/wp-json/fp-*/';
        $exclude_uris[] = 'wp-json/fp-';
        
        // CRITICAL: Escludi WooCommerce Store API dalla cache (usata da Blocks)
        $exclude_uris[] = '/wc/store/';
        $exclude_uris[] = 'wc/store/';
        $exclude_uris[] = '/wc/store/*';
        $exclude_uris[] = '/wp-json/wc/store/';
        $exclude_uris[] = 'wp-json/wc/store/';
        
        return array_unique($exclude_uris);
    }
    
    /**
     * Escludi plugin FP dal monitoring query lente
     *
     * @param array<string> $exclude_plugins
     * @return array<string>
     */
    public function exclude_fp_plugins_from_monitoring(array $exclude_plugins): array
    {
        $exclude_plugins = array_merge($exclude_plugins, array_keys(self::FP_PLUGINS));
        $exclude_plugins[] = 'fp-performance'; // Anche noi stessi
        
        return array_unique($exclude_plugins);
    }
    
    /**
     * Escludi asset dei plugin FP dall'ottimizzazione aggressiva
     *
     * @param array<string> $exclude_patterns
     * @return array<string>
     */
    public function exclude_fp_plugins_assets(array $exclude_patterns): array
    {
        foreach (array_keys(self::FP_PLUGINS) as $plugin) {
            $exclude_patterns[] = "wp-content/plugins/{$plugin}/";
            $exclude_patterns[] = "wp-content/plugins/" . strtoupper($plugin) . "/";
            
            // Varianti con underscore e dash
            $plugin_underscore = str_replace('-', '_', $plugin);
            $plugin_upper = strtoupper(str_replace('-', '-', $plugin));
            
            $exclude_patterns[] = "wp-content/plugins/{$plugin_underscore}/";
            $exclude_patterns[] = "wp-content/plugins/{$plugin_upper}/";
        }
        
        // Pattern generici
        $exclude_patterns[] = 'wp-content/plugins/FP-*/';
        $exclude_patterns[] = 'wp-content/plugins/fp-*/';
        
        return array_unique($exclude_patterns);
    }
    
    /**
     * Escludi JavaScript dei plugin FP da delay/defer
     *
     * @param array<string> $exclude_patterns
     * @return array<string>
     */
    public function exclude_fp_plugins_js(array $exclude_patterns): array
    {
        foreach (array_keys(self::FP_PLUGINS) as $plugin) {
            $exclude_patterns[] = "{$plugin}/assets/js/";
            $exclude_patterns[] = "{$plugin}/assets/dist/";
            $exclude_patterns[] = strtoupper($plugin) . "/assets/js/";
        }
        
        // Pattern generici per tutti i plugin FP
        $exclude_patterns[] = '/plugins/FP-';
        $exclude_patterns[] = '/plugins/fp-';
        
        return array_unique($exclude_patterns);
    }
    
    /**
     * Escludi CSS dei plugin FP da critical/inline
     *
     * @param array<string> $exclude_patterns
     * @return array<string>
     */
    public function exclude_fp_plugins_css(array $exclude_patterns): array
    {
        foreach (array_keys(self::FP_PLUGINS) as $plugin) {
            $exclude_patterns[] = "{$plugin}/assets/css/";
            $exclude_patterns[] = "{$plugin}/dist/";
            $exclude_patterns[] = strtoupper($plugin) . "/assets/css/";
        }
        
        return array_unique($exclude_patterns);
    }
    
    /**
     * Ottieni la lista dei plugin FP attivi
     *
     * @return array<string>
     */
    public static function get_active_fp_plugins(): array
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', []);
        
        $fp_plugins = [];
        
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            // Check se è un plugin FP (dalla folder o dal nome)
            $plugin_dir = dirname($plugin_file);
            
            if (stripos($plugin_dir, 'fp-') === 0 || stripos($plugin_dir, 'FP-') === 0) {
                $is_active = in_array($plugin_file, $active_plugins, true);
                
                $fp_plugins[] = [
                    'slug' => $plugin_dir,
                    'name' => $plugin_data['Name'] ?? $plugin_dir,
                    'version' => $plugin_data['Version'] ?? 'unknown',
                    'active' => $is_active,
                ];
            }
        }
        
        return $fp_plugins;
    }
}

