<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function get_option;
use function update_option;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function is_wp_error;
use function home_url;
use function add_action;
use function wp_parse_args;

/**
 * Third-Party Script Detector
 *
 * Automatically detects third-party scripts on the website,
 * suggests them to the user, and allows adding them to the managed list.
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class ThirdPartyScriptDetector
{
    private const OPTION_DETECTED = 'fp_ps_detected_scripts';
    private const OPTION_CUSTOM = 'fp_ps_custom_scripts';
    private const OPTION_DISMISSED = 'fp_ps_dismissed_scripts';
    
    private ThirdPartyScriptManager $manager;

    public function __construct(ThirdPartyScriptManager $manager)
    {
        $this->manager = $manager;
    }

    public function register(): void
    {
        // Scan periodically (once per day)
        add_action('fp_ps_daily_scan', [$this, 'scanHomepage']);
        
        if (!wp_next_scheduled('fp_ps_daily_scan')) {
            wp_schedule_event(time(), 'daily', 'fp_ps_daily_scan');
        }
    }

    /**
     * Scan the homepage for third-party scripts
     *
     * @return array{detected:array,new:array,managed:array}
     */
    public function scanHomepage(): array
    {
        $url = home_url('/');
        
        $response = wp_remote_get($url, [
            'timeout' => 15,
            'sslverify' => false,
            'headers' => [
                'User-Agent' => 'FP-Performance-Suite-Scanner/1.0',
            ],
        ]);

        if (is_wp_error($response)) {
            Logger::error('Failed to scan homepage for scripts', null, [
                'error' => $response->get_error_message(),
            ]);
            return ['detected' => [], 'new' => [], 'managed' => []];
        }

        $html = wp_remote_retrieve_body($response);
        return $this->analyzeHtml($html);
    }

    /**
     * Analyze HTML and detect third-party scripts
     *
     * @param string $html Page HTML
     * @return array{detected:array,new:array,managed:array}
     */
    public function analyzeHtml(string $html): array
    {
        $detected = [];
        $managed = [];
        $new = [];

        // Extract all script tags with src
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        if (empty($matches[1])) {
            return ['detected' => [], 'new' => [], 'managed' => []];
        }

        $managedScripts = $this->manager->settings()['scripts'];
        $customScripts = $this->getCustomScripts();
        $dismissed = $this->getDismissedScripts();

        foreach ($matches[1] as $src) {
            // Skip same-domain scripts
            if ($this->isSameDomain($src)) {
                continue;
            }

            // Skip WordPress core scripts
            if ($this->isWordPressCore($src)) {
                continue;
            }

            $scriptInfo = $this->identifyScript($src);
            
            // Check if already managed
            $alreadyManaged = false;
            foreach ($managedScripts as $key => $config) {
                foreach ($config['patterns'] as $pattern) {
                    if (stripos($src, $pattern) !== false) {
                        $managed[] = [
                            'src' => $src,
                            'service' => $key,
                            'enabled' => $config['enabled'] ?? false,
                        ];
                        $alreadyManaged = true;
                        break 2;
                    }
                }
            }

            // Check if in custom scripts
            foreach ($customScripts as $custom) {
                foreach ($custom['patterns'] as $pattern) {
                    if (stripos($src, $pattern) !== false) {
                        $managed[] = [
                            'src' => $src,
                            'service' => $custom['name'],
                            'custom' => true,
                            'enabled' => $custom['enabled'] ?? false,
                        ];
                        $alreadyManaged = true;
                        break 2;
                    }
                }
            }

            if (!$alreadyManaged) {
                $hash = md5($src);
                
                // Skip if dismissed
                if (in_array($hash, $dismissed)) {
                    continue;
                }

                $detected[] = $scriptInfo;
                
                // Check if it's new (not seen before)
                $previouslyDetected = get_option(self::OPTION_DETECTED, []);
                if (!isset($previouslyDetected[$hash])) {
                    $new[] = $scriptInfo;
                }
            }
        }

        // Store detected scripts
        $this->storeDetectedScripts($detected);

        return [
            'detected' => $detected,
            'new' => $new,
            'managed' => $managed,
        ];
    }

    /**
     * Identify a script and extract information
     *
     * @param string $src Script source URL
     * @return array{src:string,domain:string,hash:string,suggested_name:string,category:string,confidence:string}
     */
    private function identifyScript(string $src): array
    {
        $parsed = parse_url($src);
        $domain = $parsed['host'] ?? '';
        $path = $parsed['path'] ?? '';
        
        // Extract main domain (without subdomain for common patterns)
        $domainParts = explode('.', $domain);
        $mainDomain = count($domainParts) >= 2 
            ? $domainParts[count($domainParts) - 2] . '.' . $domainParts[count($domainParts) - 1]
            : $domain;

        // Try to identify the service
        $identification = $this->guessServiceInfo($domain, $path, $src);

        return [
            'src' => $src,
            'domain' => $domain,
            'main_domain' => $mainDomain,
            'path' => $path,
            'hash' => md5($src),
            'suggested_name' => $identification['name'],
            'suggested_pattern' => $identification['pattern'],
            'category' => $identification['category'],
            'confidence' => $identification['confidence'],
            'detected_at' => time(),
        ];
    }

    /**
     * Guess service information from domain and path
     *
     * @param string $domain Domain name
     * @param string $path URL path
     * @param string $fullSrc Full source URL
     * @return array{name:string,pattern:string,category:string,confidence:string}
     */
    private function guessServiceInfo(string $domain, string $path, string $fullSrc): array
    {
        // Common patterns for service identification
        $patterns = [
            // Analytics
            '/analytics|tracking|tracker|tag|pixel|beacon/i' => ['category' => 'analytics', 'confidence' => 'medium'],
            // Chat
            '/chat|messenger|widget.*chat|livechat|support/i' => ['category' => 'chat', 'confidence' => 'high'],
            // Ads
            '/ads|advertising|adserver|adsense|doubleclick/i' => ['category' => 'advertising', 'confidence' => 'high'],
            // Social
            '/facebook|twitter|instagram|linkedin|social/i' => ['category' => 'social', 'confidence' => 'high'],
            // CDN/Libraries (usually safe)
            '/cdn|cloudflare|jsdelivr|unpkg|cdnjs/i' => ['category' => 'cdn', 'confidence' => 'high'],
            // Forms
            '/form|survey|typeform|jotform/i' => ['category' => 'forms', 'confidence' => 'medium'],
            // Video
            '/video|player|vimeo|youtube|wistia/i' => ['category' => 'video', 'confidence' => 'high'],
            // Payment
            '/payment|checkout|stripe|paypal/i' => ['category' => 'payment', 'confidence' => 'high'],
        ];

        $category = 'unknown';
        $confidence = 'low';
        
        $searchString = $domain . $path;
        
        foreach ($patterns as $pattern => $info) {
            if (preg_match($pattern, $searchString)) {
                $category = $info['category'];
                $confidence = $info['confidence'];
                break;
            }
        }

        // Generate suggested name from domain
        $nameParts = explode('.', $domain);
        $suggestedName = ucfirst($nameParts[count($nameParts) - 2] ?? $nameParts[0]);
        
        // Clean up common prefixes
        $suggestedName = preg_replace('/^(www|cdn|static|api|js|widget|embed)\-?/i', '', $suggestedName);
        $suggestedName = ucwords(str_replace(['-', '_'], ' ', $suggestedName));

        // Generate pattern (use domain without protocol)
        $pattern = str_replace(['http://', 'https://'], '', $domain);

        return [
            'name' => $suggestedName,
            'pattern' => $pattern,
            'category' => $category,
            'confidence' => $confidence,
        ];
    }

    /**
     * Check if URL is same domain
     *
     * @param string $url URL to check
     * @return bool True if same domain
     */
    private function isSameDomain(string $url): bool
    {
        // Relative URLs are same domain
        if (strpos($url, '//') === false) {
            return true;
        }

        $parsed = parse_url($url);
        $scriptDomain = $parsed['host'] ?? '';
        
        $homeParsed = parse_url(home_url());
        $homeDomain = $homeParsed['host'] ?? '';

        return $scriptDomain === $homeDomain;
    }

    /**
     * Check if URL is WordPress core
     *
     * @param string $url URL to check
     * @return bool True if WordPress core
     */
    private function isWordPressCore(string $url): bool
    {
        $corePatterns = [
            '/wp-includes/',
            '/wp-admin/',
            '/wp-content/themes/',
            '/wp-content/plugins/',
        ];

        foreach ($corePatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Store detected scripts
     *
     * @param array $scripts Detected scripts
     */
    private function storeDetectedScripts(array $scripts): void
    {
        $stored = get_option(self::OPTION_DETECTED, []);
        
        foreach ($scripts as $script) {
            $stored[$script['hash']] = [
                'src' => $script['src'],
                'domain' => $script['domain'],
                'suggested_name' => $script['suggested_name'],
                'suggested_pattern' => $script['suggested_pattern'],
                'category' => $script['category'],
                'first_seen' => $stored[$script['hash']]['first_seen'] ?? time(),
                'last_seen' => time(),
                'occurrences' => ($stored[$script['hash']]['occurrences'] ?? 0) + 1,
            ];
        }

        update_option(self::OPTION_DETECTED, $stored);
    }

    /**
     * Get all detected scripts
     *
     * @return array Detected scripts
     */
    public function getDetectedScripts(): array
    {
        return get_option(self::OPTION_DETECTED, []);
    }

    /**
     * Get custom scripts
     *
     * @return array Custom scripts
     */
    public function getCustomScripts(): array
    {
        return get_option(self::OPTION_CUSTOM, []);
    }

    /**
     * Add a custom script to the managed list
     *
     * @param array $script Script configuration
     * @return bool Success
     */
    public function addCustomScript(array $script): bool
    {
        $custom = $this->getCustomScripts();
        
        // Generate unique key
        $baseKey = sanitize_key($script['name'] ?? '');
        if (empty($baseKey)) {
            return false;
        }

        // Ensure patterns is array
        if (isset($script['patterns']) && !is_array($script['patterns'])) {
            $script['patterns'] = [$script['patterns']];
        }

        // Check if script with same name already exists
        $key = $baseKey;
        $counter = 1;
        while (isset($custom[$key])) {
            // If it's the same script (same patterns), merge patterns instead of creating duplicate
            $existingPatterns = $custom[$key]['patterns'] ?? [];
            $newPatterns = $script['patterns'] ?? [];
            
            // Check if any pattern already exists
            $hasCommonPattern = false;
            foreach ($newPatterns as $newPattern) {
                if (in_array($newPattern, $existingPatterns)) {
                    $hasCommonPattern = true;
                    break;
                }
            }
            
            if ($hasCommonPattern) {
                // Merge patterns and update existing script
                $mergedPatterns = array_unique(array_merge($existingPatterns, $newPatterns));
                $custom[$key]['patterns'] = $mergedPatterns;
                
                update_option(self::OPTION_CUSTOM, $custom);
                
                Logger::info('Custom script patterns merged', [
                    'name' => $custom[$key]['name'],
                    'patterns' => $custom[$key]['patterns'],
                ]);
                
                return true;
            }
            
            // Generate unique key with counter
            $key = $baseKey . '_' . $counter;
            $counter++;
        }

        $custom[$key] = wp_parse_args($script, [
            'name' => '',
            'patterns' => [],
            'enabled' => true,
            'delay' => true,
            'category' => 'custom',
            'added_at' => time(),
        ]);

        update_option(self::OPTION_CUSTOM, $custom);

        Logger::info('Custom script added', [
            'name' => $custom[$key]['name'],
            'patterns' => $custom[$key]['patterns'],
            'key' => $key,
        ]);

        return true;
    }

    /**
     * Remove a custom script
     *
     * @param string $key Script key
     * @return bool Success
     */
    public function removeCustomScript(string $key): bool
    {
        $custom = $this->getCustomScripts();
        
        if (!isset($custom[$key])) {
            return false;
        }

        unset($custom[$key]);
        update_option(self::OPTION_CUSTOM, $custom);

        return true;
    }

    /**
     * Dismiss a detected script (won't suggest again)
     *
     * @param string $hash Script hash
     * @return bool Success
     */
    public function dismissScript(string $hash): bool
    {
        $dismissed = $this->getDismissedScripts();
        
        if (!in_array($hash, $dismissed)) {
            $dismissed[] = $hash;
            update_option(self::OPTION_DISMISSED, $dismissed);
        }

        return true;
    }

    /**
     * Get dismissed scripts
     *
     * @return array Dismissed script hashes
     */
    private function getDismissedScripts(): array
    {
        return get_option(self::OPTION_DISMISSED, []);
    }

    /**
     * Get suggestions for user (detected scripts not yet managed)
     *
     * @return array Suggested scripts with metadata
     */
    public function getSuggestions(): array
    {
        $detected = $this->getDetectedScripts();
        $dismissed = $this->getDismissedScripts();
        $suggestions = [];

        foreach ($detected as $hash => $script) {
            if (in_array($hash, $dismissed)) {
                continue;
            }

            // Prioritize by occurrences and category
            $priority = $this->calculatePriority($script);
            
            $suggestions[] = array_merge($script, [
                'hash' => $hash,
                'priority' => $priority,
            ]);
        }

        // Sort by priority
        usort($suggestions, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        return $suggestions;
    }

    /**
     * Calculate priority for a suggestion
     *
     * @param array $script Script data
     * @return int Priority score
     */
    private function calculatePriority(array $script): int
    {
        $priority = 0;

        // More occurrences = higher priority
        $priority += ($script['occurrences'] ?? 0) * 10;

        // Category priorities
        $categoryPriorities = [
            'analytics' => 30,
            'advertising' => 25,
            'chat' => 20,
            'social' => 15,
            'video' => 10,
            'payment' => 35,
            'forms' => 15,
            'cdn' => 5,
            'unknown' => 5,
        ];

        $priority += $categoryPriorities[$script['category'] ?? 'unknown'] ?? 0;

        // Older scripts = higher priority (been around longer)
        $daysSinceFirstSeen = (time() - ($script['first_seen'] ?? time())) / 86400;
        $priority += min($daysSinceFirstSeen * 2, 50);

        return (int) $priority;
    }

    /**
     * Auto-add script from suggestion
     *
     * @param string $hash Script hash
     * @return bool Success
     */
    public function autoAddFromSuggestion(string $hash): bool
    {
        $detected = $this->getDetectedScripts();
        
        if (!isset($detected[$hash])) {
            return false;
        }

        $script = $detected[$hash];

        return $this->addCustomScript([
            'name' => $script['suggested_name'],
            'patterns' => [$script['suggested_pattern']],
            'enabled' => true,
            'delay' => true,
            'category' => $script['category'],
            'auto_added' => true,
        ]);
    }

    /**
     * Get statistics
     *
     * @return array{total_detected:int,total_custom:int,total_suggestions:int,by_category:array}
     */
    public function getStats(): array
    {
        $detected = $this->getDetectedScripts();
        $custom = $this->getCustomScripts();
        $suggestions = $this->getSuggestions();

        $byCategory = [];
        foreach ($detected as $script) {
            $category = $script['category'] ?? 'unknown';
            $byCategory[$category] = ($byCategory[$category] ?? 0) + 1;
        }

        return [
            'total_detected' => count($detected),
            'total_custom' => count($custom),
            'total_suggestions' => count($suggestions),
            'by_category' => $byCategory,
        ];
    }
}
