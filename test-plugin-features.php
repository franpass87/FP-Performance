<?php
/**
 * 🧪 TEST RAPIDO FUNZIONALITÀ PLUGIN
 * 
 * Questo script testa rapidamente tutte le funzionalità del plugin
 * e genera un report dettagliato dei problemi
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

class FP_Performance_Feature_Tester {
    
    private $results = [];
    private $errors = [];
    private $warnings = [];
    private $success = [];
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_test_menu']);
    }
    
    public function add_test_menu() {
        add_submenu_page(
            'fp-performance-suite',
            '🧪 Test Funzionalità',
            '🧪 Test',
            'manage_options',
            'fp-performance-test',
            [$this, 'test_page']
        );
    }
    
    public function test_page() {
        echo '<div class="wrap">';
        echo '<h1>🧪 Test Funzionalità Plugin</h1>';
        
        if (isset($_POST['run_tests'])) {
            $this->run_all_tests();
        }
        
        echo '<form method="post">';
        echo '<p><input type="submit" name="run_tests" value="🚀 Esegui Tutti i Test" class="button button-primary"></p>';
        echo '</form>';
        
        if (!empty($this->results)) {
            $this->display_results();
        }
        
        echo '</div>';
    }
    
    private function run_all_tests() {
        $this->log('=== INIZIO TEST FUNZIONALITÀ ===');
        
        // Test 1: Assets Optimization
        $this->test_assets_optimization();
        
        // Test 2: Database Optimization
        $this->test_database_optimization();
        
        // Test 3: Mobile Optimization
        $this->test_mobile_optimization();
        
        // Test 4: Backend Optimization
        $this->test_backend_optimization();
        
        // Test 5: ML Features
        $this->test_ml_features();
        
        // Test 6: Form Saving
        $this->test_form_saving();
        
        // Test 7: Settings Management
        $this->test_settings_management();
        
        // Test 8: Performance Monitoring
        $this->test_performance_monitoring();
        
        $this->log('=== FINE TEST FUNZIONALITÀ ===');
    }
    
    private function test_assets_optimization() {
        $this->log('🧪 Test: Assets Optimization');
        
        try {
            // Test 1: Main Toggle
            $this->test_assets_main_toggle();
            
            // Test 2: JavaScript Optimization
            $this->test_javascript_optimization();
            
            // Test 3: CSS Optimization
            $this->test_css_optimization();
            
            // Test 4: Font Optimization
            $this->test_font_optimization();
            
            // Test 5: Third Party Scripts
            $this->test_third_party_scripts();
            
        } catch (Exception $e) {
            $this->add_error('Assets Optimization', $e->getMessage());
        }
    }
    
    private function test_assets_main_toggle() {
        $this->log('  📋 Test: Main Toggle');
        
        try {
            $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer();
            
            // Test salvataggio enabled
            $settings = $optimizer->settings();
            $settings['enabled'] = true;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('Main Toggle', 'Salvataggio enabled funzionante');
            } else {
                $this->add_error('Main Toggle', 'Salvataggio enabled fallito');
            }
            
            // Test salvataggio disabled
            $settings['enabled'] = false;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('Main Toggle', 'Salvataggio disabled funzionante');
            } else {
                $this->add_error('Main Toggle', 'Salvataggio disabled fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('Main Toggle', $e->getMessage());
        }
    }
    
    private function test_javascript_optimization() {
        $this->log('  📋 Test: JavaScript Optimization');
        
        try {
            $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer();
            
            // Test defer JS
            $settings = $optimizer->settings();
            $settings['defer_js'] = true;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('JavaScript', 'Defer JS funzionante');
            } else {
                $this->add_error('JavaScript', 'Defer JS fallito');
            }
            
            // Test async JS
            $settings['async_js'] = true;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('JavaScript', 'Async JS funzionante');
            } else {
                $this->add_error('JavaScript', 'Async JS fallito');
            }
            
            // Test combine JS
            $settings['combine_js'] = true;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('JavaScript', 'Combine JS funzionante');
            } else {
                $this->add_error('JavaScript', 'Combine JS fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('JavaScript', $e->getMessage());
        }
    }
    
    private function test_css_optimization() {
        $this->log('  📋 Test: CSS Optimization');
        
        try {
            $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer();
            
            // Test combine CSS
            $settings = $optimizer->settings();
            $settings['combine_css'] = true;
            $result = $optimizer->update($settings);
            
            if ($result) {
                $this->add_success('CSS', 'Combine CSS funzionante');
            } else {
                $this->add_error('CSS', 'Combine CSS fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('CSS', $e->getMessage());
        }
    }
    
    private function test_font_optimization() {
        $this->log('  📋 Test: Font Optimization');
        
        try {
            $font_optimizer = new \FP\PerfSuite\Services\Assets\FontOptimizer();
            
            $settings = $font_optimizer->getSettings();
            $settings['enabled'] = true;
            $result = $font_optimizer->updateSettings($settings);
            
            if ($result) {
                $this->add_success('Font', 'Font optimization funzionante');
            } else {
                $this->add_error('Font', 'Font optimization fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('Font', $e->getMessage());
        }
    }
    
    private function test_third_party_scripts() {
        $this->log('  📋 Test: Third Party Scripts');
        
        try {
            $third_party = new \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager();
            
            $settings = $third_party->settings();
            $settings['enabled'] = true;
            $result = $third_party->updateSettings($settings);
            
            if ($result) {
                $this->add_success('Third Party', 'Third party scripts funzionante');
            } else {
                $this->add_error('Third Party', 'Third party scripts fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('Third Party', $e->getMessage());
        }
    }
    
    private function test_database_optimization() {
        $this->log('🧪 Test: Database Optimization');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Database\Optimizer')) {
                $db_optimizer = new \FP\PerfSuite\Services\Database\Optimizer();
                $this->add_success('Database', 'Database optimizer disponibile');
            } else {
                $this->add_warning('Database', 'Database optimizer non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('Database', $e->getMessage());
        }
    }
    
    private function test_mobile_optimization() {
        $this->log('🧪 Test: Mobile Optimization');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Mobile\Optimizer')) {
                $mobile_optimizer = new \FP\PerfSuite\Services\Mobile\Optimizer();
                $this->add_success('Mobile', 'Mobile optimizer disponibile');
            } else {
                $this->add_warning('Mobile', 'Mobile optimizer non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('Mobile', $e->getMessage());
        }
    }
    
    private function test_backend_optimization() {
        $this->log('🧪 Test: Backend Optimization');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\Backend\Optimizer')) {
                $backend_optimizer = new \FP\PerfSuite\Services\Backend\Optimizer();
                $this->add_success('Backend', 'Backend optimizer disponibile');
            } else {
                $this->add_warning('Backend', 'Backend optimizer non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('Backend', $e->getMessage());
        }
    }
    
    private function test_ml_features() {
        $this->log('🧪 Test: ML Features');
        
        try {
            if (class_exists('\FP\PerfSuite\Services\ML\Optimizer')) {
                $ml_optimizer = new \FP\PerfSuite\Services\ML\Optimizer();
                $this->add_success('ML', 'ML optimizer disponibile');
            } else {
                $this->add_warning('ML', 'ML optimizer non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('ML', $e->getMessage());
        }
    }
    
    private function test_form_saving() {
        $this->log('🧪 Test: Form Saving');
        
        try {
            // Test Assets form
            $this->test_assets_form_saving();
            
            // Test Database form
            $this->test_database_form_saving();
            
            // Test Mobile form
            $this->test_mobile_form_saving();
            
        } catch (Exception $e) {
            $this->add_error('Form Saving', $e->getMessage());
        }
    }
    
    private function test_assets_form_saving() {
        $this->log('  📋 Test: Assets Form Saving');
        
        try {
            // Simula POST data
            $original_post = $_POST;
            
            $_POST = [
                'fp_ps_assets_nonce' => wp_create_nonce('fp-ps-assets'),
                'form_type' => 'main_toggle',
                'assets_enabled' => '1'
            ];
            
            $post_handler = new \FP\PerfSuite\Admin\Pages\Assets\Handlers\PostHandler();
            $settings = [];
            $font_settings = [];
            $third_party_settings = [];
            
            $message = $post_handler->handlePost($settings, $font_settings, $third_party_settings);
            
            if ($message && strpos($message, 'successfully') !== false) {
                $this->add_success('Assets Form', 'Salvataggio form Assets funzionante');
            } else {
                $this->add_error('Assets Form', 'Salvataggio form Assets fallito');
            }
            
            $_POST = $original_post;
            
        } catch (Exception $e) {
            $this->add_error('Assets Form', $e->getMessage());
        }
    }
    
    private function test_database_form_saving() {
        $this->log('  📋 Test: Database Form Saving');
        
        try {
            // Test se esiste il form handler per database
            if (class_exists('\FP\PerfSuite\Admin\Pages\Database\Handlers\PostHandler')) {
                $this->add_success('Database Form', 'Database form handler disponibile');
            } else {
                $this->add_warning('Database Form', 'Database form handler non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('Database Form', $e->getMessage());
        }
    }
    
    private function test_mobile_form_saving() {
        $this->log('  📋 Test: Mobile Form Saving');
        
        try {
            // Test se esiste il form handler per mobile
            if (class_exists('\FP\PerfSuite\Admin\Pages\Mobile\Handlers\PostHandler')) {
                $this->add_success('Mobile Form', 'Mobile form handler disponibile');
            } else {
                $this->add_warning('Mobile Form', 'Mobile form handler non disponibile');
            }
        } catch (Exception $e) {
            $this->add_error('Mobile Form', $e->getMessage());
        }
    }
    
    private function test_settings_management() {
        $this->log('🧪 Test: Settings Management');
        
        try {
            // Test salvataggio settings
            $test_option = 'fp_ps_test_settings_' . time();
            $test_value = ['test' => 'value', 'timestamp' => time()];
            
            $saved = update_option($test_option, $test_value);
            if ($saved) {
                $retrieved = get_option($test_option);
                if ($retrieved === $test_value) {
                    $this->add_success('Settings', 'Settings management funzionante');
                } else {
                    $this->add_error('Settings', 'Settings retrieval fallito');
                }
                delete_option($test_option);
            } else {
                $this->add_error('Settings', 'Settings saving fallito');
            }
            
        } catch (Exception $e) {
            $this->add_error('Settings', $e->getMessage());
        }
    }
    
    private function test_performance_monitoring() {
        $this->log('🧪 Test: Performance Monitoring');
        
        try {
            // Test se esistono le funzioni di monitoring
            if (function_exists('fp_ps_get_core_web_vitals')) {
                $this->add_success('Performance', 'Core Web Vitals functions disponibili');
            } else {
                $this->add_warning('Performance', 'Core Web Vitals functions non disponibili');
            }
            
        } catch (Exception $e) {
            $this->add_error('Performance', $e->getMessage());
        }
    }
    
    private function display_results() {
        echo '<div style="margin: 20px 0;">';
        
        // Statistiche
        $total_tests = count($this->success) + count($this->errors) + count($this->warnings);
        $success_rate = $total_tests > 0 ? round((count($this->success) / $total_tests) * 100, 2) : 0;
        
        echo '<div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
        echo '<h3>📊 Risultati Test</h3>';
        echo '<p><strong>Test Totali:</strong> ' . $total_tests . '</p>';
        echo '<p><strong>Successi:</strong> ' . count($this->success) . '</p>';
        echo '<p><strong>Errori:</strong> ' . count($this->errors) . '</p>';
        echo '<p><strong>Warning:</strong> ' . count($this->warnings) . '</p>';
        echo '<p><strong>Tasso di Successo:</strong> ' . $success_rate . '%</p>';
        echo '</div>';
        
        // Successi
        if (!empty($this->success)) {
            echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>✅ Successi</h3>';
            foreach ($this->success as $success) {
                echo '<p>' . $success . '</p>';
            }
            echo '</div>';
        }
        
        // Errori
        if (!empty($this->errors)) {
            echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>❌ Errori</h3>';
            foreach ($this->errors as $error) {
                echo '<p>' . $error . '</p>';
            }
            echo '</div>';
        }
        
        // Warning
        if (!empty($this->warnings)) {
            echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">';
            echo '<h3>⚠️ Warning</h3>';
            foreach ($this->warnings as $warning) {
                echo '<p>' . $warning . '</p>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    private function add_success($feature, $message) {
        $this->success[] = "✅ {$feature}: {$message}";
        $this->log("✅ {$feature}: {$message}");
    }
    
    private function add_error($feature, $message) {
        $this->errors[] = "❌ {$feature}: {$message}";
        $this->log("❌ {$feature}: {$message}");
    }
    
    private function add_warning($feature, $message) {
        $this->warnings[] = "⚠️ {$feature}: {$message}";
        $this->log("⚠️ {$feature}: {$message}");
    }
    
    private function log($message) {
        $this->results[] = $message;
        error_log("FP Performance Test: {$message}");
    }
}

// Inizializza il tester
new FP_Performance_Feature_Tester();
