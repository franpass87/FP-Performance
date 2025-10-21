# Developer Guide - FP Performance Suite

Complete guide for developers extending or integrating with FP Performance Suite.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Accessing Services](#accessing-services)
- [Creating Custom Integrations](#creating-custom-integrations)
- [Extending Functionality](#extending-functionality)
- [Best Practices](#best-practices)
- [Examples](#examples)

---

## Architecture Overview

FP Performance Suite uses a modern architecture with:

- **Dependency Injection Container** - All services are registered in `ServiceContainer`
- **PSR-4 Autoloading** - Clean namespace structure: `FP\PerfSuite\`
- **Interface-Based Design** - Core services implement interfaces for testability
- **Event-Driven** - Extensive hooks and filters for customization

### Core Components

```
FP\PerfSuite\
├── Plugin                 # Main plugin class
├── ServiceContainer       # DI container
├── Admin/                 # Admin interface
│   ├── Assets            # Admin assets
│   ├── Menu              # Menu registration
│   └── Pages/            # Admin pages
├── Services/              # Business logic
│   ├── Assets/           # Asset optimization
│   ├── Cache/            # Caching services
│   ├── DB/               # Database operations
│   ├── Logs/             # Logging services
│   ├── Media/            # Media optimization
│   ├── Presets/          # Configuration presets
│   └── Score/            # Performance scoring
├── Http/                  # REST API routes
├── Utils/                 # Utility classes
│   ├── Logger            # Centralized logging
│   ├── RateLimiter       # Rate limiting
│   ├── Fs                # Filesystem operations
│   └── ...
└── Contracts/             # Interfaces
```

---

## Accessing Services

### Via Dependency Injection Container

```php
// Get the container
$container = \FP\PerfSuite\Plugin::container();

// Access any service
$pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
$optimizer = $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class);
```

### Via Action Hook

```php
add_action('fp_perfsuite_container_ready', function($container) {
    // Container is ready, access services
    $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
    
    // Do something with the service
    $status = $pageCache->status();
});
```

---

## Creating Custom Integrations

### Example: Custom Cache Backend

```php
<?php
/**
 * Plugin Name: FP Performance - Redis Cache
 * Description: Adds Redis caching to FP Performance Suite
 */

use FP\PerfSuite\Contracts\CacheInterface;

class FP_Redis_Cache implements CacheInterface
{
    private $redis;
    
    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function isEnabled(): bool
    {
        return $this->redis->isConnected();
    }
    
    public function settings(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'backend' => 'redis',
        ];
    }
    
    public function clear(): void
    {
        $this->redis->flushAll();
    }
    
    public function status(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'keys' => $this->redis->dbSize(),
        ];
    }
    
    public function update(array $settings): void
    {
        // Implementation
    }
}

// Register custom cache
add_action('fp_perfsuite_container_ready', function($container) {
    $container->set('redis_cache', fn() => new FP_Redis_Cache());
});
```

### Example: Custom Optimization Service

```php
<?php
/**
 * Plugin Name: FP Performance - Image Lazy Load
 * Description: Adds lazy loading for images
 */

use FP\PerfSuite\Utils\Logger;

class FP_Image_LazyLoad
{
    public function __construct()
    {
        add_filter('the_content', [$this, 'lazyLoadImages']);
        Logger::info('Image Lazy Load initialized');
    }
    
    public function lazyLoadImages(string $content): string
    {
        // Add loading="lazy" to all images
        $content = preg_replace(
            '/<img((?![^>]*loading=)[^>]*)>/i',
            '<img$1 loading="lazy">',
            $content
        );
        
        return $content;
    }
}

// Initialize after FP Performance is ready
add_action('fp_perfsuite_container_ready', function($container) {
    $container->set('image_lazy_load', fn() => new FP_Image_LazyLoad());
    $container->get('image_lazy_load'); // Instantiate
});
```

---

## Extending Functionality

### Adding Custom Database Cleanup Tasks

```php
add_filter('fp_ps_db_scheduled_scope', function($scope) {
    // Add custom cleanup task
    $scope[] = 'my_custom_cleanup';
    return $scope;
});

// Handle the custom cleanup
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if (in_array('my_custom_cleanup', $results)) {
        // Your cleanup logic here
        global $wpdb;
        
        if (!$dryRun) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}my_table WHERE created < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        }
    }
}, 10, 2);
```

### Custom Performance Metrics

```php
add_action('fp_ps_cache_cleared', function() {
    // Track cache clears
    $metrics = get_option('my_performance_metrics', []);
    $metrics['cache_clears'][] = time();
    update_option('my_performance_metrics', $metrics);
});

add_action('fp_ps_webp_converted', function($file) {
    // Track WebP conversions
    $metrics = get_option('my_performance_metrics', []);
    $metrics['webp_conversions'][] = [
        'file' => basename($file),
        'time' => time(),
    ];
    update_option('my_performance_metrics', $metrics);
});
```

### Custom Admin Page

```php
<?php
use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;

class My_Custom_Page extends AbstractPage
{
    public function slug(): string
    {
        return 'my-custom-perf-page';
    }
    
    public function title(): string
    {
        return __('My Custom Metrics', 'my-plugin');
    }
    
    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }
    
    protected function content(): string
    {
        $metrics = get_option('my_performance_metrics', []);
        
        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>Custom Performance Metrics</h2>
            <p>Total cache clears: <?php echo count($metrics['cache_clears'] ?? []); ?></p>
            <p>Total WebP conversions: <?php echo count($metrics['webp_conversions'] ?? []); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Register custom page
add_action('admin_menu', function() {
    $page = new My_Custom_Page(\FP\PerfSuite\Plugin::container());
    
    add_submenu_page(
        'fp-performance-suite',
        $page->title(),
        $page->title(),
        'manage_options',
        $page->slug(),
        [$page, 'render']
    );
});
```

---

## Best Practices

### 1. Use the Logger

```php
use FP\PerfSuite\Utils\Logger;

// Instead of error_log()
Logger::error('Operation failed', $exception);
Logger::warning('Potential issue detected');
Logger::info('Operation completed');
Logger::debug('Detailed debugging info', ['context' => 'data']);
```

### 2. Respect Rate Limits

```php
use FP\PerfSuite\Utils\RateLimiter;

$limiter = new RateLimiter();

if (!$limiter->isAllowed('my_expensive_operation', 3, 3600)) {
    wp_send_json_error('Rate limit exceeded');
    return;
}

// Perform expensive operation
perform_expensive_operation();
```

### 3. Use Service Container

```php
// ❌ Don't create services directly
$pageCache = new \FP\PerfSuite\Services\Cache\PageCache($fs, $env);

// ✅ Get from container
$container = \FP\PerfSuite\Plugin::container();
$pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
```

### 4. Cache Settings

```php
// ✅ Use the container's cached settings
$container = \FP\PerfSuite\Plugin::container();
$settings = $container->getCachedSettings('my_option_name', ['default' => 'value']);

// When updating, invalidate cache
update_option('my_option_name', $newSettings);
$container->invalidateSettingsCache('my_option_name');
```

### 5. Add Hooks for Extensibility

```php
class MyService
{
    public function process($data)
    {
        // Allow filtering input
        $data = apply_filters('my_service_input', $data);
        
        // Do processing
        $result = $this->doProcess($data);
        
        // Fire action after processing
        do_action('my_service_processed', $result, $data);
        
        // Allow filtering output
        return apply_filters('my_service_output', $result);
    }
}
```

---

## Examples

### Example 1: CDN Integration

```php
<?php
/**
 * Plugin Name: FP Performance - CloudFlare CDN
 * Description: Purges CloudFlare cache when FP Performance clears cache
 */

use FP\PerfSuite\Utils\Logger;

class FP_CloudFlare_Integration
{
    private string $apiKey;
    private string $zoneId;
    
    public function __construct()
    {
        $this->apiKey = get_option('cloudflare_api_key');
        $this->zoneId = get_option('cloudflare_zone_id');
        
        add_action('fp_ps_cache_cleared', [$this, 'purgeCDN']);
        add_action('fp_ps_webp_converted', [$this, 'purgeImage'], 10, 1);
    }
    
    public function purgeCDN(): void
    {
        $response = wp_remote_post(
            "https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/purge_cache",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode(['purge_everything' => true]),
            ]
        );
        
        if (is_wp_error($response)) {
            Logger::error('CloudFlare purge failed', $response);
        } else {
            Logger::info('CloudFlare cache purged successfully');
        }
    }
    
    public function purgeImage(string $file): void
    {
        $url = wp_get_attachment_url(attachment_url_to_postid($file));
        
        if (!$url) {
            return;
        }
        
        $response = wp_remote_post(
            "https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/purge_cache",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode(['files' => [$url]]),
            ]
        );
        
        if (!is_wp_error($response)) {
            Logger::debug('CloudFlare image purged', ['url' => $url]);
        }
    }
}

// Initialize
add_action('plugins_loaded', function() {
    if (class_exists('\FP\PerfSuite\Plugin')) {
        new FP_CloudFlare_Integration();
    }
});
```

### Example 2: Monitoring Integration

```php
<?php
/**
 * Plugin Name: FP Performance - Sentry Monitoring
 * Description: Sends FP Performance errors to Sentry
 */

class FP_Sentry_Integration
{
    private $sentryDsn;
    
    public function __construct()
    {
        $this->sentryDsn = get_option('sentry_dsn');
        
        // Hook into FP Performance logging
        add_action('fp_ps_log_error', [$this, 'sendToSentry'], 10, 2);
        add_action('fp_ps_rate_limit_exceeded', [$this, 'trackRateLimit'], 10, 2);
    }
    
    public function sendToSentry(string $message, ?\Throwable $exception): void
    {
        if (!$this->sentryDsn || !$exception) {
            return;
        }
        
        wp_remote_post('https://sentry.io/api/...' . $this->sentryDsn, [
            'body' => wp_json_encode([
                'message' => $message,
                'exception' => [
                    'type' => get_class($exception),
                    'value' => $exception->getMessage(),
                    'stacktrace' => $exception->getTraceAsString(),
                ],
                'tags' => [
                    'plugin' => 'fp-performance-suite',
                    'version' => FP_PERF_SUITE_VERSION,
                ],
            ]),
        ]);
    }
    
    public function trackRateLimit(string $action, array $data): void
    {
        // Track rate limit violations
        wp_remote_post('https://sentry.io/api/...' . $this->sentryDsn, [
            'body' => wp_json_encode([
                'message' => "Rate limit exceeded: {$action}",
                'level' => 'warning',
                'extra' => $data,
            ]),
        ]);
    }
}

add_action('plugins_loaded', function() {
    if (class_exists('\FP\PerfSuite\Plugin')) {
        new FP_Sentry_Integration();
    }
});
```

### Example 3: Custom WP-CLI Command

```php
<?php
/**
 * Custom WP-CLI command for FP Performance Suite
 */

class FP_Custom_CLI_Commands
{
    /**
     * Warm up cache by crawling sitemap
     *
     * @when after_wp_load
     */
    public function warmup($args, $assoc_args)
    {
        $sitemap_url = home_url('/sitemap.xml');
        
        WP_CLI::log("Fetching sitemap from {$sitemap_url}...");
        
        $response = wp_remote_get($sitemap_url);
        if (is_wp_error($response)) {
            WP_CLI::error('Failed to fetch sitemap');
            return;
        }
        
        $xml = simplexml_load_string(wp_remote_retrieve_body($response));
        $urls = [];
        
        foreach ($xml->url as $url) {
            $urls[] = (string)$url->loc;
        }
        
        WP_CLI::log('Found ' . count($urls) . ' URLs. Warming up cache...');
        
        $progress = \WP_CLI\Utils\make_progress_bar('Warming cache', count($urls));
        
        foreach ($urls as $url) {
            wp_remote_get($url);
            $progress->tick();
        }
        
        $progress->finish();
        WP_CLI::success('Cache warmup complete!');
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('fp-performance warmup', [FP_Custom_CLI_Commands::class, 'warmup']);
}
```

---

## Testing Your Integration

### Unit Testing

```php
use PHPUnit\Framework\TestCase;

class MyIntegrationTest extends TestCase
{
    public function testCDNPurge(): void
    {
        $integration = new FP_CloudFlare_Integration();
        
        // Mock the cache clear action
        do_action('fp_ps_cache_cleared');
        
        // Assert CDN was purged
        $this->assertTrue($integration->wasLastPurgeSuccessful());
    }
}
```

### Integration Testing

```bash
# Test with WP-CLI
wp fp-performance cache clear
wp fp-performance score
wp fp-performance db cleanup --dry-run
```

---

## Support & Resources

- **Documentation**: https://francescopasseri.com
- **GitHub**: https://github.com/franpass87/FP-Performance
- **Hooks Reference**: [HOOKS.md](./HOOKS.md)

---

## Contributing

Contributions are welcome! Please:

1. Follow PSR-12 coding standards
2. Add tests for new features
3. Update documentation
4. Use semantic commit messages

Happy coding! 🚀
