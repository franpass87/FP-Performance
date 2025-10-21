<?php

namespace FP\PerfSuite;

use FP\PerfSuite\Services\Assets\AutoFontOptimizer;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\CriticalPathOptimizer;
use FP\PerfSuite\Services\Assets\RenderBlockingOptimizer;
use FP\PerfSuite\Admin\Pages\LighthouseFontOptimization;

/**
 * Main Plugin Class
 * 
 * Gestisce l'inizializzazione e l'integrazione di tutti i servizi
 * del plugin FP Performance Suite.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Plugin
{
    private static $instance = null;
    private $services = [];

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize plugin
     */
    public static function init(): void
    {
        $plugin = self::getInstance();
        $plugin->registerServices();
        $plugin->registerHooks();
    }

    /**
     * Register all services
     */
    private function registerServices(): void
    {
        // Auto Font Optimizer - Sistema di auto-rilevamento
        $this->services['auto_font_optimizer'] = new AutoFontOptimizer();
        
        // Font Optimizer - Ottimizzazioni font esistenti
        $this->services['font_optimizer'] = new FontOptimizer();
        
        // Critical Path Optimizer - Ottimizzazioni critical path
        $this->services['critical_path_optimizer'] = new CriticalPathOptimizer();
        
        // Render Blocking Optimizer - Ottimizzazioni render blocking
        $this->services['render_blocking_optimizer'] = new RenderBlockingOptimizer();
        
        // Admin Pages
        if (is_admin()) {
            $this->services['lighthouse_font_admin'] = new LighthouseFontOptimization();
        }
    }

    /**
     * Register WordPress hooks
     */
    private function registerHooks(): void
    {
        // Registra tutti i servizi
        foreach ($this->services as $service) {
            if (method_exists($service, 'register')) {
                $service->register();
            }
        }

        // Hook di attivazione/disattivazione
        register_activation_hook(FP_PERF_SUITE_FILE, [__CLASS__, 'onActivate']);
        register_deactivation_hook(FP_PERF_SUITE_FILE, [__CLASS__, 'onDeactivate']);
    }

    /**
     * Plugin activation
     */
    public static function onActivate(): void
    {
        // Abilita Auto Font Optimizer di default
        update_option('fp_ps_auto_font_optimization', [
            'enabled' => true,
            'auto_detect_fonts' => true,
            'auto_preload_critical' => true,
            'auto_inject_font_display' => true,
            'auto_preconnect_providers' => true,
            'auto_optimize_google_fonts' => true,
            'auto_optimize_local_fonts' => true,
        ]);

        // Abilita Font Optimizer di default
        update_option('fp_ps_font_optimization', [
            'enabled' => true,
            'optimize_google_fonts' => true,
            'add_font_display' => true,
            'inject_font_display' => true,
            'preload_fonts' => true,
            'preconnect_providers' => true,
            'optimize_render_delay' => true,
        ]);

        // Abilita Critical Path Optimizer di default
        update_option('fp_ps_critical_path_optimization', [
            'enabled' => true,
            'preload_critical_fonts' => true,
            'preconnect_providers' => true,
            'optimize_google_fonts' => true,
            'inject_font_display' => true,
            'add_resource_hints' => true,
        ]);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function onDeactivate(): void
    {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Get service by name
     */
    public function getService(string $name)
    {
        return $this->services[$name] ?? null;
    }

    /**
     * Get all services
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Get plugin status
     */
    public function getStatus(): array
    {
        $status = [
            'version' => FP_PERF_SUITE_VERSION,
        ];

        // Aggiungi status dei servizi se disponibili
        if (isset($this->services['auto_font_optimizer'])) {
            $status['auto_font_optimizer'] = $this->services['auto_font_optimizer']->status();
        }
        if (isset($this->services['font_optimizer'])) {
            $status['font_optimizer'] = $this->services['font_optimizer']->status();
        }
        if (isset($this->services['critical_path_optimizer'])) {
            $status['critical_path_optimizer'] = $this->services['critical_path_optimizer']->status();
        }

        return $status;
    }
}