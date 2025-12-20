<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Services\DB\PluginOptimizers\WooCommerceOptimizer;
use FP\PerfSuite\Services\DB\PluginOptimizers\YoastOptimizer;
use FP\PerfSuite\Services\DB\PluginOptimizers\ACFOptimizer;
use FP\PerfSuite\Services\DB\PluginOptimizers\ElementorOptimizer;
use FP\PerfSuite\Services\DB\PluginOptimizers\CF7Optimizer;

/**
 * Plugin-Specific Database Optimizer
 * 
 * Ottimizzazioni specifiche per plugin popolari:
 * - WooCommerce: sessioni, ordini bozza, log
 * - Yoast SEO: meta ridondanti
 * - Advanced Custom Fields: serializzazione
 * - Elementor: revisioni CSS/JS, cache
 * - WordPress Core: revisioni, orfani
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 */
class PluginSpecificOptimizer
{
    /**
     * Analizza opportunità di pulizia per plugin installati
     */
    public function analyzeInstalledPlugins(): array
    {
        $opportunities = [];
        
        // WooCommerce
        if ($this->isPluginActive('woocommerce/woocommerce.php')) {
            $optimizer = new WooCommerceOptimizer();
            $opportunities['woocommerce'] = $optimizer->analyze();
        }
        
        // Yoast SEO
        if ($this->isPluginActive('wordpress-seo/wp-seo.php')) {
            $optimizer = new YoastOptimizer();
            $opportunities['yoast'] = $optimizer->analyze();
        }
        
        // Advanced Custom Fields
        if ($this->isPluginActive('advanced-custom-fields/acf.php') || 
            $this->isPluginActive('advanced-custom-fields-pro/acf.php')) {
            $optimizer = new ACFOptimizer();
            $opportunities['acf'] = $optimizer->analyze();
        }
        
        // Elementor
        if ($this->isPluginActive('elementor/elementor.php')) {
            $optimizer = new ElementorOptimizer();
            $opportunities['elementor'] = $optimizer->analyze();
        }
        
        // Contact Form 7
        if ($this->isPluginActive('contact-form-7/wp-contact-form-7.php')) {
            $optimizer = new CF7Optimizer();
            $opportunities['cf7'] = $optimizer->analyze();
        }
        
        // Calcola totale potenziale risparmio
        $totalSavings = 0;
        $totalItems = 0;
        
        foreach ($opportunities as $plugin => $data) {
            $totalSavings += $data['potential_savings_mb'] ?? 0;
            $totalItems += $data['total_items'] ?? 0;
        }
        
        return [
            'opportunities' => $opportunities,
            'total_plugins_analyzed' => count($opportunities),
            'total_potential_savings_mb' => round($totalSavings, 2),
            'total_items_to_clean' => $totalItems,
        ];
    }
    
    // Metodi rimossi - ora gestiti dalle classi PluginOptimizers
    // analyzeWooCommerce() -> WooCommerceOptimizer::analyze()
    // analyzeYoast() -> YoastOptimizer::analyze()
    // analyzeACF() -> ACFOptimizer::analyze()
    // analyzeElementor() -> ElementorOptimizer::analyze()
    // analyzeContactForm7() -> CF7Optimizer::analyze()
    
    /**
     * Pulisci dati WooCommerce
     */
    public function cleanupWooCommerce(array $tasks = []): array
    {
        $optimizer = new WooCommerceOptimizer();
        return $optimizer->cleanup($tasks);
    }
    
    /**
     * Pulisci dati Elementor
     */
    public function cleanupElementor(array $tasks = []): array
    {
        $optimizer = new ElementorOptimizer();
        return $optimizer->cleanup($tasks);
    }
    
    /**
     * Verifica se un plugin è attivo
     */
    private function isPluginActive(string $plugin): bool
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active($plugin);
    }
    
    /**
     * Verifica se una tabella esiste
     */
    private function tableExists(string $table): bool
    {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table
        ));
    }
    
    // Metodi rimossi - ora gestiti dalle classi PluginOptimizers
    // getWooCommerceRecommendations() -> WooCommerceOptimizer::getRecommendations()
    // getYoastRecommendations() -> YoastOptimizer::getRecommendations()
    // getACFRecommendations() -> ACFOptimizer::getRecommendations()
    // getElementorRecommendations() -> ElementorOptimizer::getRecommendations()
    // getCF7Recommendations() -> CF7Optimizer::getRecommendations()
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // PluginSpecificOptimizer non ha hook specifici da registrare
        // È utilizzato principalmente per analisi e ottimizzazioni on-demand
    }
}

