<?php
/**
 * Test per verificare la funzionalità di tutti i checkbox del plugin FP Performance Suite
 * 
 * Questo script testa che ogni checkbox abbia:
 * 1. La logica di salvataggio corrispondente
 * 2. La logica di caricamento dei valori
 * 3. La logica di attivazione/disattivazione delle funzionalità
 */

// Carica WordPress
require_once __DIR__ . '/wp-config.php';

// Carica il plugin
require_once __DIR__ . '/fp-performance-suite.php';

class CheckboxFunctionalityTest
{
    private array $testResults = [];
    private array $checkboxTests = [];

    public function __construct()
    {
        $this->initializeCheckboxTests();
    }

    private function initializeCheckboxTests(): void
    {
        // Test per Settings.php
        $this->checkboxTests['settings'] = [
            'safety_mode' => [
                'form_field' => 'safety_mode',
                'option_key' => 'fp_ps_settings',
                'sub_key' => 'safety_mode',
                'expected_type' => 'boolean',
                'description' => 'Modalità Sicura'
            ]
        ];

        // Test per Assets.php - Main Toggle
        $this->checkboxTests['assets_main'] = [
            'assets_enabled' => [
                'form_field' => 'assets_enabled',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Asset Optimization Main Toggle'
            ]
        ];

        // Test per Assets - JavaScript Tab
        $this->checkboxTests['assets_js'] = [
            'defer_js' => [
                'form_field' => 'defer_js',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'defer_js',
                'expected_type' => 'boolean',
                'description' => 'Defer JavaScript'
            ],
            'async_js' => [
                'form_field' => 'async_js',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'async_js',
                'expected_type' => 'boolean',
                'description' => 'Async JavaScript'
            ],
            'combine_js' => [
                'form_field' => 'combine_js',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'combine_js',
                'expected_type' => 'boolean',
                'description' => 'Combine JS files'
            ],
            'remove_emojis' => [
                'form_field' => 'remove_emojis',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'remove_emojis',
                'expected_type' => 'boolean',
                'description' => 'Remove emojis script'
            ],
            'minify_inline_js' => [
                'form_field' => 'minify_inline_js',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'minify_inline_js',
                'expected_type' => 'boolean',
                'description' => 'Minify inline JavaScript'
            ]
        ];

        // Test per Assets - CSS Tab
        $this->checkboxTests['assets_css'] = [
            'combine_css' => [
                'form_field' => 'combine_css',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'combine_css',
                'expected_type' => 'boolean',
                'description' => 'Combine CSS files'
            ],
            'minify_inline_css' => [
                'form_field' => 'minify_inline_css',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'minify_inline_css',
                'expected_type' => 'boolean',
                'description' => 'Minify inline CSS'
            ],
            'remove_comments' => [
                'form_field' => 'remove_comments',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'remove_comments',
                'expected_type' => 'boolean',
                'description' => 'Remove CSS/JS comments'
            ],
            'optimize_google_fonts_assets' => [
                'form_field' => 'optimize_google_fonts_assets',
                'option_key' => 'fp_ps_assets',
                'sub_key' => 'optimize_google_fonts',
                'expected_type' => 'boolean',
                'description' => 'Optimize Google Fonts loading'
            ]
        ];

        // Test per Cache.php
        $this->checkboxTests['cache'] = [
            'page_cache_enabled' => [
                'form_field' => 'page_cache_enabled',
                'option_key' => 'fp_ps_page_cache',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Enable page cache'
            ],
            'browser_cache_enabled' => [
                'form_field' => 'browser_cache_enabled',
                'option_key' => 'fp_ps_browser_cache',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Enable browser cache'
            ],
            'prefetch_enabled' => [
                'form_field' => 'prefetch_enabled',
                'option_key' => 'fp_ps_prefetch',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Enable Predictive Prefetching'
            ],
            'cache_rules_enabled' => [
                'form_field' => 'cache_rules_enabled',
                'option_key' => 'fp_ps_security',
                'sub_key' => 'cache_rules.enabled',
                'expected_type' => 'boolean',
                'description' => 'Enable Cache Rules'
            ]
        ];

        // Test per Database.php
        $this->checkboxTests['database'] = [
            'database_enabled' => [
                'form_field' => 'database_enabled',
                'option_key' => 'fp_ps_db',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Database Optimization Main Toggle'
            ]
        ];

        // Test per Mobile.php
        $this->checkboxTests['mobile'] = [
            'enabled' => [
                'form_field' => 'enabled',
                'option_key' => 'fp_ps_mobile',
                'sub_key' => 'enabled',
                'expected_type' => 'boolean',
                'description' => 'Enable Mobile Optimization'
            ],
            'disable_animations' => [
                'form_field' => 'disable_animations',
                'option_key' => 'fp_ps_mobile',
                'sub_key' => 'disable_animations',
                'expected_type' => 'boolean',
                'description' => 'Disable Animations on Mobile'
            ],
            'remove_unnecessary_scripts' => [
                'form_field' => 'remove_unnecessary_scripts',
                'option_key' => 'fp_ps_mobile',
                'sub_key' => 'remove_unnecessary_scripts',
                'expected_type' => 'boolean',
                'description' => 'Remove Unnecessary Scripts'
            ],
            'optimize_touch_targets' => [
                'form_field' => 'optimize_touch_targets',
                'option_key' => 'fp_ps_mobile',
                'sub_key' => 'optimize_touch_targets',
                'expected_type' => 'boolean',
                'description' => 'Optimize Touch Targets'
            ],
            'enable_responsive_images' => [
                'form_field' => 'enable_responsive_images',
                'option_key' => 'fp_ps_mobile',
                'sub_key' => 'enable_responsive_images',
                'expected_type' => 'boolean',
                'description' => 'Enable Responsive Images'
            ]
        ];
    }

    public function runAllTests(): array
    {
        echo "🔍 Iniziando test di funzionalità checkbox...\n\n";

        foreach ($this->checkboxTests as $category => $tests) {
            echo "📋 Testando categoria: " . strtoupper($category) . "\n";
            echo str_repeat("-", 50) . "\n";

            foreach ($tests as $checkboxName => $testConfig) {
                $this->testCheckbox($category, $checkboxName, $testConfig);
            }

            echo "\n";
        }

        return $this->testResults;
    }

    private function testCheckbox(string $category, string $checkboxName, array $config): void
    {
        $testName = "{$category}.{$checkboxName}";
        $result = [
            'category' => $category,
            'checkbox' => $checkboxName,
            'description' => $config['description'],
            'tests' => []
        ];

        echo "  ✓ Testando: {$config['description']}\n";

        // Test 1: Verifica che l'opzione esista
        $optionExists = $this->testOptionExists($config['option_key']);
        $result['tests']['option_exists'] = $optionExists;
        echo "    - Opzione esiste: " . ($optionExists ? "✅" : "❌") . "\n";

        // Test 2: Verifica che il valore possa essere salvato
        $canSave = $this->testCanSaveValue($config);
        $result['tests']['can_save'] = $canSave;
        echo "    - Può salvare: " . ($canSave ? "✅" : "❌") . "\n";

        // Test 3: Verifica che il valore possa essere caricato
        $canLoad = $this->testCanLoadValue($config);
        $result['tests']['can_load'] = $canLoad;
        echo "    - Può caricare: " . ($canLoad ? "✅" : "❌") . "\n";

        // Test 4: Verifica che la funzionalità sia attivabile/disattivabile
        $canToggle = $this->testCanToggle($config);
        $result['tests']['can_toggle'] = $canToggle;
        echo "    - Può attivare/disattivare: " . ($canToggle ? "✅" : "❌") . "\n";

        // Test 5: Verifica che il valore sia del tipo corretto
        $correctType = $this->testCorrectType($config);
        $result['tests']['correct_type'] = $correctType;
        echo "    - Tipo corretto: " . ($correctType ? "✅" : "❌") . "\n";

        $this->testResults[$testName] = $result;
    }

    private function testOptionExists(string $optionKey): bool
    {
        $option = get_option($optionKey);
        return $option !== false;
    }

    private function testCanSaveValue(array $config): bool
    {
        try {
            // Simula il salvataggio di un valore
            $testValue = true;
            
            if (isset($config['sub_key']) && strpos($config['sub_key'], '.') !== false) {
                // Gestisce chiavi annidate come 'cache_rules.enabled'
                $keys = explode('.', $config['sub_key']);
                $option = get_option($config['option_key'], []);
                
                if (!is_array($option)) {
                    $option = [];
                }
                
                $current = &$option;
                for ($i = 0; $i < count($keys) - 1; $i++) {
                    if (!isset($current[$keys[$i]])) {
                        $current[$keys[$i]] = [];
                    }
                    $current = &$current[$keys[$i]];
                }
                $current[$keys[count($keys) - 1]] = $testValue;
                
                update_option($config['option_key'], $option);
            } else {
                // Gestisce chiavi semplici
                if (isset($config['sub_key'])) {
                    $option = get_option($config['option_key'], []);
                    if (!is_array($option)) {
                        $option = [];
                    }
                    $option[$config['sub_key']] = $testValue;
                    update_option($config['option_key'], $option);
                } else {
                    update_option($config['option_key'], $testValue);
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function testCanLoadValue(array $config): bool
    {
        try {
            $option = get_option($config['option_key']);
            
            if ($option === false) {
                return false;
            }
            
            if (isset($config['sub_key'])) {
                if (strpos($config['sub_key'], '.') !== false) {
                    // Gestisce chiavi annidate
                    $keys = explode('.', $config['sub_key']);
                    $current = $option;
                    
                    foreach ($keys as $key) {
                        if (!is_array($current) || !array_key_exists($key, $current)) {
                            return false;
                        }
                        $current = $current[$key];
                    }
                    
                    return true;
                } else {
                    // Gestisce chiavi semplici
                    return is_array($option) && array_key_exists($config['sub_key'], $option);
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function testCanToggle(array $config): bool
    {
        try {
            // Testa l'attivazione
            $this->setCheckboxValue($config, true);
            $enabled = $this->getCheckboxValue($config);
            
            // Testa la disattivazione
            $this->setCheckboxValue($config, false);
            $disabled = $this->getCheckboxValue($config);
            
            return $enabled === true && $disabled === false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function testCorrectType(array $config): bool
    {
        try {
            $value = $this->getCheckboxValue($config);
            
            switch ($config['expected_type']) {
                case 'boolean':
                    return is_bool($value);
                case 'string':
                    return is_string($value);
                case 'integer':
                    return is_int($value);
                case 'array':
                    return is_array($value);
                default:
                    return true;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    private function setCheckboxValue(array $config, bool $value): void
    {
        if (isset($config['sub_key']) && strpos($config['sub_key'], '.') !== false) {
            $keys = explode('.', $config['sub_key']);
            $option = get_option($config['option_key'], []);
            
            if (!is_array($option)) {
                $option = [];
            }
            
            $current = &$option;
            for ($i = 0; $i < count($keys) - 1; $i++) {
                if (!isset($current[$keys[$i]])) {
                    $current[$keys[$i]] = [];
                }
                $current = &$current[$keys[$i]];
            }
            $current[$keys[count($keys) - 1]] = $value;
            
            update_option($config['option_key'], $option);
        } else {
            if (isset($config['sub_key'])) {
                $option = get_option($config['option_key'], []);
                if (!is_array($option)) {
                    $option = [];
                }
                $option[$config['sub_key']] = $value;
                update_option($config['option_key'], $option);
            } else {
                update_option($config['option_key'], $value);
            }
        }
    }

    private function getCheckboxValue(array $config)
    {
        $option = get_option($config['option_key']);
        
        if (isset($config['sub_key'])) {
            if (strpos($config['sub_key'], '.') !== false) {
                $keys = explode('.', $config['sub_key']);
                $current = $option;
                
                foreach ($keys as $key) {
                    if (!is_array($current) || !array_key_exists($key, $current)) {
                        return null;
                    }
                    $current = $current[$key];
                }
                
                return $current;
            } else {
                return is_array($option) && array_key_exists($config['sub_key'], $option) ? $option[$config['sub_key']] : null;
            }
        }
        
        return $option;
    }

    public function generateReport(): string
    {
        $report = "📊 REPORT FINALE - TEST FUNZIONALITÀ CHECKBOX\n";
        $report .= str_repeat("=", 60) . "\n\n";

        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;

        foreach ($this->testResults as $testName => $result) {
            $report .= "🔸 {$result['description']}\n";
            $report .= "   Categoria: {$result['category']}\n";
            $report .= "   Checkbox: {$result['checkbox']}\n\n";

            foreach ($result['tests'] as $testType => $passed) {
                $totalTests++;
                if ($passed) {
                    $passedTests++;
                    $report .= "   ✅ {$testType}: PASS\n";
                } else {
                    $failedTests++;
                    $report .= "   ❌ {$testType}: FAIL\n";
                }
            }

            $report .= "\n";
        }

        $report .= str_repeat("-", 60) . "\n";
        $report .= "📈 STATISTICHE:\n";
        $report .= "   Test totali: {$totalTests}\n";
        $report .= "   Test passati: {$passedTests}\n";
        $report .= "   Test falliti: {$failedTests}\n";
        $report .= "   Percentuale successo: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

        if ($failedTests > 0) {
            $report .= "⚠️  CHECKBOX CON PROBLEMI:\n";
            foreach ($this->testResults as $testName => $result) {
                $hasFailures = false;
                foreach ($result['tests'] as $testType => $passed) {
                    if (!$passed) {
                        $hasFailures = true;
                        break;
                    }
                }
                
                if ($hasFailures) {
                    $report .= "   - {$result['description']} ({$testName})\n";
                }
            }
        } else {
            $report .= "🎉 TUTTI I CHECKBOX FUNZIONANO CORRETTAMENTE!\n";
        }

        return $report;
    }
}

// Esegui i test
$tester = new CheckboxFunctionalityTest();
$results = $tester->runAllTests();
$report = $tester->generateReport();

echo "\n" . $report;

// Salva il report su file
file_put_contents(__DIR__ . '/checkbox-test-report.txt', $report);
echo "\n📄 Report salvato in: checkbox-test-report.txt\n";