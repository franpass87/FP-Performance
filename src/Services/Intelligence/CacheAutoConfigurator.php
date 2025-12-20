<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Utils\Logger;

/**
 * Cache Auto-Configurator
 * 
 * Integra Smart Exclusion con il sistema di cache per
 * auto-configurare regole di esclusione basate su performance
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CacheAutoConfigurator
{
    private SmartExclusionDetector $smartDetector;
    private PerformanceBasedExclusionDetector $performanceDetector;
    private PageCache $pageCache;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->smartDetector = new SmartExclusionDetector();
        $this->performanceDetector = new PerformanceBasedExclusionDetector();
        $this->pageCache = new PageCache();
    }

    /**
     * Auto-configura le regole di cache basandosi su esclusioni rilevate
     */
    public function autoConfigureCacheRules(): array
    {
        $results = [
            'cache_rules_applied' => 0,
            'exclusions_applied' => 0,
            'optimizations_applied' => 0,
            'errors' => [],
        ];

        try {
            // 1. Rileva esclusioni intelligenti
            $smartExclusions = $this->smartDetector->detectSensitiveUrls();
            $appliedSmart = $this->applySmartExclusions($smartExclusions);
            $results['exclusions_applied'] += $appliedSmart;

            // 2. Rileva esclusioni basate su performance
            $performanceExclusions = $this->performanceDetector->autoApplyPerformanceExclusions(false);
            $results['exclusions_applied'] += $performanceExclusions['applied'];

            // 3. Ottimizza configurazioni cache esistenti
            $cacheOptimizations = $this->optimizeCacheSettings();
            $results['optimizations_applied'] += $cacheOptimizations;

            // 4. Applica regole di cache avanzate
            $advancedRules = $this->applyAdvancedCacheRules();
            $results['cache_rules_applied'] += $advancedRules;

            Logger::info('Cache auto-configuration completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Logger::error('Cache auto-configuration failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Applica esclusioni intelligenti alla cache
     */
    private function applySmartExclusions(array $exclusions): int
    {
        $applied = 0;
        $cacheSettings = $this->getOption('fp_ps_page_cache', []);

        foreach ($exclusions as $category => $items) {
            if (empty($items)) continue;

            foreach ($items as $item) {
                if ($item['confidence'] >= 0.8) {
                    // Aggiungi alla lista esclusioni cache
                    $currentExclusions = $cacheSettings['exclude_urls'] ?? '';
                    $exclusionsList = array_filter(explode("\n", $currentExclusions));
                    
                    if (!in_array($item['url'], $exclusionsList, true)) {
                        $exclusionsList[] = $item['url'];
                        $cacheSettings['exclude_urls'] = implode("\n", $exclusionsList);
                        $applied++;
                    }
                }
            }
        }

        if ($applied > 0) {
            $this->setOption('fp_ps_page_cache', $cacheSettings);
        }

        return $applied;
    }

    /**
     * Ottimizza le impostazioni di cache esistenti
     */
    private function optimizeCacheSettings(): int
    {
        $optimizations = 0;
        $cacheSettings = $this->getOption('fp_ps_page_cache', []);

        // 1. Abilita cache per utenti loggati se non ci sono esclusioni sensibili
        $sensitiveExclusions = $this->countSensitiveExclusions();
        if ($sensitiveExclusions < 5 && !isset($cacheSettings['cache_logged_in'])) {
            $cacheSettings['cache_logged_in'] = true;
            $optimizations++;
        }

        // 2. Ottimizza TTL basandosi sul tipo di sito
        $siteType = $this->detectSiteType();
        if ($siteType === 'ecommerce' && (!isset($cacheSettings['cache_ttl']) || $cacheSettings['cache_ttl'] > 3600)) {
            $cacheSettings['cache_ttl'] = 1800; // 30 minuti per e-commerce
            $optimizations++;
        } elseif ($siteType === 'blog' && (!isset($cacheSettings['cache_ttl']) || $cacheSettings['cache_ttl'] < 3600)) {
            $cacheSettings['cache_ttl'] = 7200; // 2 ore per blog
            $optimizations++;
        }

        // 3. Abilita compressione se non già attiva
        if (!isset($cacheSettings['enable_compression']) || !$cacheSettings['enable_compression']) {
            $cacheSettings['enable_compression'] = true;
            $optimizations++;
        }

        // 4. Configura headers cache appropriati
        if (!isset($cacheSettings['cache_headers'])) {
            $cacheSettings['cache_headers'] = [
                'Cache-Control' => 'public, max-age=3600',
                'Vary' => 'Accept-Encoding',
            ];
            $optimizations++;
        }

        if ($optimizations > 0) {
            $this->setOption('fp_ps_page_cache', $cacheSettings);
        }

        return $optimizations;
    }

    /**
     * Applica regole di cache avanzate
     */
    private function applyAdvancedCacheRules(): int
    {
        $rulesApplied = 0;
        $cacheSettings = $this->getOption('fp_ps_page_cache', []);

        // 1. Regole per pagine dinamiche
        $dynamicPages = $this->getDynamicPages();
        if (!empty($dynamicPages)) {
            $cacheSettings['dynamic_page_rules'] = $dynamicPages;
            $rulesApplied++;
        }

        // 2. Regole per mobile
        if (!isset($cacheSettings['mobile_cache_rules'])) {
            $cacheSettings['mobile_cache_rules'] = [
                'separate_mobile_cache' => true,
                'mobile_ttl' => 1800,
                'mobile_exclusions' => ['/checkout', '/cart', '/my-account'],
            ];
            $rulesApplied++;
        }

        // 3. Regole per query string
        if (!isset($cacheSettings['query_string_rules'])) {
            $cacheSettings['query_string_rules'] = [
                'ignore_utm' => true,
                'ignore_fbclid' => true,
                'ignore_gclid' => true,
                'cache_with_params' => ['page', 'paged'],
            ];
            $rulesApplied++;
        }

        // 4. Regole per cookies
        if (!isset($cacheSettings['cookie_rules'])) {
            $cacheSettings['cookie_rules'] = [
                'ignore_tracking_cookies' => true,
                'cache_with_cookies' => ['wordpress_test_cookie'],
                'exclude_cookies' => ['woocommerce_cart_hash', 'woocommerce_items_in_cart'],
            ];
            $rulesApplied++;
        }

        if ($rulesApplied > 0) {
            $this->setOption('fp_ps_page_cache', $cacheSettings);
        }

        return $rulesApplied;
    }

    /**
     * Conta esclusioni sensibili attive
     */
    private function countSensitiveExclusions(): int
    {
        $appliedExclusions = $this->smartDetector->getAppliedExclusions();
        $sensitiveCount = 0;

        foreach ($appliedExclusions as $exclusion) {
            if (strpos($exclusion['url'], '/checkout') !== false ||
                strpos($exclusion['url'], '/cart') !== false ||
                strpos($exclusion['url'], '/my-account') !== false ||
                strpos($exclusion['url'], '/login') !== false) {
                $sensitiveCount++;
            }
        }

        return $sensitiveCount;
    }

    /**
     * Rileva il tipo di sito
     */
    private function detectSiteType(): string
    {
        $activePlugins = get_option('active_plugins', []);

        // E-commerce
        if (in_array('woocommerce/woocommerce.php', $activePlugins) ||
            in_array('easy-digital-downloads/easy-digital-downloads.php', $activePlugins)) {
            return 'ecommerce';
        }

        // Blog/Content
        if (in_array('elementor/elementor.php', $activePlugins) ||
            in_array('gutenberg/gutenberg.php', $activePlugins)) {
            return 'blog';
        }

        // Corporate/Business
        if (in_array('contact-form-7/wp-contact-form-7.php', $activePlugins) ||
            in_array('wpforms-lite/wpforms.php', $activePlugins)) {
            return 'corporate';
        }

        return 'general';
    }

    /**
     * Ottieni pagine dinamiche che richiedono regole speciali
     */
    private function getDynamicPages(): array
    {
        $dynamicPages = [];

        // Pagine di ricerca
        $dynamicPages[] = [
            'pattern' => '/?s=*',
            'rule' => 'no_cache',
            'reason' => 'Search results are dynamic',
        ];

        // Pagine di categoria con filtri
        $dynamicPages[] = [
            'pattern' => '/category/*?filter=*',
            'rule' => 'no_cache',
            'reason' => 'Filtered category pages are dynamic',
        ];

        // Pagine con parametri di ordinamento
        $dynamicPages[] = [
            'pattern' => '/*?orderby=*',
            'rule' => 'short_ttl',
            'ttl' => 300, // 5 minuti
            'reason' => 'Sorted pages need short cache',
        ];

        return $dynamicPages;
    }

    /**
     * Valida configurazione cache
     */
    public function validateCacheConfiguration(): array
    {
        $issues = [];
        $suggestions = [];
        $cacheSettings = $this->getOption('fp_ps_page_cache', []);

        // 1. Controlla esclusioni duplicate
        $exclusions = $cacheSettings['exclude_urls'] ?? '';
        $exclusionsList = array_filter(explode("\n", $exclusions));
        $duplicates = array_diff_assoc($exclusionsList, array_unique($exclusionsList));
        
        if (!empty($duplicates)) {
            $issues[] = sprintf(
                __('Trovate %d esclusioni duplicate', 'fp-performance-suite'),
                count($duplicates)
            );
        }

        // 2. Controlla TTL appropriato
        $siteType = $this->detectSiteType();
        $currentTtl = $cacheSettings['cache_ttl'] ?? 3600;
        
        if ($siteType === 'ecommerce' && $currentTtl > 3600) {
            $suggestions[] = __('Per siti e-commerce, considera un TTL più breve (30-60 minuti)', 'fp-performance-suite');
        }

        // 3. Controlla compressione
        if (!($cacheSettings['enable_compression'] ?? false)) {
            $suggestions[] = __('Abilita la compressione per migliorare le performance', 'fp-performance-suite');
        }

        // 4. Controlla regole mobile
        if (!isset($cacheSettings['mobile_cache_rules'])) {
            $suggestions[] = __('Configura regole specifiche per dispositivi mobile', 'fp-performance-suite');
        }

        return [
            'issues' => $issues,
            'suggestions' => $suggestions,
            'score' => $this->calculateConfigurationScore($issues, $suggestions),
        ];
    }

    /**
     * Calcola score della configurazione
     */
    private function calculateConfigurationScore(array $issues, array $suggestions): int
    {
        $score = 100;
        $score -= count($issues) * 20; // -20 per ogni issue
        $score -= count($suggestions) * 5; // -5 per ogni suggerimento
        return max(0, $score);
    }

    /**
     * Genera report di configurazione cache
     */
    public function generateCacheReport(): array
    {
        $validation = $this->validateCacheConfiguration();
        $appliedExclusions = $this->smartDetector->getAppliedExclusions();
        $performanceReport = $this->performanceDetector->getPerformanceReport();

        return [
            'configuration_score' => $validation['score'],
            'issues' => $validation['issues'],
            'suggestions' => $validation['suggestions'],
            'exclusions' => [
                'total' => count($appliedExclusions),
                'automatic' => count(array_filter($appliedExclusions, fn($e) => $e['type'] === 'automatic')),
                'manual' => count(array_filter($appliedExclusions, fn($e) => $e['type'] === 'manual')),
                'performance_based' => count(array_filter($appliedExclusions, fn($e) => $e['type'] === 'performance_based')),
            ],
            'performance_impact' => [
                'problematic_pages' => $performanceReport['summary']['problematic_pages'],
                'avg_load_time' => $performanceReport['summary']['avg_load_time'],
                'optimization_potential' => $this->calculateOptimizationPotential($performanceReport),
            ],
            'recommendations' => $this->generateRecommendations($validation, $performanceReport),
        ];
    }

    /**
     * Calcola potenziale di ottimizzazione
     */
    private function calculateOptimizationPotential(array $performanceReport): string
    {
        $problematicPages = $performanceReport['summary']['problematic_pages'];
        $totalPages = $performanceReport['summary']['total_pages_analyzed'];

        if ($totalPages === 0) return 'unknown';

        $percentage = ($problematicPages / $totalPages) * 100;

        if ($percentage > 20) return 'high';
        if ($percentage > 10) return 'medium';
        return 'low';
    }

    /**
     * Genera raccomandazioni specifiche
     */
    private function generateRecommendations(array $validation, array $performanceReport): array
    {
        $recommendations = [];

        if ($validation['score'] < 70) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'fix_configuration_issues',
                'message' => __('Risolvi i problemi di configurazione identificati', 'fp-performance-suite'),
            ];
        }

        if ($performanceReport['summary']['problematic_pages'] > 5) {
            $recommendations[] = [
                'priority' => 'medium',
                'action' => 'review_problematic_pages',
                'message' => __('Rivedi le pagine problematiche per ottimizzazioni aggiuntive', 'fp-performance-suite'),
            ];
        }

        if ($performanceReport['summary']['avg_load_time'] > 2.0) {
            $recommendations[] = [
                'priority' => 'high',
                'action' => 'optimize_slow_pages',
                'message' => __('Ottimizza le pagine con tempi di caricamento elevati', 'fp-performance-suite'),
            ];
        }

        return $recommendations;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // CacheAutoConfigurator non ha hook specifici da registrare
        // È utilizzato principalmente per configurazione on-demand
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
}
