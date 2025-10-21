<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;

/**
 * Admin Bar Integration
 * 
 * Aggiunge elementi alla barra admin di WordPress per accesso rapido alle funzionalità
 *
 * @package FP\PerfSuite\Admin
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AdminBar
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Inizializza l'integrazione con la admin bar
     */
    public function boot(): void
    {
        if (!is_admin_bar_showing()) {
            return;
        }

        add_action('admin_bar_menu', [$this, 'addMenuItems'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Aggiunge elementi al menu della admin bar
     */
    public function addMenuItems(\WP_Admin_Bar $wp_admin_bar): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Menu principale
        $wp_admin_bar->add_node([
            'id' => 'fp-performance',
            'title' => '<span class="ab-icon dashicons-performance"></span> FP Performance',
            'href' => admin_url('admin.php?page=fp-performance-suite'),
            'meta' => [
                'class' => 'fp-performance-menu',
            ],
        ]);

        // Cache
        $pageCache = $this->container->get(PageCache::class);
        $cacheStatus = $pageCache->status();
        $cacheIcon = $cacheStatus['enabled'] ? '✓' : '✗';
        
        $wp_admin_bar->add_node([
            'parent' => 'fp-performance',
            'id' => 'fp-cache',
            'title' => sprintf('%s Cache: %s', $cacheIcon, $cacheStatus['enabled'] ? __('Attiva', 'fp-performance-suite') : __('Disattiva', 'fp-performance-suite')),
            'href' => admin_url('admin.php?page=fp-performance-suite-cache'),
        ]);

        // Pulisci cache
        if ($cacheStatus['enabled']) {
            $wp_admin_bar->add_node([
                'parent' => 'fp-cache',
                'id' => 'fp-clear-cache',
                'title' => __('🗑️ Pulisci Cache', 'fp-performance-suite'),
                'href' => wp_nonce_url(admin_url('admin-post.php?action=fp_clear_cache'), 'fp_clear_cache'),
            ]);

            // Statistiche cache - usa status() invece di getStats()
            $fileCount = $cacheStatus['files'] ?? 0;
            
            $wp_admin_bar->add_node([
                'parent' => 'fp-cache',
                'id' => 'fp-cache-stats',
                'title' => sprintf(
                    __('📊 File in cache: %d', 'fp-performance-suite'),
                    $fileCount
                ),
                'meta' => [
                    'class' => 'fp-cache-stats-item',
                ],
            ]);
        }

        // Performance Monitor
        $perfMonitor = $this->container->get(PerformanceMonitor::class);
        if ($perfMonitor->isEnabled()) {
            $stats = $perfMonitor->getStats(1);
            
            $wp_admin_bar->add_node([
                'parent' => 'fp-performance',
                'id' => 'fp-perf-monitor',
                'title' => sprintf(
                    __('📈 Caricamento medio: %.2fs | Query: %.1f', 'fp-performance-suite'),
                    $stats['avg_load_time'],
                    $stats['avg_queries']
                ),
                'href' => admin_url('admin.php?page=fp-performance-suite-diagnostics'),
            ]);
        }

        // Quick Actions
        $wp_admin_bar->add_node([
            'parent' => 'fp-performance',
            'id' => 'fp-quick-actions',
            'title' => __('⚡ Azioni Rapide', 'fp-performance-suite'),
        ]);

        $wp_admin_bar->add_node([
            'parent' => 'fp-quick-actions',
            'id' => 'fp-optimize-db',
            'title' => __('🔧 Ottimizza Database', 'fp-performance-suite'),
            'href' => wp_nonce_url(admin_url('admin-post.php?action=fp_optimize_db'), 'fp_optimize_db'),
        ]);

        $wp_admin_bar->add_node([
            'parent' => 'fp-quick-actions',
            'id' => 'fp-test-speed',
            'title' => __('🚀 Test Velocità', 'fp-performance-suite'),
            'href' => admin_url('admin.php?page=fp-performance-suite-diagnostics&tab=speed-test'),
        ]);

        // Link documentazione
        $wp_admin_bar->add_node([
            'parent' => 'fp-performance',
            'id' => 'fp-docs',
            'title' => __('📚 Documentazione', 'fp-performance-suite'),
            'href' => 'https://francescopasseri.com/fp-performance-suite/docs',
            'meta' => [
                'target' => '_blank',
            ],
        ]);
    }

    /**
     * Carica gli assets per la admin bar
     */
    public function enqueueAssets(): void
    {
        if (!is_admin_bar_showing() || !current_user_can('manage_options')) {
            return;
        }

        wp_add_inline_style('admin-bar', '
            #wpadminbar .fp-performance-menu .ab-icon:before {
                content: "\f227";
                top: 2px;
            }
            #wpadminbar .fp-cache-stats-item {
                cursor: default !important;
                background: transparent !important;
            }
            #wpadminbar .fp-cache-stats-item:hover {
                background: rgba(255,255,255,0.1) !important;
            }
        ');
    }

    /**
     * Registra le azioni AJAX per la admin bar
     */
    public static function registerActions(): void
    {
        add_action('admin_post_fp_clear_cache', [self::class, 'handleClearCache']);
        add_action('admin_post_fp_optimize_db', [self::class, 'handleOptimizeDb']);
    }

    /**
     * Gestisce la pulizia della cache
     */
    public static function handleClearCache(): void
    {
        check_admin_referer('fp_clear_cache');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'fp-performance-suite'));
        }

        $container = \FP\PerfSuite\Plugin::container();
        $pageCache = $container->get(PageCache::class);
        
        $result = $pageCache->clear();

        $redirect = wp_get_referer() ?: admin_url('admin.php?page=fp-performance-suite');
        $redirect = add_query_arg('fp_cache_cleared', $result ? '1' : '0', $redirect);
        
        wp_safe_redirect($redirect);
        exit;
    }

    /**
     * Gestisce l'ottimizzazione del database
     */
    public static function handleOptimizeDb(): void
    {
        check_admin_referer('fp_optimize_db');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'fp-performance-suite'));
        }

        $container = \FP\PerfSuite\Plugin::container();
        $cleaner = $container->get(\FP\PerfSuite\Services\DB\Cleaner::class);
        
        // FIX BUG #20: optimizeTables() è privato, usa cleanup() pubblico
        $result = $cleaner->cleanup(['optimize_tables'], false);
        
        $tablesOptimized = 0;
        if (isset($result['optimize_tables']['tables'])) {
            $tablesOptimized = count($result['optimize_tables']['tables']);
        }

        $redirect = wp_get_referer() ?: admin_url('admin.php?page=fp-performance-suite-database');
        $redirect = add_query_arg([
            'fp_db_optimized' => '1',
            'tables_count' => $tablesOptimized,
        ], $redirect);
        
        wp_safe_redirect($redirect);
        exit;
    }
}

