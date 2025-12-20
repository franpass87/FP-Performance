<?php

namespace FP\PerfSuite\Services\AI\Analyzer;

/**
 * Suggerisce configurazioni ottimali basate sull'analisi
 * 
 * @package FP\PerfSuite\Services\AI\Analyzer
 * @author Francesco Passeri
 */
class ConfigurationSuggester
{
    /**
     * Suggerisce configurazione basata sull'analisi
     */
    public function suggest(array $analysis): array
    {
        $config = [];
        $suggestions = [];
        
        $hosting = $analysis['hosting'];
        $resources = $analysis['resources'];
        $traffic = $analysis['traffic'];
        $hasBuilder = $analysis['plugins']['page_builders'] > 0;
        $hasEcommerce = $analysis['plugins']['ecommerce'];
        
        // Page Cache Configuration
        $config['page_cache'] = [
            'enabled' => true,
            'ttl' => $this->suggestCacheTTL($resources, $traffic),
        ];
        
        $suggestions[] = [
            'icon' => '⚡',
            'title' => 'Page Cache Attivato',
            'description' => 'Cache delle pagine abilitata per migliorare le performance',
            'impact' => 'high',
        ];
        
        // Asset Optimization
        $config['asset_optimizer'] = [
            'enabled' => true,
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => !$hasBuilder,
            'defer_js' => true,
            'combine_css' => false,
            'combine_js' => false,
            'remove_query_strings' => true,
            'preload' => [],
        ];
        
        if ($hasBuilder) {
            $suggestions[] = [
                'icon' => '🎨',
                'title' => 'Page Builder Rilevato',
                'description' => 'Minificazione JS disabilitata per compatibilità con page builders',
                'impact' => 'medium',
            ];
        }
        
        // Media Optimization
        $imageCount = $analysis['content']['images'];
        $config['media_optimization'] = [
            'enabled' => $imageCount > 10,
            'lazy_loading' => true,
            'responsive_images' => true,
        ];
        
        if ($imageCount > 10) {
            $suggestions[] = [
                'icon' => '🖼️',
                'title' => 'Ottimizzazione Media Attivata',
                'description' => sprintf('Rilevate %d immagini. L\'ottimizzazione migliorerà le performance del 15-25%%', $imageCount),
                'impact' => 'high',
            ];
        }
        
        // Lazy Load
        $config['lazy_load'] = [
            'enabled' => $imageCount > 20,
            'images' => true,
            'iframes' => true,
            'threshold' => 300,
        ];
        
        if ($imageCount > 20) {
            $suggestions[] = [
                'icon' => '⚡',
                'title' => 'Lazy Load Attivato',
                'description' => 'Caricamento differito per migliorare i tempi di caricamento iniziali',
                'impact' => 'high',
            ];
        }
        
        // Database Optimization
        $dbSize = $analysis['database']['size_mb'];
        $config['db'] = [
            'batch' => $this->suggestDBBatch($resources, $hosting['provider']),
            'auto_optimize' => $dbSize > 100,
        ];
        
        if ($dbSize > 100) {
            $suggestions[] = [
                'icon' => '💾',
                'title' => 'Ottimizzazione Database',
                'description' => sprintf('Database di %.1f MB - consigliata ottimizzazione automatica', $dbSize),
                'impact' => 'medium',
            ];
        }
        
        // Heartbeat API
        $config['heartbeat'] = $this->suggestHeartbeat($resources, $hasBuilder);
        
        $suggestions[] = [
            'icon' => '💓',
            'title' => 'Heartbeat API Ottimizzato',
            'description' => sprintf('Intervallo impostato a %d secondi per ridurre il carico server', $config['heartbeat']),
            'impact' => 'medium',
        ];
        
        // Backend Optimization
        $config['backend'] = [
            'limit_post_revisions' => 5,
            'autosave_interval' => 120,
            'disable_emojis' => true,
            'disable_embeds' => !$analysis['content']['has_videos'],
        ];
        
        // E-commerce specifico
        if ($hasEcommerce) {
            $suggestions[] = [
                'icon' => '🛒',
                'title' => 'E-commerce Rilevato',
                'description' => 'Configurazione ottimizzata per e-commerce',
                'impact' => 'medium',
            ];
        }
        
        return [
            'config' => $config,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Suggerisce TTL cache
     */
    public function suggestCacheTTL(array $resources, array $traffic): int
    {
        $base = 3600; // 1 ora di default
        
        // Riduci per hosting limitati
        if ($resources['memory_category'] === 'low') {
            $base = 1800; // 30 minuti
        }
        
        // Aumenta per alto traffico
        if ($traffic['level'] === 'high') {
            $base = 7200; // 2 ore
        }
        
        // Riduci per basso traffico
        if ($traffic['level'] === 'low') {
            $base = 1800; // 30 minuti
        }
        
        return $base;
    }

    /**
     * Suggerisce la qualità per l'ottimizzazione media
     */
    public function suggestMediaQuality(array $resources, string $hostingProvider): int
    {
        // Default 80
        $quality = 80;
        
        // Riduci per hosting limitati
        if ($resources['memory_category'] === 'low' || in_array($hostingProvider, ['aruba', 'ionos'], true)) {
            $quality = 75;
        }
        
        // Aumenta per risorse abbondanti
        if ($resources['memory_category'] === 'high') {
            $quality = 85;
        }
        
        return $quality;
    }

    /**
     * Suggerisce il batch size per database
     */
    public function suggestDBBatch(array $resources, string $hostingProvider): int
    {
        if ($resources['memory_category'] === 'low' || in_array($hostingProvider, ['aruba', 'ionos'], true)) {
            return 100;
        }
        
        if ($resources['memory_category'] === 'high') {
            return 300;
        }
        
        return 200;
    }

    /**
     * Suggerisce intervallo heartbeat
     */
    public function suggestHeartbeat(array $resources, bool $hasBuilder): int
    {
        if ($hasBuilder) {
            return 60; // Più frequente per page builders
        }
        
        if ($resources['memory_category'] === 'low') {
            return 120; // Meno frequente per risorse limitate
        }
        
        return 60;
    }
}















