<?php
/**
 * Plugin Name: FP Performance Suite
 * Description: Modular performance suite for shared hosting
 * Version: 1.5.2
 * Author: Francesco Passeri
 */

// Prevenzione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica che il plugin non sia già caricato
if (class_exists('FP_Performance_Simple')) {
    return;
}

/**
 * Classe principale del plugin
 */
class FP_Performance_Simple {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'admin_init']);
    }
    
    public function add_menu() {
        add_menu_page(
            'FP Performance',
            'FP Performance',
            'manage_options',
            'fp-performance',
            [$this, 'dashboard_page']
        );
    }
    
    public function admin_init() {
        // Inizializzazione admin
    }
    
    public function dashboard_page() {
        echo '<div class="wrap"><h1>FP Performance Dashboard</h1><p>Plugin funzionante!</p></div>';
    }
}

// Inizializza il plugin
new FP_Performance_Simple();
?>
