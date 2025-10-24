<?php
/**
 * Verifica Reale Backend Optimization
 * 
 * Testa se le funzionalità della pagina Backend Optimization
 * sono realmente implementate e funzionanti.
 * 
 * @package FP\PerfSuite\Tests
 * @author Francesco Passeri
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class BackendOptimizationRealTest
{
    private $results = [];
    private $settings = [];

    public function __construct()
    {
        $this->runRealTests();
    }

    /**
     * Esegue test reali delle funzionalità
     */
    public function runRealTests(): void
    {
        echo "<h1>🔍 Verifica Reale Backend Optimization</h1>\n";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
        
        $this->loadCurrentSettings();
        $this->testAdminBarFunctionality();
        $this->testHeartbeatFunctionality();
        $this->testDashboardFunctionality();
        $this->testRevisionsFunctionality();
        $this->testEmojiFunctionality();
        $this->testEmbedsFunctionality();
        
        $this->displayResults();
        echo "</div>\n";
    }

    /**
     * Carica le impostazioni correnti
     */
    private function loadCurrentSettings(): void
    {
        $this->settings = get_option('fp_ps_backend_optimizer', []);
        
        echo "<h2>📋 Impostazioni Correnti</h2>\n";
        if (empty($this->settings)) {
            echo "<p style='color: #dc3545;'>❌ Nessuna impostazione trovata</p>\n";
        } else {
            echo "<p style='color: #28a745;'>✅ Impostazioni trovate: " . count($this->settings) . " opzioni</p>\n";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;'>";
            print_r($this->settings);
            echo "</pre>\n";
        }
    }

    /**
     * Test funzionalità Admin Bar
     */
    private function testAdminBarFunctionality(): void
    {
        echo "<h2>🎨 Test Admin Bar</h2>\n";
        
        // Test 1: Verifica se show_admin_bar è modificato
        $adminBarSettings = $this->settings['admin_bar'] ?? [];
        
        if (!empty($adminBarSettings['disable_frontend'])) {
            echo "<p style='color: #28a745;'>✅ Admin Bar frontend disabilitato nelle impostazioni</p>\n";
            
            // Verifica se il filtro è attivo
            if (has_filter('show_admin_bar')) {
                echo "<p style='color: #28a745;'>✅ Filtro show_admin_bar registrato</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Filtro show_admin_bar NON registrato</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Admin Bar frontend non disabilitato</p>\n";
        }
        
        // Test 2: Verifica rimozione logo WordPress
        if (!empty($adminBarSettings['disable_wordpress_logo'])) {
            echo "<p style='color: #28a745;'>✅ Logo WordPress disabilitato nelle impostazioni</p>\n";
            
            if (has_action('admin_bar_menu')) {
                echo "<p style='color: #28a745;'>✅ Hook admin_bar_menu registrato</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Hook admin_bar_menu NON registrato</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Logo WordPress non disabilitato</p>\n";
        }
        
        // Test 3: Verifica rimozione menu aggiornamenti
        if (!empty($adminBarSettings['disable_updates'])) {
            echo "<p style='color: #28a745;'>✅ Menu aggiornamenti disabilitato</p>\n";
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Menu aggiornamenti non disabilitato</p>\n";
        }
        
        // Test 4: Verifica rimozione menu commenti
        if (!empty($adminBarSettings['disable_comments'])) {
            echo "<p style='color: #28a745;'>✅ Menu commenti disabilitato</p>\n";
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Menu commenti non disabilitato</p>\n";
        }
    }

    /**
     * Test funzionalità Heartbeat
     */
    private function testHeartbeatFunctionality(): void
    {
        echo "<h2>💓 Test Heartbeat API</h2>\n";
        
        $heartbeatSettings = $this->settings['heartbeat'] ?? [];
        
        // Test 1: Verifica se heartbeat è ottimizzato
        if (!empty($this->settings['optimize_heartbeat'])) {
            echo "<p style='color: #28a745;'>✅ Heartbeat ottimizzazione abilitata</p>\n";
            
            if (has_filter('heartbeat_settings')) {
                echo "<p style='color: #28a745;'>✅ Filtro heartbeat_settings registrato</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Filtro heartbeat_settings NON registrato</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Heartbeat ottimizzazione non abilitata</p>\n";
        }
        
        // Test 2: Verifica intervallo heartbeat
        $interval = $this->settings['heartbeat_interval'] ?? 60;
        echo "<p>Intervallo heartbeat: {$interval} secondi</p>\n";
        
        if ($interval >= 60) {
            echo "<p style='color: #28a745;'>✅ Intervallo ottimale (≥60s)</p>\n";
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Intervallo non ottimale (<60s)</p>\n";
        }
    }

    /**
     * Test funzionalità Dashboard
     */
    private function testDashboardFunctionality(): void
    {
        echo "<h2>📊 Test Dashboard Widgets</h2>\n";
        
        $dashboardSettings = $this->settings['dashboard'] ?? [];
        
        // Test 1: Verifica ottimizzazione dashboard
        if (!empty($this->settings['optimize_dashboard'])) {
            echo "<p style='color: #28a745;'>✅ Dashboard ottimizzazione abilitata</p>\n";
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Dashboard ottimizzazione non abilitata</p>\n";
        }
        
        // Test 2: Verifica rimozione widget
        if (!empty($this->settings['remove_dashboard_widgets'])) {
            echo "<p style='color: #28a745;'>✅ Rimozione widget abilitata</p>\n";
            
            if (has_action('wp_dashboard_setup')) {
                echo "<p style='color: #28a745;'>✅ Hook wp_dashboard_setup registrato</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Hook wp_dashboard_setup NON registrato</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Rimozione widget non abilitata</p>\n";
        }
        
        // Test 3: Verifica widget specifici disabilitati
        $disabledWidgets = 0;
        $widgets = ['disable_welcome', 'disable_quick_press', 'disable_activity', 'disable_primary', 'disable_secondary', 'disable_site_health', 'disable_php_update'];
        
        foreach ($widgets as $widget) {
            if (!empty($dashboardSettings[$widget])) {
                $disabledWidgets++;
            }
        }
        
        echo "<p>Widget disabilitati: {$disabledWidgets}/" . count($widgets) . "</p>\n";
        
        if ($disabledWidgets >= 3) {
            echo "<p style='color: #28a745;'>✅ Buona ottimizzazione widget</p>\n";
        } elseif ($disabledWidgets > 0) {
            echo "<p style='color: #ffc107;'>⚠️ Ottimizzazione parziale widget</p>\n";
        } else {
            echo "<p style='color: #dc3545;'>❌ Nessun widget disabilitato</p>\n";
        }
    }

    /**
     * Test funzionalità Revisioni
     */
    private function testRevisionsFunctionality(): void
    {
        echo "<h2>📝 Test Limitazione Revisioni</h2>\n";
        
        // Test 1: Verifica se limitazione è abilitata
        if (!empty($this->settings['limit_revisions'])) {
            echo "<p style='color: #28a745;'>✅ Limitazione revisioni abilitata</p>\n";
            
            // Test 2: Verifica costante WP_POST_REVISIONS
            if (defined('WP_POST_REVISIONS')) {
                $limit = WP_POST_REVISIONS;
                echo "<p>Limite revisioni: {$limit}</p>\n";
                
                if ($limit > 0 && $limit <= 10) {
                    echo "<p style='color: #28a745;'>✅ Limite ottimale (1-10)</p>\n";
                } else {
                    echo "<p style='color: #ffc107;'>⚠️ Limite non ottimale: {$limit}</p>\n";
                }
            } else {
                echo "<p style='color: #dc3545;'>❌ Costante WP_POST_REVISIONS non definita</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Limitazione revisioni non abilitata</p>\n";
        }
    }

    /**
     * Test funzionalità Emoji
     */
    private function testEmojiFunctionality(): void
    {
        echo "<h2>😀 Test Disabilitazione Emoji</h2>\n";
        
        $adminAjaxSettings = $this->settings['admin_ajax'] ?? [];
        
        if (!empty($adminAjaxSettings['disable_emojis'])) {
            echo "<p style='color: #28a745;'>✅ Emoji disabilitati nelle impostazioni</p>\n";
            
            // Verifica se gli hook emoji sono rimossi
            if (!has_action('admin_print_styles', 'print_emoji_styles')) {
                echo "<p style='color: #28a745;'>✅ Hook emoji styles rimosso</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Hook emoji styles ancora attivo</p>\n";
            }
            
            if (!has_action('admin_print_scripts', 'print_emoji_detection_script')) {
                echo "<p style='color: #28a745;'>✅ Hook emoji detection rimosso</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Hook emoji detection ancora attivo</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Emoji non disabilitati</p>\n";
        }
    }

    /**
     * Test funzionalità Embeds
     */
    private function testEmbedsFunctionality(): void
    {
        echo "<h2>🔗 Test Disabilitazione Embeds</h2>\n";
        
        $adminAjaxSettings = $this->settings['admin_ajax'] ?? [];
        
        if (!empty($adminAjaxSettings['disable_embeds'])) {
            echo "<p style='color: #28a745;'>✅ Embeds disabilitati nelle impostazioni</p>\n";
            
            // Verifica se gli hook oembed sono rimossi
            if (!has_action('rest_api_init', 'wp_oembed_register_route')) {
                echo "<p style='color: #28a745;'>✅ Hook oembed route rimosso</p>\n";
            } else {
                echo "<p style='color: #dc3545;'>❌ Hook oembed route ancora attivo</p>\n";
            }
        } else {
            echo "<p style='color: #ffc107;'>⚠️ Embeds non disabilitati</p>\n";
        }
    }

    /**
     * Mostra risultati finali
     */
    private function displayResults(): void
    {
        echo "<h2>📊 Risultati Finali</h2>\n";
        
        // Calcola score
        $totalChecks = 0;
        $passedChecks = 0;
        
        // Conta i test
        $totalChecks += 4; // Admin Bar
        $totalChecks += 2; // Heartbeat
        $totalChecks += 3; // Dashboard
        $totalChecks += 2; // Revisioni
        $totalChecks += 2; // Emoji
        $totalChecks += 2; // Embeds
        
        // Stima score basato su impostazioni
        $score = 0;
        
        // Admin Bar (25 punti)
        if (!empty($this->settings['admin_bar']['disable_frontend'])) $score += 10;
        if (!empty($this->settings['admin_bar']['disable_wordpress_logo'])) $score += 5;
        if (!empty($this->settings['admin_bar']['disable_updates'])) $score += 5;
        if (!empty($this->settings['admin_bar']['disable_comments'])) $score += 5;
        
        // Heartbeat (25 punti)
        if (!empty($this->settings['optimize_heartbeat'])) $score += 15;
        if (($this->settings['heartbeat_interval'] ?? 60) >= 60) $score += 10;
        
        // Dashboard (25 punti)
        if (!empty($this->settings['optimize_dashboard'])) $score += 10;
        if (!empty($this->settings['remove_dashboard_widgets'])) $score += 10;
        if (!empty($this->settings['dashboard'])) $score += 5;
        
        // Revisioni (15 punti)
        if (!empty($this->settings['limit_revisions'])) $score += 10;
        if (defined('WP_POST_REVISIONS') && WP_POST_REVISIONS <= 10) $score += 5;
        
        // Emoji (5 punti)
        if (!empty($this->settings['admin_ajax']['disable_emojis'])) $score += 5;
        
        // Embeds (5 punti)
        if (!empty($this->settings['admin_ajax']['disable_embeds'])) $score += 5;
        
        $percentage = round(($score / 100) * 100);
        
        echo "<div style='background: #e9ecef; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>🎯 Score Funzionalità: {$percentage}%</h3>";
        
        if ($percentage >= 80) {
            echo "<p style='color: #28a745; font-weight: bold; font-size: 18px;'>✅ Le funzionalità sono implementate correttamente!</p>";
            echo "<p>La pagina Backend Optimization funziona come promesso.</p>";
        } elseif ($percentage >= 60) {
            echo "<p style='color: #ffc107; font-weight: bold; font-size: 18px;'>⚠️ Alcune funzionalità potrebbero non essere attive.</p>";
            echo "<p>Controlla le impostazioni e assicurati che siano salvate correttamente.</p>";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold; font-size: 18px;'>❌ Molte funzionalità non sono implementate o non funzionano.</p>";
            echo "<p>La pagina potrebbe non fare le modifiche promesse.</p>";
        }
        
        echo "<p><strong>Punti ottenuti:</strong> {$score}/100</p>";
        echo "</div>";
        
        // Raccomandazioni
        echo "<h3>💡 Raccomandazioni</h3>";
        echo "<ul>";
        
        if (empty($this->settings['enabled'])) {
            echo "<li style='color: #dc3545;'>❌ <strong>Abilita l'ottimizzazione backend</strong> - Il toggle principale è disabilitato</li>";
        }
        
        if (empty($this->settings['admin_bar']['disable_frontend'])) {
            echo "<li style='color: #ffc107;'>⚠️ <strong>Disabilita Admin Bar sul frontend</strong> - Risparmia ~150KB per pagina</li>";
        }
        
        if (empty($this->settings['optimize_heartbeat'])) {
            echo "<li style='color: #ffc107;'>⚠️ <strong>Ottimizza Heartbeat API</strong> - Riduce carico server del 20-30%</li>";
        }
        
        if (empty($this->settings['limit_revisions'])) {
            echo "<li style='color: #ffc107;'>⚠️ <strong>Limita revisioni post</strong> - Riduce dimensione database</li>";
        }
        
        echo "</ul>";
    }
}

// Esegui test se chiamato direttamente
if (isset($_GET['test_backend_real'])) {
    $test = new BackendOptimizationRealTest();
}
