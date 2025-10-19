<?php
/**
 * Test Specifico Modulo Cache
 * Verifica funzionalità page cache, browser cache, e cache headers
 */

if (!defined('ABSPATH')) {
    $wp_load = dirname(__DIR__, 3) . '/wp-load.php';
    if (file_exists($wp_load)) require_once $wp_load;
    else die("WordPress non trovato\n");
}

class FP_Cache_Module_Test {
    
    private $results = [];
    
    public function run() {
        echo "\n╔══════════════════════════════════════════════╗\n";
        echo "║   TEST MODULO CACHE - FP PERFORMANCE SUITE   ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";
        
        $this->testPageCacheClass();
        $this->testPageCacheSettings();
        $this->testCacheDirectory();
        $this->testCacheCreation();
        $this->testCacheInvalidation();
        $this->testBrowserCacheHeaders();
        $this->testHtaccessIntegration();
        $this->testCacheExclusions();
        $this->testCacheStatistics();
        
        $this->printResults();
    }
    
    private function testPageCacheClass() {
        echo "🧪 Test 1: Verifica Classe PageCache\n";
        echo "───────────────────────────────────────────\n";
        
        if (class_exists('FP\\PerfSuite\\Services\\Cache\\PageCache')) {
            $this->pass("Classe PageCache trovata");
            
            // Test metodi pubblici
            $methods = ['clear', 'purge', 'warmup', 'getStats'];
            $reflection = new ReflectionClass('FP\\PerfSuite\\Services\\Cache\\PageCache');
            
            foreach ($methods as $method) {
                if ($reflection->hasMethod($method)) {
                    $this->pass("Metodo $method() disponibile");
                } else {
                    $this->warning("Metodo $method() non trovato");
                }
            }
        } else {
            $this->fail("Classe PageCache NON trovata");
        }
        echo "\n";
    }
    
    private function testPageCacheSettings() {
        echo "🧪 Test 2: Verifica Impostazioni Cache\n";
        echo "───────────────────────────────────────────\n";
        
        $settings = [
            'fp_ps_cache_enabled' => 'Cache Abilitata',
            'fp_ps_cache_ttl' => 'Cache TTL',
            'fp_ps_cache_mobile' => 'Cache Mobile Separata',
            'fp_ps_cache_logged_in' => 'Cache Utenti Loggati'
        ];
        
        foreach ($settings as $option => $label) {
            $value = get_option($option, null);
            if ($value !== null) {
                $display = is_bool($value) ? ($value ? 'Sì' : 'No') : $value;
                $this->info("$label: $display");
            } else {
                $this->warning("$label: Non configurato");
            }
        }
        echo "\n";
    }
    
    private function testCacheDirectory() {
        echo "🧪 Test 3: Verifica Directory Cache\n";
        echo "───────────────────────────────────────────\n";
        
        $cache_dir = WP_CONTENT_DIR . '/cache/fp-performance/';
        
        if (is_dir($cache_dir)) {
            $this->pass("Directory cache esiste: $cache_dir");
            
            if (is_writable($cache_dir)) {
                $this->pass("Directory cache scrivibile");
                
                // Verifica permessi
                $perms = fileperms($cache_dir);
                $perms_oct = substr(sprintf('%o', $perms), -4);
                $this->info("Permessi directory: $perms_oct");
                
                if ($perms_oct === '0755' || $perms_oct === '0775') {
                    $this->pass("Permessi corretti");
                } else {
                    $this->warning("Permessi potrebbero non essere ottimali (consigliato: 0755)");
                }
            } else {
                $this->fail("Directory cache NON scrivibile");
            }
            
            // Conta file cache
            $cache_files = glob($cache_dir . '*.html');
            $count = $cache_files ? count($cache_files) : 0;
            $this->info("File in cache: $count");
            
            if ($count > 0) {
                // Analizza un file di esempio
                $sample = $cache_files[0];
                $size = filesize($sample);
                $age = time() - filemtime($sample);
                $this->info("File esempio: " . basename($sample));
                $this->info("Dimensione: " . $this->formatBytes($size));
                $this->info("Età: " . $this->formatTime($age));
            }
            
        } else {
            $this->warning("Directory cache non esiste ancora");
            $this->info("Verrà creata al primo utilizzo della cache");
        }
        echo "\n";
    }
    
    private function testCacheCreation() {
        echo "🧪 Test 4: Test Creazione Cache\n";
        echo "───────────────────────────────────────────\n";
        
        $cache_enabled = get_option('fp_ps_cache_enabled', false);
        
        if (!$cache_enabled) {
            $this->warning("Cache non abilitata - skip test creazione");
            echo "\n";
            return;
        }
        
        // Simula richiesta per generare cache
        $test_url = home_url('/');
        $this->info("URL test: $test_url");
        
        // Verifica se URL ha cache
        $cache_key = md5($test_url);
        $cache_file = WP_CONTENT_DIR . '/cache/fp-performance/' . $cache_key . '.html';
        
        if (file_exists($cache_file)) {
            $this->pass("File cache esiste per homepage");
            $this->info("File: " . basename($cache_file));
            
            // Leggi contenuto
            $content = file_get_contents($cache_file);
            if (!empty($content)) {
                $this->pass("Contenuto cache valido (" . $this->formatBytes(strlen($content)) . ")");
                
                // Verifica marker cache
                if (strpos($content, '<!-- Cached by FP Performance Suite') !== false) {
                    $this->pass("Marker cache trovato nell'HTML");
                }
            }
        } else {
            $this->info("Nessun file cache per homepage (normale se non ancora visitata)");
        }
        echo "\n";
    }
    
    private function testCacheInvalidation() {
        echo "🧪 Test 5: Test Invalidazione Cache\n";
        echo "───────────────────────────────────────────\n";
        
        // Verifica hook invalidazione
        $hooks = [
            'save_post' => 'Invalidazione al salvataggio post',
            'deleted_post' => 'Invalidazione alla cancellazione post',
            'switch_theme' => 'Invalidazione al cambio tema',
            'update_option' => 'Invalidazione all\'aggiornamento opzioni'
        ];
        
        foreach ($hooks as $hook => $description) {
            $has_action = has_action($hook);
            if ($has_action) {
                $this->pass("Hook $hook registrato");
            } else {
                $this->info("Hook $hook non registrato (potrebbe essere normale)");
            }
        }
        echo "\n";
    }
    
    private function testBrowserCacheHeaders() {
        echo "🧪 Test 6: Verifica Browser Cache Headers\n";
        echo "───────────────────────────────────────────\n";
        
        $browser_cache = get_option('fp_ps_browser_cache_enabled', false);
        $this->info("Browser cache headers: " . ($browser_cache ? 'Abilitati' : 'Disabilitati'));
        
        if ($browser_cache) {
            // Verifica configurazione
            $cache_times = get_option('fp_ps_browser_cache_times', []);
            if (!empty($cache_times)) {
                $this->pass("Configurazione tempi cache trovata");
                foreach ($cache_times as $type => $time) {
                    $this->info("$type: $time secondi");
                }
            }
        }
        echo "\n";
    }
    
    private function testHtaccessIntegration() {
        echo "🧪 Test 7: Verifica Integrazione .htaccess\n";
        echo "───────────────────────────────────────────\n";
        
        $htaccess_path = ABSPATH . '.htaccess';
        
        if (file_exists($htaccess_path)) {
            $this->pass("File .htaccess trovato");
            
            if (is_readable($htaccess_path)) {
                $this->pass("File .htaccess leggibile");
                
                $content = file_get_contents($htaccess_path);
                
                // Cerca sezione plugin
                if (strpos($content, '# BEGIN FP Performance Suite') !== false) {
                    $this->pass("Sezione plugin trovata in .htaccess");
                    
                    // Verifica regole specifiche
                    $rules = [
                        'mod_expires.c' => 'Modulo Expires',
                        'ExpiresByType' => 'Regole Expires',
                        'mod_headers.c' => 'Modulo Headers',
                        'Cache-Control' => 'Header Cache-Control'
                    ];
                    
                    foreach ($rules as $rule => $description) {
                        if (strpos($content, $rule) !== false) {
                            $this->pass("$description presente");
                        }
                    }
                } else {
                    $this->info("Sezione plugin non trovata in .htaccess");
                    $this->info("Normale se browser cache non abilitato");
                }
                
                // Verifica se writable per future modifiche
                if (is_writable($htaccess_path)) {
                    $this->pass("File .htaccess scrivibile");
                } else {
                    $this->warning("File .htaccess NON scrivibile");
                    $this->info("Potrebbe impedire l'applicazione automatica delle regole");
                }
            } else {
                $this->fail("File .htaccess NON leggibile");
            }
        } else {
            $this->warning("File .htaccess non trovato");
            $this->info("Alcuni server non usano .htaccess (es. Nginx)");
        }
        echo "\n";
    }
    
    private function testCacheExclusions() {
        echo "🧪 Test 8: Verifica Esclusioni Cache\n";
        echo "───────────────────────────────────────────\n";
        
        $exclusions = get_option('fp_ps_cache_exclusions', []);
        
        if (!empty($exclusions)) {
            $this->pass("Esclusioni configurate: " . count($exclusions));
            foreach ($exclusions as $exclusion) {
                $this->info("- $exclusion");
            }
        } else {
            $this->info("Nessuna esclusione configurata");
            $this->info("Esclusioni comuni: /carrello, /checkout, /account");
        }
        
        // Test esclusioni predefinite
        $default_exclusions = [
            'wp-admin',
            'wp-login.php',
            'xmlrpc.php'
        ];
        
        $this->info("Esclusioni predefinite WordPress:");
        foreach ($default_exclusions as $exclusion) {
            $this->info("- $exclusion");
        }
        echo "\n";
    }
    
    private function testCacheStatistics() {
        echo "🧪 Test 9: Statistiche Cache\n";
        echo "───────────────────────────────────────────\n";
        
        $stats = get_option('fp_ps_cache_stats', []);
        
        if (!empty($stats)) {
            $this->pass("Statistiche cache disponibili");
            
            $keys = ['hits', 'misses', 'size', 'count'];
            foreach ($keys as $key) {
                if (isset($stats[$key])) {
                    $value = $key === 'size' ? $this->formatBytes($stats[$key]) : $stats[$key];
                    $this->info(ucfirst($key) . ": $value");
                }
            }
            
            // Calcola hit rate
            if (isset($stats['hits']) && isset($stats['misses'])) {
                $total = $stats['hits'] + $stats['misses'];
                if ($total > 0) {
                    $hit_rate = round(($stats['hits'] / $total) * 100, 2);
                    $this->info("Hit Rate: $hit_rate%");
                    
                    if ($hit_rate >= 80) {
                        $this->pass("Eccellente hit rate!");
                    } elseif ($hit_rate >= 60) {
                        $this->pass("Buon hit rate");
                    } else {
                        $this->warning("Hit rate migliorabile");
                    }
                }
            }
        } else {
            $this->info("Statistiche non ancora disponibili");
            $this->info("Verranno generate dopo alcuni utilizzi");
        }
        echo "\n";
    }
    
    private function printResults() {
        echo "╔══════════════════════════════════════════════╗\n";
        echo "║              RIEPILOGO TEST CACHE            ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";
        
        $passed = count(array_filter($this->results, fn($r) => $r['type'] === 'pass'));
        $failed = count(array_filter($this->results, fn($r) => $r['type'] === 'fail'));
        $warnings = count(array_filter($this->results, fn($r) => $r['type'] === 'warning'));
        $total = count($this->results);
        
        echo "✅ Passed: $passed\n";
        echo "❌ Failed: $failed\n";
        echo "⚠️  Warnings: $warnings\n";
        echo "📊 Total: $total\n\n";
        
        if ($failed === 0) {
            echo "🎉 TUTTI I TEST SUPERATI!\n";
        } elseif ($failed < 3) {
            echo "✅ Maggior parte test superati con alcuni problemi\n";
        } else {
            echo "⚠️  Diversi problemi rilevati - revisione necessaria\n";
        }
        echo "\n";
    }
    
    // Helper methods
    private function pass($msg) {
        echo "✅ $msg\n";
        $this->results[] = ['type' => 'pass', 'message' => $msg];
    }
    
    private function fail($msg) {
        echo "❌ $msg\n";
        $this->results[] = ['type' => 'fail', 'message' => $msg];
    }
    
    private function warning($msg) {
        echo "⚠️  $msg\n";
        $this->results[] = ['type' => 'warning', 'message' => $msg];
    }
    
    private function info($msg) {
        echo "ℹ️  $msg\n";
        $this->results[] = ['type' => 'info', 'message' => $msg];
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function formatTime($seconds) {
        if ($seconds < 60) return $seconds . 's';
        if ($seconds < 3600) return floor($seconds/60) . 'm ' . ($seconds%60) . 's';
        return floor($seconds/3600) . 'h ' . floor(($seconds%3600)/60) . 'm';
    }
}

// Esegui test
$test = new FP_Cache_Module_Test();
$test->run();

