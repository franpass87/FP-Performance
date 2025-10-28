<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Utils\Logger;

/**
 * Asset Optimization Integrator
 * 
 * Integra Smart Exclusion con Asset Optimization per
 * escludere automaticamente script e CSS critici
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AssetOptimizationIntegrator
{
    private SmartExclusionDetector $smartDetector;
    private Optimizer $optimizer;

    public function __construct()
    {
        $this->smartDetector = new SmartExclusionDetector();
        $this->optimizer = new Optimizer();
    }

    /**
     * Applica esclusioni intelligenti agli asset
     */
    public function applySmartAssetExclusions(): array
    {
        $results = [
            'js_exclusions_applied' => 0,
            'css_exclusions_applied' => 0,
            'critical_scripts_detected' => 0,
            'critical_css_detected' => 0,
            'errors' => [],
        ];

        try {
            // 1. Rileva e applica esclusioni JavaScript
            $jsResults = $this->applyJavaScriptExclusions();
            $results['js_exclusions_applied'] = $jsResults['applied'];
            $results['critical_scripts_detected'] = $jsResults['detected'];

            // 2. Rileva e applica esclusioni CSS
            $cssResults = $this->applyCssExclusions();
            $results['css_exclusions_applied'] = $cssResults['applied'];
            $results['critical_css_detected'] = $cssResults['detected'];

            Logger::info('Smart asset exclusions applied', $results);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Logger::error('Smart asset exclusions failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Applica esclusioni JavaScript intelligenti
     */
    private function applyJavaScriptExclusions(): array
    {
        $results = ['applied' => 0, 'detected' => 0];
        
        // Rileva script critici
        $criticalScripts = $this->smartDetector->detectCriticalScripts();
        $excludeJs = $this->smartDetector->detectExcludeJs();
        
        $results['detected'] = count($criticalScripts['always_exclude']) + 
                             count($criticalScripts['plugin_critical']) +
                             count($excludeJs['plugin_specific']);

        // Applica esclusioni alle impostazioni
        $settings = get_option('fp_ps_assets', []);
        $currentExclusions = $settings['exclude_js'] ?? '';
        $exclusionsList = array_filter(explode("\n", $currentExclusions));

        $newExclusions = [];

        // Aggiungi script critici sempre esclusi
        foreach ($criticalScripts['always_exclude'] as $script) {
            if (!in_array($script, $exclusionsList, true)) {
                $newExclusions[] = $script;
            }
        }

        // Aggiungi script critici dei plugin
        foreach ($criticalScripts['plugin_critical'] as $script) {
            $pattern = $script['pattern'] . '*';
            if (!in_array($pattern, $exclusionsList, true)) {
                $newExclusions[] = $pattern;
            }
        }

        // Aggiungi script rilevati dal detector
        foreach ($excludeJs['plugin_specific'] as $script) {
            if ($script['confidence'] >= 0.8) {
                $handle = $script['handle'];
                if (!in_array($handle, $exclusionsList, true)) {
                    $newExclusions[] = $handle;
                }
            }
        }

        // Aggiungi script con dipendenze critiche
        foreach ($excludeJs['core_dependencies'] as $script) {
            if ($script['confidence'] >= 0.7) {
                $handle = $script['handle'];
                if (!in_array($handle, $exclusionsList, true)) {
                    $newExclusions[] = $handle;
                }
            }
        }

        // Aggiorna impostazioni
        if (!empty($newExclusions)) {
            $allExclusions = array_merge($exclusionsList, $newExclusions);
            $settings['exclude_js'] = implode("\n", array_unique($allExclusions));
            update_option('fp_ps_assets', $settings);
            $results['applied'] = count($newExclusions);
        }

        return $results;
    }

    /**
     * Applica esclusioni CSS intelligenti
     */
    private function applyCssExclusions(): array
    {
        $results = ['applied' => 0, 'detected' => 0];
        
        // Rileva CSS critici
        $excludeCss = $this->smartDetector->detectExcludeCss();
        
        $results['detected'] = count($excludeCss['plugin_specific']) + 
                             count($excludeCss['critical_files']) +
                             count($excludeCss['admin_styles']);

        // Applica esclusioni alle impostazioni
        $settings = get_option('fp_ps_assets', []);
        $currentExclusions = $settings['exclude_css'] ?? '';
        $exclusionsList = array_filter(explode("\n", $currentExclusions));

        $newExclusions = [];

        // Aggiungi CSS specifici dei plugin
        foreach ($excludeCss['plugin_specific'] as $css) {
            if ($css['confidence'] >= 0.8) {
                $handle = $css['handle'];
                if (!in_array($handle, $exclusionsList, true)) {
                    $newExclusions[] = $handle;
                }
            }
        }

        // Aggiungi file CSS critici
        foreach ($excludeCss['critical_files'] as $css) {
            if ($css['confidence'] >= 0.7) {
                $handle = $css['handle'];
                if (!in_array($handle, $exclusionsList, true)) {
                    $newExclusions[] = $handle;
                }
            }
        }

        // Aggiungi stili admin
        foreach ($excludeCss['admin_styles'] as $css) {
            if ($css['confidence'] >= 0.9) {
                $handle = $css['handle'];
                if (!in_array($handle, $exclusionsList, true)) {
                    $newExclusions[] = $handle;
                }
            }
        }

        // Aggiorna impostazioni
        if (!empty($newExclusions)) {
            $allExclusions = array_merge($exclusionsList, $newExclusions);
            $settings['exclude_css'] = implode("\n", array_unique($allExclusions));
            update_option('fp_ps_assets', $settings);
            $results['applied'] = count($newExclusions);
        }

        return $results;
    }

    /**
     * Ottimizza configurazione asset basandosi su esclusioni
     */
    public function optimizeAssetConfiguration(): array
    {
        $results = [
            'optimizations_applied' => 0,
            'settings_updated' => [],
            'recommendations' => [],
        ];

        $settings = get_option('fp_ps_assets', []);
        $optimizations = [];

        // 1. Configura minificazione basandosi su script critici
        $criticalScripts = $this->smartDetector->detectCriticalScripts();
        if (count($criticalScripts['always_exclude']) > 5) {
            if (!isset($settings['minify_js']) || !$settings['minify_js']) {
                $settings['minify_js'] = true;
                $optimizations[] = 'minify_js_enabled';
            }
        }

        // 2. Configura defer/async basandosi su dipendenze
        $excludeJs = $this->smartDetector->detectExcludeJs();
        $highDependencyScripts = array_filter($excludeJs['core_dependencies'], 
            fn($script) => $script['confidence'] >= 0.8
        );

        if (count($highDependencyScripts) > 3) {
            if (!isset($settings['defer_js']) || !$settings['defer_js']) {
                $settings['defer_js'] = true;
                $optimizations[] = 'defer_js_enabled';
            }
        }

        // 3. Configura critical CSS basandosi su CSS critici
        $excludeCss = $this->smartDetector->detectExcludeCss();
        $criticalCssCount = count($excludeCss['critical_files']);

        if ($criticalCssCount > 2) {
            if (!isset($settings['critical_css']) || !$settings['critical_css']) {
                $settings['critical_css'] = true;
                $optimizations[] = 'critical_css_enabled';
            }
        }

        // 4. Configura tree shaking per script non critici
        $nonCriticalScripts = $this->identifyNonCriticalScripts();
        if (count($nonCriticalScripts) > 5) {
            if (!isset($settings['tree_shaking']) || !$settings['tree_shaking']) {
                $settings['tree_shaking'] = true;
                $optimizations[] = 'tree_shaking_enabled';
            }
        }

        // 5. Configura HTTP/2 Server Push per asset critici
        $criticalAssets = $this->identifyCriticalAssets();
        if (count($criticalAssets) > 0) {
            if (!isset($settings['http2_push']) || !$settings['http2_push']) {
                $settings['http2_push'] = true;
                $optimizations[] = 'http2_push_enabled';
            }
        }

        // Applica ottimizzazioni
        if (!empty($optimizations)) {
            update_option('fp_ps_assets', $settings);
            $results['optimizations_applied'] = count($optimizations);
            $results['settings_updated'] = $optimizations;
        }

        // Genera raccomandazioni
        $results['recommendations'] = $this->generateAssetRecommendations($settings);

        return $results;
    }

    /**
     * Identifica script non critici per tree shaking
     */
    private function identifyNonCriticalScripts(): array
    {
        global $wp_scripts;
        
        if (!isset($wp_scripts) || !is_object($wp_scripts)) {
            return [];
        }

        $nonCritical = [];
        $criticalPatterns = [
            'jquery', 'wp-', 'admin', 'login', 'checkout', 'cart',
            'stripe', 'paypal', 'analytics', 'gtag'
        ];

        foreach ($wp_scripts->registered as $handle => $script) {
            $isCritical = false;
            
            foreach ($criticalPatterns as $pattern) {
                if (stripos($handle, $pattern) !== false || 
                    (isset($script->src) && stripos($script->src, $pattern) !== false)) {
                    $isCritical = true;
                    break;
                }
            }
            
            if (!$isCritical && isset($script->src) && strpos($script->src, get_site_url()) !== false) {
                $nonCritical[] = $handle;
            }
        }

        return $nonCritical;
    }

    /**
     * Identifica asset critici per HTTP/2 Server Push
     */
    private function identifyCriticalAssets(): array
    {
        $criticalAssets = [];
        
        // CSS critici del tema
        $themeUrl = get_stylesheet_directory_uri();
        $criticalAssets[] = $themeUrl . '/style.css';
        
        // Font critici
        $criticalAssets[] = $themeUrl . '/fonts/';
        
        // Script critici
        $criticalScripts = $this->smartDetector->detectCriticalScripts();
        foreach ($criticalScripts['always_exclude'] as $script) {
            if (strpos($script, 'jquery') !== false) {
                $criticalAssets[] = 'jquery';
            }
        }

        return array_unique($criticalAssets);
    }

    /**
     * Genera raccomandazioni per asset
     */
    private function generateAssetRecommendations(array $settings): array
    {
        $recommendations = [];

        // Raccomandazioni basate su configurazione attuale
        if (!($settings['minify_js'] ?? false)) {
            $recommendations[] = [
                'type' => 'minification',
                'priority' => 'high',
                'message' => __('Abilita la minificazione JavaScript per ridurre le dimensioni dei file', 'fp-performance-suite'),
            ];
        }

        if (!($settings['minify_css'] ?? false)) {
            $recommendations[] = [
                'type' => 'minification',
                'priority' => 'high',
                'message' => __('Abilita la minificazione CSS per ridurre le dimensioni dei file', 'fp-performance-suite'),
            ];
        }

        if (!($settings['critical_css'] ?? false)) {
            $recommendations[] = [
                'type' => 'critical_css',
                'priority' => 'medium',
                'message' => __('Abilita Critical CSS per migliorare il First Contentful Paint', 'fp-performance-suite'),
            ];
        }

        if (!($settings['defer_js'] ?? false)) {
            $recommendations[] = [
                'type' => 'defer',
                'priority' => 'medium',
                'message' => __('Abilita il defer per JavaScript non critico', 'fp-performance-suite'),
            ];
        }

        if (!($settings['tree_shaking'] ?? false)) {
            $recommendations[] = [
                'type' => 'tree_shaking',
                'priority' => 'low',
                'message' => __('Abilita Tree Shaking per rimuovere codice non utilizzato', 'fp-performance-suite'),
            ];
        }

        return $recommendations;
    }

    /**
     * Analizza efficacia delle esclusioni asset
     */
    public function analyzeAssetExclusionEffectiveness(): array
    {
        $analysis = [
            'js_exclusions' => [],
            'css_exclusions' => [],
            'effectiveness_score' => 0,
            'recommendations' => [],
        ];

        // Analizza esclusioni JavaScript
        $jsSettings = get_option('fp_ps_assets', []);
        $jsExclusions = $jsSettings['exclude_js'] ?? '';
        $jsExclusionsList = array_filter(explode("\n", $jsExclusions));

        $analysis['js_exclusions'] = [
            'total' => count($jsExclusionsList),
            'critical_scripts' => count(array_filter($jsExclusionsList, fn($script) => 
                strpos($script, 'jquery') !== false || strpos($script, 'wp-') !== false
            )),
            'plugin_scripts' => count(array_filter($jsExclusionsList, fn($script) => 
                strpos($script, 'woocommerce') !== false || strpos($script, 'elementor') !== false
            )),
        ];

        // Analizza esclusioni CSS
        $cssExclusions = $jsSettings['exclude_css'] ?? '';
        $cssExclusionsList = array_filter(explode("\n", $cssExclusions));

        $analysis['css_exclusions'] = [
            'total' => count($cssExclusionsList),
            'critical_css' => count(array_filter($cssExclusionsList, fn($css) => 
                strpos($css, 'style') !== false || strpos($css, 'main') !== false
            )),
            'plugin_css' => count(array_filter($cssExclusionsList, fn($css) => 
                strpos($css, 'woocommerce') !== false || strpos($css, 'elementor') !== false
            )),
        ];

        // Calcola score di efficacia
        $totalExclusions = $analysis['js_exclusions']['total'] + $analysis['css_exclusions']['total'];
        $criticalExclusions = $analysis['js_exclusions']['critical_scripts'] + $analysis['css_exclusions']['critical_css'];
        
        if ($totalExclusions > 0) {
            $analysis['effectiveness_score'] = round(($criticalExclusions / $totalExclusions) * 100, 1);
        }

        // Genera raccomandazioni
        if ($analysis['effectiveness_score'] < 70) {
            $analysis['recommendations'][] = [
                'type' => 'effectiveness',
                'message' => __('Considera di rivedere le esclusioni per includere più asset critici', 'fp-performance-suite'),
            ];
        }

        if ($totalExclusions < 5) {
            $analysis['recommendations'][] = [
                'type' => 'coverage',
                'message' => __('Poche esclusioni rilevate. Considera di eseguire il rilevamento automatico', 'fp-performance-suite'),
            ];
        }

        return $analysis;
    }

    /**
     * Genera report completo di ottimizzazione asset
     */
    public function generateAssetOptimizationReport(): array
    {
        $report = [
            'exclusions_analysis' => $this->analyzeAssetExclusionEffectiveness(),
            'optimization_status' => $this->getOptimizationStatus(),
            'recommendations' => $this->generateAssetRecommendations(get_option('fp_ps_assets', [])),
            'performance_impact' => $this->calculatePerformanceImpact(),
        ];

        return $report;
    }

    /**
     * Ottieni status delle ottimizzazioni
     */
    private function getOptimizationStatus(): array
    {
        $settings = get_option('fp_ps_assets', []);
        
        return [
            'minification_enabled' => ($settings['minify_js'] ?? false) && ($settings['minify_css'] ?? false),
            'critical_css_enabled' => $settings['critical_css'] ?? false,
            'defer_js_enabled' => $settings['defer_js'] ?? false,
            'tree_shaking_enabled' => $settings['tree_shaking'] ?? false,
            'http2_push_enabled' => $settings['http2_push'] ?? false,
        ];
    }

    /**
     * Calcola impatto sulle performance
     */
    private function calculatePerformanceImpact(): array
    {
        // Questa funzione potrebbe essere integrata con metriche reali
        // Per ora restituisce stime basate sulla configurazione
        $settings = get_option('fp_ps_assets', []);
        
        $impact = [
            'estimated_improvement' => 0,
            'file_size_reduction' => 0,
            'load_time_improvement' => 0,
        ];

        if ($settings['minify_js'] ?? false) {
            $impact['estimated_improvement'] += 15;
            $impact['file_size_reduction'] += 20;
        }

        if ($settings['minify_css'] ?? false) {
            $impact['estimated_improvement'] += 10;
            $impact['file_size_reduction'] += 15;
        }

        if ($settings['critical_css'] ?? false) {
            $impact['estimated_improvement'] += 25;
            $impact['load_time_improvement'] += 30;
        }

        if ($settings['defer_js'] ?? false) {
            $impact['estimated_improvement'] += 20;
            $impact['load_time_improvement'] += 25;
        }

        return $impact;
    }
}
