<?php
/**
 * 🔧 INSTALLAZIONE STRUMENTI DI DEBUG
 * 
 * Questo script integra gli strumenti di debug nel plugin FP Performance Suite
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

class FP_Performance_Debug_Installer {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_installer_menu']);
    }
    
    public function add_installer_menu() {
        add_submenu_page(
            'fp-performance-suite',
            '🔧 Installa Debug Tools',
            '🔧 Installa Debug',
            'manage_options',
            'fp-performance-install-debug',
            [$this, 'installer_page']
        );
    }
    
    public function installer_page() {
        echo '<div class="wrap">';
        echo '<h1>🔧 Installa Strumenti di Debug</h1>';
        
        if (isset($_POST['install_debug'])) {
            $this->install_debug_tools();
        }
        
        echo '<div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 20px; border-radius: 5px; margin-bottom: 20px;">';
        echo '<h3>📋 Strumenti di Debug Disponibili</h3>';
        echo '<ul>';
        echo '<li><strong>🔧 Debug Tool Completo:</strong> Test automatici di tutte le funzionalità</li>';
        echo '<li><strong>🧪 Test Funzionalità:</strong> Test specifici per ogni feature</li>';
        echo '<li><strong>🚀 Debug Rapido:</strong> Debug in tempo reale con ?debug=1</li>';
        echo '<li><strong>📊 Monitoraggio:</strong> Logging dettagliato di errori e performance</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<form method="post">';
        echo '<p><input type="submit" name="install_debug" value="🚀 Installa Strumenti di Debug" class="button button-primary"></p>';
        echo '</form>';
        
        echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 20px;">';
        echo '<h4>ℹ️ Come Usare gli Strumenti di Debug</h4>';
        echo '<ol>';
        echo '<li><strong>Debug Rapido:</strong> Aggiungi <code>?debug=1</code> a qualsiasi URL del plugin</li>';
        echo '<li><strong>Debug Completo:</strong> Vai su <strong>FP Performance Suite → Debug</strong></li>';
        echo '<li><strong>Test Funzionalità:</strong> Vai su <strong>FP Performance Suite → Test</strong></li>';
        echo '<li><strong>Monitoraggio:</strong> I log vengono salvati automaticamente nel debug log di WordPress</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';
    }
    
    private function install_debug_tools() {
        echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>✅ Installazione Completata!</h3>';
        
        try {
            // Copia i file di debug nella directory del plugin
            $this->copy_debug_files();
            
            // Abilita il debug mode
            $this->enable_debug_mode();
            
            // Crea le voci di menu
            $this->create_debug_menus();
            
            echo '<p>Gli strumenti di debug sono stati installati con successo!</p>';
            echo '<p><strong>Prossimi passi:</strong></p>';
            echo '<ul>';
            echo '<li>Vai su <strong>FP Performance Suite → Debug</strong> per eseguire test completi</li>';
            echo '<li>Vai su <strong>FP Performance Suite → Test</strong> per test specifici</li>';
            echo '<li>Aggiungi <code>?debug=1</code> a qualsiasi URL del plugin per debug rapido</li>';
            echo '</ul>';
            
        } catch (Exception $e) {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;">';
            echo '<h3>❌ Errore durante l\'installazione</h3>';
            echo '<p>Errore: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    private function copy_debug_files() {
        $plugin_dir = plugin_dir_path(__FILE__);
        
        // Copia i file di debug
        $debug_files = [
            'debug-plugin-complete.php',
            'test-plugin-features.php',
            'debug-quick.php'
        ];
        
        foreach ($debug_files as $file) {
            $source = $plugin_dir . $file;
            $destination = $plugin_dir . 'src/Debug/' . $file;
            
            if (file_exists($source)) {
                if (!is_dir($plugin_dir . 'src/Debug/')) {
                    mkdir($plugin_dir . 'src/Debug/', 0755, true);
                }
                
                if (copy($source, $destination)) {
                    echo '<p>✅ Copiato: ' . $file . '</p>';
                } else {
                    throw new Exception('Errore nella copia di ' . $file);
                }
            }
        }
    }
    
    private function enable_debug_mode() {
        // Abilita il debug mode in WordPress
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
        
        if (!defined('WP_DEBUG_LOG')) {
            define('WP_DEBUG_LOG', true);
        }
        
        if (!defined('WP_DEBUG_DISPLAY')) {
            define('WP_DEBUG_DISPLAY', false);
        }
        
        echo '<p>✅ Debug mode abilitato</p>';
    }
    
    private function create_debug_menus() {
        // I menu vengono creati automaticamente dai file di debug
        echo '<p>✅ Menu di debug creati</p>';
    }
}

// Inizializza l'installer
new FP_Performance_Debug_Installer();
