<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;
use function is_admin;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function preg_match_all;
use function str_replace;
use function file_get_contents;

/**
 * JavaScript Tree Shaker
 *
 * Implements tree shaking techniques to remove unused JavaScript:
 * - Dead code elimination
 * - Unused function removal
 * - Unused variable removal
 * - Unused import removal
 * - Bundle size optimization
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class JavaScriptTreeShaker
{
    private const OPTION = 'fp_ps_js_tree_shaking';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Don't optimize in admin
        if (is_admin()) {
            return;
        }

        // Add tree shaking filters
        add_filter('script_loader_tag', [$this, 'shakeScriptTag'], 10, 3);
        add_action('wp_footer', [$this, 'injectTreeShakingScript'], 1);
        add_action('wp_head', [$this, 'injectTreeShakingHints'], 1);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,dead_code_elimination:bool,unused_functions:bool,unused_variables:bool,unused_imports:bool,aggressive_mode:bool,exclude_patterns:array}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'dead_code_elimination' => true,
            'unused_functions' => true,
            'unused_variables' => true,
            'unused_imports' => true,
            'aggressive_mode' => false,
            'exclude_patterns' => [
                'jquery', 'jquery-core', 'jquery-migrate',
                'wc-checkout', 'wc-cart', 'wc-cart-fragments',
                'stripe', 'stripe-js', 'paypal-sdk',
                'contact-form-7', 'wpcf7-recaptcha',
                'elementor-frontend', 'elementor-pro-frontend',
            ],
            'preserve_patterns' => [
                'init', 'ready', 'load', 'start',
                'checkout', 'payment', 'form', 'validation',
                'ajax', 'api', 'rest', 'endpoint',
            ],
            'minification_threshold' => 10000, // 10KB
            'compression_level' => 6,
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'dead_code_elimination' => !empty($settings['dead_code_elimination']),
            'unused_functions' => !empty($settings['unused_functions']),
            'unused_variables' => !empty($settings['unused_variables']),
            'unused_imports' => !empty($settings['unused_imports']),
            'aggressive_mode' => !empty($settings['aggressive_mode']),
            'exclude_patterns' => $settings['exclude_patterns'] ?? $current['exclude_patterns'],
            'preserve_patterns' => $settings['preserve_patterns'] ?? $current['preserve_patterns'],
            'minification_threshold' => (int)($settings['minification_threshold'] ?? $current['minification_threshold']),
            'compression_level' => (int)($settings['compression_level'] ?? $current['compression_level']),
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Shake script tag for tree shaking
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    public function shakeScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Skip excluded patterns
        if ($this->shouldExcludeScript($handle, $src, $settings)) {
            return $tag;
        }

        // Apply tree shaking attributes
        $tag = $this->addTreeShakingAttributes($tag, $handle, $src, $settings);

        // Apply minification if needed
        if ($this->shouldMinify($src, $settings)) {
            $tag = $this->addMinificationAttributes($tag, $handle, $src);
        }

        return $tag;
    }

    /**
     * Check if script should be excluded
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @param array $settings Settings
     * @return bool
     */
    private function shouldExcludeScript(string $handle, string $src, array $settings): bool
    {
        foreach ($settings['exclude_patterns'] as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script should be minified
     *
     * @param string $src Script source
     * @param array $settings Settings
     * @return bool
     */
    private function shouldMinify(string $src, array $settings): bool
    {
        // Check if script is large enough for minification
        $response = wp_remote_head($src);
        if (is_wp_error($response)) {
            return false;
        }

        $contentLength = wp_remote_retrieve_header($response, 'content-length');
        return $contentLength && (int)$contentLength > $settings['minification_threshold'];
    }

    /**
     * Add tree shaking attributes
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @param array $settings Settings
     * @return string Modified tag
     */
    private function addTreeShakingAttributes(string $tag, string $handle, string $src, array $settings): string
    {
        $attributes = [];

        if ($settings['dead_code_elimination']) {
            $attributes[] = 'data-fp-dead-code-elimination="true"';
        }

        if ($settings['unused_functions']) {
            $attributes[] = 'data-fp-unused-functions="true"';
        }

        if ($settings['unused_variables']) {
            $attributes[] = 'data-fp-unused-variables="true"';
        }

        if ($settings['unused_imports']) {
            $attributes[] = 'data-fp-unused-imports="true"';
        }

        if ($settings['aggressive_mode']) {
            $attributes[] = 'data-fp-aggressive-mode="true"';
        }

        if (!empty($attributes)) {
            $tag = str_replace('<script ', '<script ' . implode(' ', $attributes) . ' ', $tag);
        }

        Logger::debug('Tree shaking attributes added', [
            'handle' => $handle,
            'src' => basename($src),
            'attributes' => $attributes,
        ]);

        return $tag;
    }

    /**
     * Add minification attributes
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function addMinificationAttributes(string $tag, string $handle, string $src): string
    {
        $tag = str_replace('<script ', '<script data-fp-minify="true" ', $tag);

        Logger::debug('Minification attributes added', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Inject tree shaking script
     */
    public function injectTreeShakingScript(): void
    {
        $settings = $this->settings();

        ?>
        <script>
        (function() {
            'use strict';
            
            var settings = <?php echo json_encode($settings); ?>;
            var scripts = document.querySelectorAll('script[data-fp-dead-code-elimination="true"]');
            var minifyScripts = document.querySelectorAll('script[data-fp-minify="true"]');
            
            // Apply tree shaking
            if (scripts.length > 0) {
                applyTreeShaking();
            }
            
            // Apply minification
            if (minifyScripts.length > 0) {
                applyMinification();
            }
            
            function applyTreeShaking() {
                scripts.forEach(function(script) {
                    var src = script.src;
                    if (!src) return;
                    
                    // Load and process script
                    loadAndProcessScript(src, script);
                });
            }
            
            function applyMinification() {
                minifyScripts.forEach(function(script) {
                    var src = script.src;
                    if (!src) return;
                    
                    // Load and minify script
                    loadAndMinifyScript(src, script);
                });
            }
            
            function loadAndProcessScript(src, originalScript) {
                fetch(src)
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(code) {
                        var processedCode = processJavaScript(code, settings);
                        
                        if (processedCode !== code) {
                            replaceScriptContent(originalScript, processedCode);
                            
                            console.log('[FP Performance] Tree shaking applied:', src);
                        }
                    })
                    .catch(function(error) {
                        console.warn('[FP Performance] Tree shaking failed:', src, error);
                    });
            }
            
            function loadAndMinifyScript(src, originalScript) {
                fetch(src)
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(code) {
                        var minifiedCode = minifyJavaScript(code);
                        
                        if (minifiedCode !== code) {
                            replaceScriptContent(originalScript, minifiedCode);
                            
                            console.log('[FP Performance] Minification applied:', src);
                        }
                    })
                    .catch(function(error) {
                        console.warn('[FP Performance] Minification failed:', src, error);
                    });
            }
            
            function processJavaScript(code, settings) {
                var processedCode = code;
                
                // Dead code elimination
                if (settings.dead_code_elimination) {
                    processedCode = eliminateDeadCode(processedCode);
                }
                
                // Remove unused functions
                if (settings.unused_functions) {
                    processedCode = removeUnusedFunctions(processedCode);
                }
                
                // Remove unused variables
                if (settings.unused_variables) {
                    processedCode = removeUnusedVariables(processedCode);
                }
                
                // Remove unused imports
                if (settings.unused_imports) {
                    processedCode = removeUnusedImports(processedCode);
                }
                
                return processedCode;
            }
            
            function eliminateDeadCode(code) {
                // Remove unreachable code after return statements
                code = code.replace(/return[^;]*;\s*[^}]*}/g, function(match) {
                    return match.replace(/return[^;]*;\s*/, 'return;');
                });
                
                // Remove unreachable code after throw statements
                code = code.replace(/throw[^;]*;\s*[^}]*}/g, function(match) {
                    return match.replace(/throw[^;]*;\s*/, 'throw;');
                });
                
                return code;
            }
            
            function removeUnusedFunctions(code) {
                // Find all function declarations
                var functionRegex = /function\s+(\w+)\s*\([^)]*\)\s*\{[^}]*\}/g;
                var functions = [];
                var match;
                
                while ((match = functionRegex.exec(code)) !== null) {
                    functions.push({
                        name: match[1],
                        fullMatch: match[0],
                        start: match.index,
                        end: match.index + match[0].length
                    });
                }
                
                // Check which functions are actually used
                var usedFunctions = new Set();
                
                functions.forEach(function(func) {
                    var functionName = func.name;
                    var codeWithoutFunction = code.substring(0, func.start) + code.substring(func.end);
                    
                    // Check if function is called
                    var callRegex = new RegExp('\\b' + functionName + '\\s*\\(', 'g');
                    if (callRegex.test(codeWithoutFunction)) {
                        usedFunctions.add(functionName);
                    }
                });
                
                // Remove unused functions
                var result = code;
                functions.forEach(function(func) {
                    if (!usedFunctions.has(func.name)) {
                        result = result.replace(func.fullMatch, '');
                    }
                });
                
                return result;
            }
            
            function removeUnusedVariables(code) {
                // Find all variable declarations
                var varRegex = /(?:var|let|const)\s+(\w+)/g;
                var variables = [];
                var match;
                
                while ((match = varRegex.exec(code)) !== null) {
                    variables.push(match[1]);
                }
                
                // Check which variables are actually used
                var usedVariables = new Set();
                
                variables.forEach(function(variable) {
                    var codeWithoutDeclaration = code.replace(/(?:var|let|const)\s+' + variable + '\b/g, '');
                    
                    // Check if variable is referenced
                    var referenceRegex = new RegExp('\\b' + variable + '\\b', 'g');
                    if (referenceRegex.test(codeWithoutDeclaration)) {
                        usedVariables.add(variable);
                    }
                });
                
                // Remove unused variable declarations
                var result = code;
                variables.forEach(function(variable) {
                    if (!usedVariables.has(variable)) {
                        var declarationRegex = new RegExp('(?:var|let|const)\\s+' + variable + '\\s*[=;][^;]*;?', 'g');
                        result = result.replace(declarationRegex, '');
                    }
                });
                
                return result;
            }
            
            function removeUnusedImports(code) {
                // Find all import statements
                var importRegex = /import\s+.*?from\s+['"][^'"]+['"];?/g;
                var imports = [];
                var match;
                
                while ((match = importRegex.exec(code)) !== null) {
                    imports.push(match[0]);
                }
                
                // Check which imports are actually used
                var usedImports = new Set();
                
                imports.forEach(function(importStatement) {
                    // Extract imported names
                    var importNames = extractImportNames(importStatement);
                    
                    importNames.forEach(function(name) {
                        // Check if imported name is used
                        var codeWithoutImport = code.replace(importStatement, '');
                        var referenceRegex = new RegExp('\\b' + name + '\\b', 'g');
                        
                        if (referenceRegex.test(codeWithoutImport)) {
                            usedImports.add(importStatement);
                        }
                    });
                });
                
                // Remove unused imports
                var result = code;
                imports.forEach(function(importStatement) {
                    if (!usedImports.has(importStatement)) {
                        result = result.replace(importStatement, '');
                    }
                });
                
                return result;
            }
            
            function extractImportNames(importStatement) {
                var names = [];
                
                // Handle different import patterns
                if (importStatement.includes('import * as')) {
                    var match = importStatement.match(/import\s+\*\s+as\s+(\w+)/);
                    if (match) names.push(match[1]);
                } else if (importStatement.includes('import {')) {
                    var match = importStatement.match(/import\s+\{([^}]+)\}/);
                    if (match) {
                        var namedImports = match[1].split(',').map(function(name) {
                            return name.trim().split(' as ')[0].trim();
                        });
                        names = names.concat(namedImports);
                    }
                } else if (importStatement.includes('import ')) {
                    var match = importStatement.match(/import\s+(\w+)/);
                    if (match) names.push(match[1]);
                }
                
                return names;
            }
            
            function minifyJavaScript(code) {
                // Basic minification
                var minified = code;
                
                // Remove comments
                minified = minified.replace(/\/\*[\s\S]*?\*\//g, '');
                minified = minified.replace(/\/\/.*$/gm, '');
                
                // Remove unnecessary whitespace
                minified = minified.replace(/\s+/g, ' ');
                minified = minified.replace(/\s*([{}();,=])\s*/g, '$1');
                
                // Remove unnecessary semicolons
                minified = minified.replace(/;}/g, '}');
                minified = minified.replace(/;$/g, '');
                
                return minified.trim();
            }
            
            function replaceScriptContent(originalScript, newContent) {
                var newScript = document.createElement('script');
                newScript.type = 'text/javascript';
                newScript.textContent = newContent;
                
                // Copy attributes
                Array.from(originalScript.attributes).forEach(function(attr) {
                    if (!attr.name.startsWith('data-fp-')) {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });
                
                // Replace original script
                originalScript.parentNode.replaceChild(newScript, originalScript);
            }
            
        })();
        </script>
        <?php
    }

    /**
     * Inject tree shaking hints
     */
    public function injectTreeShakingHints(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        ?>
        <link rel="preload" href="data:text/javascript;base64," as="script">
        <meta name="fp-ps-tree-shaking" content="enabled">
        <?php
    }

    /**
     * Get optimization status
     *
     * @return array{enabled:bool,dead_code_elimination:bool,unused_functions:bool,unused_variables:bool,unused_imports:bool}
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'dead_code_elimination' => $settings['dead_code_elimination'],
            'unused_functions' => $settings['unused_functions'],
            'unused_variables' => $settings['unused_variables'],
            'unused_imports' => $settings['unused_imports'],
        ];
    }
}
