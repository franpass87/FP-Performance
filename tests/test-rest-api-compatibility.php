<?php
/**
 * Test REST API Compatibility
 * 
 * Verifica che il plugin non interferisca con le REST API
 * 
 * @package FP\PerfSuite\Tests
 */

// Carica WordPress
require_once dirname(__DIR__, 4) . '/wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

class RestApiCompatibilityTest
{
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function run(): void
    {
        echo "\n";
        echo "========================================\n";
        echo "  TEST REST API COMPATIBILITY\n";
        echo "========================================\n\n";

        $this->testWordPressRestApi();
        $this->testPluginRestApi();
        $this->testCacheExclusion();
        $this->testMinificationExclusion();
        $this->testAjaxExclusion();

        $this->printResults();
    }

    private function testWordPressRestApi(): void
    {
        echo "Test 1: WordPress Core REST API...\n";
        
        $url = rest_url('wp/v2/posts');
        $response = wp_remote_get($url, [
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            $this->fail('REST API Error: ' . $response->get_error_message());
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code === 200) {
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->pass('WordPress REST API funziona correttamente');
            } else {
                $this->fail('Risposta non è JSON valido: ' . json_last_error_msg());
            }
        } else {
            $this->fail('HTTP Status Code: ' . $code);
        }
    }

    private function testPluginRestApi(): void
    {
        echo "\nTest 2: Plugin REST API (score)...\n";
        
        $url = rest_url('fp-ps/v1/score');
        
        // Simula una richiesta autenticata
        $user = wp_get_current_user();
        if (!$user || $user->ID === 0) {
            // Usa un admin se disponibile
            $admins = get_users(['role' => 'administrator', 'number' => 1]);
            if (!empty($admins)) {
                wp_set_current_user($admins[0]->ID);
            }
        }

        $nonce = wp_create_nonce('wp_rest');
        
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'X-WP-Nonce' => $nonce,
            ],
        ]);

        if (is_wp_error($response)) {
            $this->fail('Plugin REST API Error: ' . $response->get_error_message());
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        
        // 200 o 401/403 sono accettabili (dipende dall'autenticazione)
        if ($code === 200 || $code === 401 || $code === 403) {
            $this->pass('Plugin REST API risponde correttamente (HTTP ' . $code . ')');
        } else if ($code === 500) {
            $this->fail('Errore 500 - Il plugin sta interferendo!');
        } else {
            $this->fail('HTTP Status Code inatteso: ' . $code);
        }
    }

    private function testCacheExclusion(): void
    {
        echo "\nTest 3: Page Cache Exclusion per REST API...\n";

        // Simula una richiesta REST API
        $_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';
        define('REST_REQUEST_TEST', true);

        $pageCache = new \FP\PerfSuite\Services\Cache\PageCache(
            new \FP\PerfSuite\Utils\Fs(),
            new \FP\PerfSuite\Utils\Env()
        );

        // Usa reflection per accedere al metodo privato
        $reflection = new ReflectionClass($pageCache);
        $method = $reflection->getMethod('isCacheableRequest');
        $method->setAccessible(true);

        $isCacheable = $method->invoke($pageCache);

        if ($isCacheable === false) {
            $this->pass('Page Cache esclude correttamente le REST API');
        } else {
            $this->fail('Page Cache NON esclude le REST API!');
        }

        // Pulisci
        unset($_SERVER['REQUEST_URI']);
    }

    private function testMinificationExclusion(): void
    {
        echo "\nTest 4: HTML Minification Exclusion per REST API...\n";

        // Simula una richiesta REST API
        $_SERVER['REQUEST_URI'] = '/wp-json/fp-ps/v1/score';
        
        $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer(
            new \FP\PerfSuite\Utils\Semaphore()
        );

        // Usa reflection per accedere al metodo privato
        $reflection = new ReflectionClass($optimizer);
        $method = $reflection->getMethod('isRestOrAjaxRequest');
        $method->setAccessible(true);

        $isRestOrAjax = $method->invoke($optimizer);

        if ($isRestOrAjax === true) {
            $this->pass('Optimizer esclude correttamente le REST API');
        } else {
            $this->fail('Optimizer NON esclude le REST API!');
        }

        // Pulisci
        unset($_SERVER['REQUEST_URI']);
    }

    private function testAjaxExclusion(): void
    {
        echo "\nTest 5: AJAX Exclusion...\n";

        // Simula una richiesta AJAX
        $_SERVER['REQUEST_URI'] = '/wp-admin/admin-ajax.php';
        define('DOING_AJAX_TEST', true);

        $optimizer = new \FP\PerfSuite\Services\Assets\Optimizer(
            new \FP\PerfSuite\Utils\Semaphore()
        );

        $reflection = new ReflectionClass($optimizer);
        $method = $reflection->getMethod('isRestOrAjaxRequest');
        $method->setAccessible(true);

        $isRestOrAjax = $method->invoke($optimizer);

        if ($isRestOrAjax === true) {
            $this->pass('Optimizer esclude correttamente le richieste AJAX');
        } else {
            $this->fail('Optimizer NON esclude le richieste AJAX!');
        }

        unset($_SERVER['REQUEST_URI']);
    }

    private function pass(string $message): void
    {
        $this->passed++;
        echo "  ✅ PASS: $message\n";
    }

    private function fail(string $message): void
    {
        $this->failed++;
        echo "  ❌ FAIL: $message\n";
    }

    private function printResults(): void
    {
        echo "\n========================================\n";
        echo "  RISULTATI\n";
        echo "========================================\n";
        echo "Passati: {$this->passed}\n";
        echo "Falliti: {$this->failed}\n";
        echo "Totale:  " . ($this->passed + $this->failed) . "\n";
        
        if ($this->failed === 0) {
            echo "\n✅ TUTTI I TEST SUPERATI!\n\n";
            exit(0);
        } else {
            echo "\n❌ ALCUNI TEST FALLITI\n\n";
            exit(1);
        }
    }
}

// Esegui i test
$test = new RestApiCompatibilityTest();
$test->run();

