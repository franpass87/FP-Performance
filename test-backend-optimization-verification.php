<?php
/**
 * Test di Verifica Backend Optimization
 * 
 * Verifica se le funzionalità della pagina Backend Optimization
 * sono realmente implementate e funzionanti.
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class BackendOptimizationVerification
{
    private $results = [];
    private $errors = [];

    public function __construct()
    {
        $this->runAllTests();
    }

    /**
     * Esegue tutti i test di verifica
     */
    public function runAllTests(): void
    {
        echo "<h1>🔍 Verifica Backend Optimization - FP Performance Suite</h1>\n";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->testBackendOptimizerClass();
        $this->testAdminBarOptimizations();
        $this->testHeartbeatOptimizations();
        $this->testDashboardWidgets();
        $this->testRevisionsLimit();
        $this->testAutosaveOptimization();
        $this->testEmojiDisable();
        $this->testEmbedsDisable();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Test 1: Verifica esistenza classe BackendOptimizer
     */
    private function testBackendOptimizerClass(): void
    {
        echo "<h2>1. 📋 Verifica Classe BackendOptimizer</h2>\n";
        
        if (class_exists('FP\PerfSuite\Services\Admin\BackendOptimizer')) {
            $this->addResult('✅ Classe BackendOptimizer trovata', 'success');
            
            // Test istanziazione
            try {
                $optimizer = new FP\PerfSuite\Services\Admin\BackendOptimizer();
                $this->addResult('✅ Istanziazione BackendOptimizer riuscita', 'success');
                
                // Test metodi principali
                $methods = ['getSettings', 'updateSettings', 'init', 'status'];
                foreach ($methods as $method) {
                    if (method_exists($optimizer, $method)) {
                        $this->addResult("✅ Metodo {$method}() trovato", 'success');
                    } else {
                        $this->addResult("❌ Metodo {$method}() mancante", 'error');
                    }
                }
                
            } catch (Exception $e) {
                $this->addResult('❌ Errore istanziazione: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->addResult('❌ Classe BackendOptimizer non trovata', 'error');
        }
    }

    /**
     * Test 2: Verifica ottimizzazioni Admin Bar
     */
    private function testAdminBarOptimizations(): void
    {
        echo "<h2>2. 🎨 Verifica Ottimizzazioni Admin Bar</h2>\n";
        
        // Test hook show_admin_bar
        if (has_filter('show_admin_bar')) {
            $this->addResult('✅ Hook show_admin_bar registrato', 'success');
        } else {
            $this->addResult('⚠️ Hook show_admin_bar non registrato', 'warning');
        }
        
        // Test hook admin_bar_menu
        if (has_action('admin_bar_menu')) {
            $this->addResult('✅ Hook admin_bar_menu registrato', 'success');
        } else {
            $this->addResult('⚠️ Hook admin_bar_menu non registrato', 'warning');
        }
        
        // Test metodi rimozione menu
        $optimizer = new FP\PerfSuite\Services\Admin\BackendOptimizer();
        $methods = [
            'removeWordPressLogo',
            'removeUpdatesMenu', 
            'removeCommentsMenu',
            'removeNewMenu',
            'removeCustomizeLink'
        ];
        
        foreach ($methods as $method) {
            if (method_exists($optimizer, $method)) {
                $this->addResult("✅ Metodo {$method}() implementato", 'success');
            } else {
                $this->addResult("❌ Metodo {$method}() mancante", 'error');
            }
        }
    }

    /**
     * Test 3: Verifica ottimizzazioni Heartbeat
     */
    private function testHeartbeatOptimizations(): void
    {
        echo "<h2>3. 💓 Verifica Ottimizzazioni Heartbeat</h2>\n";
        
        // Test hook heartbeat_settings
        if (has_filter('heartbeat_settings')) {
            $this->addResult('✅ Hook heartbeat_settings registrato', 'success');
        } else {
            $this->addResult('⚠️ Hook heartbeat_settings non registrato', 'warning');
        }
        
        // Test costante WP_POST_REVISIONS
        if (defined('WP_POST_REVISIONS')) {
            $this->addResult('✅ Costante WP_POST_REVISIONS definita: ' . WP_POST_REVISIONS, 'success');
        } else {
            $this->addResult('⚠️ Costante WP_POST_REVISIONS non definita', 'warning');
        }
    }

    /**
     * Test 4: Verifica rimozione widget dashboard
     */
    private function testDashboardWidgets(): void
    {
        echo "<h2>4. 📊 Verifica Widget Dashboard</h2>\n";
        
        // Test hook wp_dashboard_setup
        if (has_action('wp_dashboard_setup')) {
            $this->addResult('✅ Hook wp_dashboard_setup registrato', 'success');
        } else {
            $this->addResult('⚠️ Hook wp_dashboard_setup non registrato', 'warning');
        }
        
        // Test hook welcome_panel
        if (has_action('welcome_panel')) {
            $this->addResult('✅ Hook welcome_panel registrato', 'success');
        } else {
            $this->addResult('⚠️ Hook welcome_panel non registrato', 'warning');
        }
    }

    /**
     * Test 5: Verifica limitazione revisioni
     */
    private function testRevisionsLimit(): void
    {
        echo "<h2>5. 📝 Verifica Limitazione Revisioni</h2>\n";
        
        if (defined('WP_POST_REVISIONS')) {
            $limit = WP_POST_REVISIONS;
            if ($limit > 0 && $limit <= 10) {
                $this->addResult("✅ Limite revisioni ottimale: {$limit}", 'success');
            } else {
                $this->addResult("⚠️ Limite revisioni non ottimale: {$limit}", 'warning');
            }
        } else {
            $this->addResult('❌ Limite revisioni non impostato', 'error');
        }
    }

    /**
     * Test 6: Verifica ottimizzazione autosave
     */
    private function testAutosaveOptimization(): void
    {
        echo "<h2>6. 💾 Verifica Ottimizzazione Autosave</h2>\n";
        
        // Test se autosave è deregistrato
        if (!wp_script_is('autosave', 'registered')) {
            $this->addResult('✅ Script autosave deregistrato', 'success');
        } else {
            $this->addResult('⚠️ Script autosave ancora registrato', 'warning');
        }
    }

    /**
     * Test 7: Verifica disabilitazione emoji
     */
    private function testEmojiDisable(): void
    {
        echo "<h2>7. 😀 Verifica Disabilitazione Emoji</h2>\n";
        
        // Test hook emoji
        if (!has_action('admin_print_styles', 'print_emoji_styles')) {
            $this->addResult('✅ Hook emoji styles rimosso', 'success');
        } else {
            $this->addResult('⚠️ Hook emoji styles ancora attivo', 'warning');
        }
        
        if (!has_action('admin_print_scripts', 'print_emoji_detection_script')) {
            $this->addResult('✅ Hook emoji detection rimosso', 'success');
        } else {
            $this->addResult('⚠️ Hook emoji detection ancora attivo', 'warning');
        }
    }

    /**
     * Test 8: Verifica disabilitazione embeds
     */
    private function testEmbedsDisable(): void
    {
        echo "<h2>8. 🔗 Verifica Disabilitazione Embeds</h2>\n";
        
        // Test hook oembed
        if (!has_action('rest_api_init', 'wp_oembed_register_route')) {
            $this->addResult('✅ Hook oembed route rimosso', 'success');
        } else {
            $this->addResult('⚠️ Hook oembed route ancora attivo', 'warning');
        }
    }

    /**
     * Test impostazioni salvate
     */
    private function testSavedSettings(): void
    {
        echo "<h2>9. ⚙️ Verifica Impostazioni Salvate</h2>\n";
        
        $settings = get_option('fp_ps_backend_optimizer', []);
        
        if (empty($settings)) {
            $this->addResult('⚠️ Nessuna impostazione salvata', 'warning');
            return;
        }
        
        $this->addResult('✅ Impostazioni trovate: ' . count($settings) . ' opzioni', 'success');
        
        // Verifica impostazioni specifiche
        $checks = [
            'enabled' => 'Abilitazione generale',
            'optimize_heartbeat' => 'Ottimizzazione heartbeat',
            'limit_revisions' => 'Limitazione revisioni',
            'optimize_dashboard' => 'Ottimizzazione dashboard',
            'admin_bar' => 'Ottimizzazioni admin bar'
        ];
        
        foreach ($checks as $key => $description) {
            if (isset($settings[$key])) {
                $this->addResult("✅ {$description}: " . (is_array($settings[$key]) ? 'Array' : $settings[$key]), 'success');
            } else {
                $this->addResult("⚠️ {$description}: Non impostato", 'warning');
            }
        }
    }

    /**
     * Aggiunge risultato
     */
    private function addResult(string $message, string $type): void
    {
        $this->results[] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Aggiunge errore
     */
    private function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Mostra risultati
     */
    private function displayResults(): void
    {
        echo "<h2>📊 Risultati Verifica</h2>\n";
        
        $success = 0;
        $warnings = 0;
        $errors = 0;
        
        foreach ($this->results as $result) {
            $color = match($result['type']) {
                'success' => '#28a745',
                'warning' => '#ffc107', 
                'error' => '#dc3545',
                default => '#6c757d'
            };
            
            echo "<div style='color: {$color}; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>";
            echo $result['message'];
            echo "</div>\n";
            
            match($result['type']) {
                'success' => $success++,
                'warning' => $warnings++,
                'error' => $errors++
            };
        }
        
        echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>";
        echo "<h3>📈 Riepilogo</h3>";
        echo "<p><strong>✅ Successi:</strong> {$success}</p>";
        echo "<p><strong>⚠️ Avvisi:</strong> {$warnings}</p>";
        echo "<p><strong>❌ Errori:</strong> {$errors}</p>";
        
        $total = $success + $warnings + $errors;
        $score = $total > 0 ? round(($success / $total) * 100) : 0;
        
        echo "<p><strong>🎯 Score Funzionalità:</strong> {$score}%</p>";
        
        if ($score >= 80) {
            echo "<p style='color: #28a745; font-weight: bold;'>✅ Le funzionalità sono implementate correttamente!</p>";
        } elseif ($score >= 60) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Alcune funzionalità potrebbero non essere attive.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ Molte funzionalità non sono implementate o non funzionano.</p>";
        }
        
        echo "</div>";
    }

    /**
     * Test completo delle funzionalità
     */
    public function runCompleteTest(): array
    {
        $this->runAllTests();
        
        return [
            'results' => $this->results,
            'errors' => $this->errors,
            'summary' => [
                'total' => count($this->results),
                'success' => count(array_filter($this->results, fn($r) => $r['type'] === 'success')),
                'warnings' => count(array_filter($this->results, fn($r) => $r['type'] === 'warning')),
                'errors' => count(array_filter($this->results, fn($r) => $r['type'] === 'error'))
            ]
        ];
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_backend_optimization'])) {
    $verification = new BackendOptimizationVerification();
}
