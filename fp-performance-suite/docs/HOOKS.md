# FP Performance Suite - Hooks Reference

Comprehensive guide to all available hooks and filters in FP Performance Suite.

## Table of Contents

- [Actions](#actions)
- [Filters](#filters)
- [Usage Examples](#usage-examples)

---

## Actions

### Plugin Lifecycle

#### `fp_perfsuite_container_ready`

Fires after the dependency injection container has been built and all services are registered.

**Parameters:**
- `$container` (ServiceContainer) - The plugin's service container

**Example:**
```php
add_action('fp_perfsuite_container_ready', function($container) {
    // Access services
    $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
    
    // Register custom service
    $container->set('my_custom_service', fn() => new MyCustomService());
});
```

---

#### `fp_ps_plugin_activated`

Fires when the plugin is activated.

**Parameters:**
- `$version` (string) - The plugin version being activated

**Example:**
```php
add_action('fp_ps_plugin_activated', function($version) {
    error_log("FP Performance Suite v{$version} activated");
    
    // Run custom initialization
    do_custom_setup();
});
```

---

#### `fp_ps_plugin_deactivated`

Fires when the plugin is deactivated.

**Example:**
```php
add_action('fp_ps_plugin_deactivated', function() {
    // Cleanup custom data
    delete_option('my_custom_option');
});
```

---

### Cache Events

#### `fp_ps_cache_cleared`

Fires after the page cache has been successfully cleared.

**Example:**
```php
add_action('fp_ps_cache_cleared', function() {
    // Clear additional custom caches
    wp_cache_flush();
    
    // Send notification
    wp_mail(get_option('admin_email'), 'Cache Cleared', 'Page cache was cleared');
});
```

---

### WebP Conversion

#### `fp_ps_webp_bulk_start`

Fires when a bulk WebP conversion starts.

**Parameters:**
- `$total` (int) - Total number of images to convert

**Example:**
```php
add_action('fp_ps_webp_bulk_start', function($total) {
    update_option('webp_conversion_start_time', time());
    error_log("Starting WebP conversion of {$total} images");
});
```

---

#### `fp_ps_webp_converted`

Fires after a single image is successfully converted to WebP.

**Parameters:**
- `$file` (string) - Path to the original image file

**Example:**
```php
add_action('fp_ps_webp_converted', function($file) {
    // Log conversion
    error_log("Converted to WebP: {$file}");
    
    // Trigger CDN purge
    purge_cdn_cache(basename($file));
});
```

---

### Database Cleanup

#### `fp_ps_db_cleanup_complete`

Fires after database cleanup completes.

**Parameters:**
- `$results` (array) - Cleanup results with deleted counts
- `$dryRun` (bool) - Whether this was a dry run

**Example:**
```php
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if (!$dryRun) {
        $total = array_sum(array_column($results, 'deleted'));
        error_log("Database cleanup removed {$total} items");
    }
}, 10, 2);
```

---

### .htaccess Events

#### `fp_ps_htaccess_updated`

Fires after .htaccess rules are successfully injected.

**Parameters:**
- `$section` (string) - The section name
- `$rules` (string) - The injected rules

**Example:**
```php
add_action('fp_ps_htaccess_updated', function($section, $rules) {
    error_log("Updated .htaccess section: {$section}");
    
    // Backup .htaccess to remote storage
    backup_htaccess_to_s3();
}, 10, 2);
```

---

#### `fp_ps_htaccess_section_removed`

Fires after a .htaccess section is removed.

**Parameters:**
- `$section` (string) - The section name

**Example:**
```php
add_action('fp_ps_htaccess_section_removed', function($section) {
    error_log("Removed .htaccess section: {$section}");
});
```

---

### Logging Events

#### `fp_ps_log_error`

Fires when an error is logged.

**Parameters:**
- `$message` (string) - Error message
- `$exception` (Throwable|null) - Exception object if available

**Example:**
```php
add_action('fp_ps_log_error', function($message, $exception) {
    // Send to external monitoring service
    send_to_sentry($message, $exception);
}, 10, 2);
```

---

#### `fp_ps_log_warning`

Fires when a warning is logged.

**Parameters:**
- `$message` (string) - Warning message

**Example:**
```php
add_action('fp_ps_log_warning', function($message) {
    // Track warnings in database
    global $wpdb;
    $wpdb->insert('custom_warnings', ['message' => $message, 'time' => time()]);
});
```

---

#### `fp_ps_rate_limit_exceeded`

Fires when a rate limit is exceeded.

**Parameters:**
- `$action` (string) - The action that was rate limited
- `$data` (array) - Rate limit data (count, first, last)

**Example:**
```php
add_action('fp_ps_rate_limit_exceeded', function($action, $data) {
    // Send alert to admin
    wp_mail(
        get_option('admin_email'),
        'Rate Limit Exceeded',
        "Action '{$action}' exceeded rate limit. Attempts: {$data['count']}"
    );
}, 10, 2);
```

---

## Filters

### Capability Management

#### `fp_ps_required_capability`

Filter the capability required to access plugin pages.

**Parameters:**
- `$capability` (string) - Default capability (usually 'manage_options')
- `$role` (string) - The configured role

**Returns:** (string) The capability to require

**Example:**
```php
add_filter('fp_ps_required_capability', function($capability, $role) {
    // Allow editors to access on staging site
    if (wp_get_environment_type() === 'staging') {
        return 'edit_pages';
    }
    return $capability;
}, 10, 2);
```

---

### Script Optimization

#### `fp_ps_defer_skip_handles`

Filter script handles that should be excluded from automatic deferral.

**Parameters:**
- `$handles` (array) - Array of script handles to skip

**Returns:** (array) Modified array of handles

**Example:**
```php
add_filter('fp_ps_defer_skip_handles', function($handles) {
    // Don't defer critical third-party scripts
    $handles[] = 'google-analytics';
    $handles[] = 'facebook-pixel';
    $handles[] = 'my-critical-script';
    
    return $handles;
});
```

---

### Database Cleanup

#### `fp_ps_db_scheduled_scope`

Filter the scope of scheduled database cleanups.

**Parameters:**
- `$scope` (array) - Default cleanup tasks

**Returns:** (array) Modified scope

**Example:**
```php
add_filter('fp_ps_db_scheduled_scope', function($scope) {
    // Add custom cleanup task
    $scope[] = 'my_custom_cleanup';
    
    // Remove revisions cleanup
    $scope = array_diff($scope, ['revisions']);
    
    return $scope;
});
```

---

### Performance Scoring

#### `fp_ps_gzip_enabled`

Filter the detected gzip compression status.

**Parameters:**
- `$enabled` (bool) - Detected gzip status

**Returns:** (bool) Modified status

**Example:**
```php
add_filter('fp_ps_gzip_enabled', function($enabled) {
    // Force enable if using Cloudflare
    if (isset($_SERVER['HTTP_CF_RAY'])) {
        return true;
    }
    return $enabled;
});
```

---

#### `fp_ps_gzip_detection_evidence`

Filter whether there's evidence for gzip detection.

**Parameters:**
- `$hasEvidence` (bool) - Whether evidence was found

**Returns:** (bool) Modified evidence status

**Example:**
```php
add_filter('fp_ps_gzip_detection_evidence', function($hasEvidence) {
    // Always show evidence on staging
    if (wp_get_environment_type() === 'staging') {
        return true;
    }
    return $hasEvidence;
});
```

---

#### `fp_ps_require_critical_css`

Filter whether Critical CSS is considered mandatory by the scorecard.

**Parameters:**
- `$required` (bool) - Whether it's required (default: false)

**Returns:** (bool) Modified requirement

**Example:**
```php
add_filter('fp_ps_require_critical_css', function($required) {
    // Require critical CSS on production
    return wp_get_environment_type() === 'production';
});
```

---

## Usage Examples

### Custom Cache Warming

```php
add_action('fp_ps_cache_cleared', function() {
    $urls = [
        home_url('/'),
        home_url('/about/'),
        home_url('/contact/'),
    ];
    
    foreach ($urls as $url) {
        wp_remote_get($url);
    }
});
```

---

### Integration with CDN

```php
add_action('fp_ps_webp_converted', function($file) {
    // Purge from CDN
    $filename = basename($file);
    $cdn_url = 'https://cdn.example.com/wp-content/uploads/' . $filename;
    
    wp_remote_request($cdn_url, [
        'method' => 'PURGE',
        'headers' => [
            'X-API-Key' => get_option('cdn_api_key'),
        ],
    ]);
});
```

---

### Custom Monitoring Integration

```php
// Send errors to monitoring service
add_action('fp_ps_log_error', function($message, $exception) {
    if (function_exists('send_to_monitoring')) {
        send_to_monitoring([
            'level' => 'error',
            'message' => $message,
            'exception' => $exception ? $exception->getMessage() : null,
            'plugin' => 'fp-performance-suite',
        ]);
    }
}, 10, 2);
```

---

### Conditional Optimization

```php
// Disable defer on specific pages
add_filter('fp_ps_defer_skip_handles', function($handles) {
    if (is_page('checkout')) {
        // Don't defer anything on checkout
        return array_merge($handles, get_all_script_handles());
    }
    return $handles;
});
```

---

### Advanced Rate Limiting

```php
add_action('fp_ps_rate_limit_exceeded', function($action, $data) {
    // Temporarily ban IP if too many violations
    $violations = get_transient('rate_limit_violations_' . $_SERVER['REMOTE_ADDR']);
    $violations = $violations ? (int)$violations + 1 : 1;
    
    if ($violations > 5) {
        // Add to block list
        update_option('blocked_ips', array_merge(
            get_option('blocked_ips', []),
            [$_SERVER['REMOTE_ADDR']]
        ));
    }
    
    set_transient('rate_limit_violations_' . $_SERVER['REMOTE_ADDR'], $violations, HOUR_IN_SECONDS);
}, 10, 2);
```

---

### Performance Monitoring

```php
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if (!$dryRun) {
        // Store cleanup stats
        $stats = get_option('cleanup_history', []);
        $stats[] = [
            'date' => current_time('mysql'),
            'results' => $results,
            'total_deleted' => array_sum(array_column($results, 'deleted')),
        ];
        
        // Keep only last 30 entries
        if (count($stats) > 30) {
            $stats = array_slice($stats, -30);
        }
        
        update_option('cleanup_history', $stats);
    }
}, 10, 2);
```

---

## Best Practices

1. **Always check capability** before performing privileged actions in hook callbacks
2. **Use appropriate priority** when hooking - lower numbers run earlier
3. **Return filtered values** - don't forget to return in filter callbacks
4. **Handle errors gracefully** - wrap risky operations in try-catch
5. **Document your hooks** - add inline comments explaining your customizations
6. **Test thoroughly** - especially when modifying core plugin behavior

---

## Support

For more information or questions about hooks:
- Documentation: https://francescopasseri.com
- GitHub: https://github.com/franpass87/FP-Performance/issues
