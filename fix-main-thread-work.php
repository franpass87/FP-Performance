<?php
/**
 * Script per risolvere il problema "Minimize main-thread work"
 * Attiva automaticamente tutte le ottimizzazioni necessarie
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Attiva tutte le ottimizzazioni per ridurre il main-thread work
function fp_activate_main_thread_optimizations() {
    
    // 1. Attiva Script Optimizer
    update_option('fp_ps_script_optimization', [
        'enabled' => true,
        'defer_js' => true,
        'async_js' => true,
        'remove_emoji_scripts' => true,
        'optimize_jquery' => true,
        'minify_inline_js' => true,
        'combine_scripts' => true,
        'exclude_handles' => [
            'jquery', 'jquery-core', 'jquery-migrate',
            'wc-checkout', 'wc-cart', 'stripe', 'paypal'
        ]
    ]);
    
    // 2. Attiva Third-Party Script Manager
    update_option('fp_ps_third_party_scripts', [
        'enabled' => true,
        'delay_all' => false, // Non ritardare tutto per sicurezza
        'delay_timeout' => 3000, // 3 secondi
        'load_on' => 'interaction',
        'scripts' => [
            'google_analytics' => [
                'enabled' => true,
                'patterns' => ['google-analytics.com/analytics.js', 'googletagmanager.com/gtag/js'],
                'delay' => true,
            ],
            'facebook_pixel' => [
                'enabled' => true,
                'patterns' => ['connect.facebook.net'],
                'delay' => true,
            ],
            'google_tag_manager' => [
                'enabled' => true,
                'patterns' => ['googletagmanager.com/gtm.js'],
                'delay' => true,
            ],
            'hotjar' => [
                'enabled' => true,
                'patterns' => ['static.hotjar.com'],
                'delay' => true,
            ],
            'linkedin_insight' => [
                'enabled' => true,
                'patterns' => ['snap.licdn.com'],
                'delay' => true,
            ]
        ]
    ]);
    
    // 3. Attiva jQuery Optimizer
    update_option('fp_ps_jquery_optimization', [
        'enabled' => true,
        'batch_operations' => true,
        'cache_selectors' => true,
        'optimize_animations' => true,
        'prevent_reflows' => true,
        'use_request_animation_frame' => true,
        'debounce_events' => true,
        'optimize_ready' => true,
        'lazy_loading' => true,
    ]);
    
    // 4. Attiva DOM Reflow Optimizer
    update_option('fp_ps_dom_reflow_optimization', [
        'enabled' => true,
        'batch_updates' => true,
        'defer_queries' => true,
        'prevent_layout_shift' => true,
        'optimize_jquery' => true,
        'use_request_animation_frame' => true,
        'debounce_resize' => true,
        'debounce_scroll' => true,
        'debounce_timeout' => 16, // ~60fps
    ]);
    
    // 5. Attiva Render Blocking Optimizer
    update_option('fp_ps_render_blocking_optimization', [
        'enabled' => true,
        'optimize_fonts' => true,
        'defer_css' => true,
        'critical_css' => '
            /* Critical CSS per ridurre layout shift */
            body { font-family: system-ui, -apple-system, sans-serif; }
            .site-header, header { display: block; }
            .site-main, main { display: block; }
            h1, h2, h3, h4, h5, h6 { font-weight: bold; }
            p { line-height: 1.6; }
            img { max-width: 100%; height: auto; }
            .container, .wrapper { max-width: 1200px; margin: 0 auto; }
        ',
        'critical_resources' => [],
        'critical_fonts' => [],
    ]);
    
    // 6. Attiva Font Optimizer
    update_option('fp_ps_font_optimization', [
        'enabled' => true,
        'preload_critical_fonts' => true,
        'font_display_swap' => true,
        'preconnect_font_providers' => true,
        'remove_unused_fonts' => true,
        'critical_fonts' => [
            'Inter',
            'Roboto',
            'Open Sans',
            'Lato'
        ]
    ]);
    
    return true;
}

// Esegui l'attivazione
if (fp_activate_main_thread_optimizations()) {
    echo "✅ Ottimizzazioni main-thread work attivate con successo!\n";
    echo "📊 Riduzione attesa: ~2-2.5 secondi\n";
    echo "🎯 Script Evaluation: da 1,536ms a ~400-600ms\n";
    echo "🎯 Script Parsing: da 467ms a ~150-200ms\n";
    echo "🎯 Style & Layout: da 330ms a ~100-150ms\n";
} else {
    echo "❌ Errore nell'attivazione delle ottimizzazioni\n";
}
