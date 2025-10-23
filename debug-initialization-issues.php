<?php
/**
 * Script di debug per i problemi di inizializzazione
 * 
 * Questo script aiuta a identificare e risolvere i problemi
 * di inizializzazione multipla del plugin FP Performance Suite.
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Debug delle inizializzazioni del plugin
 */
function fp_debug_initialization_issues() {
    // Solo per admin e se WP_DEBUG è attivo
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    // Verifica se il plugin è caricato
    if (!class_exists('FP\\PerfSuite\\Utils\\InitializationMonitor')) {
        return;
    }
    
    $monitor = \FP\PerfSuite\Utils\InitializationMonitor::class;
    $stats = $monitor::getInitializationStats();
    
    // Se ci sono problemi di inizializzazione multipla, mostra un avviso
    if ($stats['has_multiple_attempts']) {
        add_action('admin_notices', function() use ($stats) {
            $log = $monitor::getInitializationLog();
            $logEntries = array_slice($log, -5); // Ultimi 5 tentativi
            
            echo '<div class="notice notice-warning">';
            echo '<p><strong>FP Performance Suite - Debug Info:</strong></p>';
            echo '<p>Rilevati ' . $stats['count'] . ' tentativi di inizializzazione:</p>';
            echo '<ul>';
            foreach ($logEntries as $entry) {
                echo '<li>' . esc_html($entry['source']) . ' - ' . esc_html($entry['status']) . '</li>';
            }
            echo '</ul>';
            echo '<p><em>Questo è normale durante il caricamento del plugin, ma se persiste potrebbe indicare un problema.</em></p>';
            echo '</div>';
        });
    }
}

// Esegui il debug solo in admin
add_action('admin_init', 'fp_debug_initialization_issues');

/**
 * Aggiunge informazioni di debug alla barra admin
 */
function fp_add_debug_info_to_admin_bar($wp_admin_bar) {
    // Solo se WP_DEBUG è attivo e l'utente è admin
    if (!defined('WP_DEBUG') || !WP_DEBUG || !current_user_can('manage_options')) {
        return;
    }
    
    // Verifica se il plugin è caricato
    if (!class_exists('FP\\PerfSuite\\Utils\\InitializationMonitor')) {
        return;
    }
    
    $monitor = \FP\PerfSuite\Utils\InitializationMonitor::class;
    $stats = $monitor::getInitializationStats();
    
    if ($stats['has_multiple_attempts']) {
        $wp_admin_bar->add_node([
            'id' => 'fp-perfsuite-debug',
            'title' => 'FP-PerfSuite Debug: ' . $stats['count'] . ' init attempts',
            'href' => '#',
            'meta' => [
                'title' => 'Click per dettagli debug inizializzazione plugin'
            ]
        ]);
    }
}

// Aggiungi alla barra admin
add_action('admin_bar_menu', 'fp_add_debug_info_to_admin_bar', 999);

/**
 * Log dettagliato delle inizializzazioni
 */
function fp_log_initialization_details() {
    // Solo se WP_DEBUG è attivo
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    // Verifica se il plugin è caricato
    if (!class_exists('FP\\PerfSuite\\Utils\\InitializationMonitor')) {
        return;
    }
    
    $monitor = \FP\PerfSuite\Utils\InitializationMonitor::class;
    $stats = $monitor::getInitializationStats();
    
    if ($stats['has_multiple_attempts']) {
        $log = $monitor::getInitializationLog();
        
        error_log('[FP-PerfSuite] Initialization Debug:');
        error_log('[FP-PerfSuite] Total attempts: ' . $stats['count']);
        error_log('[FP-PerfSuite] Currently initialized: ' . ($stats['initialized'] ? 'Yes' : 'No'));
        
        foreach ($log as $entry) {
            error_log(sprintf(
                '[FP-PerfSuite] Init attempt: %s from %s (status: %s)',
                date('Y-m-d H:i:s', $entry['timestamp']),
                $entry['source'],
                $entry['status']
            ));
        }
    }
}

// Log quando WordPress è completamente caricato
add_action('wp_loaded', 'fp_log_initialization_details', 999);
