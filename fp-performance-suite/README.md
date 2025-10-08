# FP Performance Suite

> Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.

| Meta | Value |
| --- | --- |
| **Name** | FP Performance Suite |
| **Version** | 1.1.0 |
| **Author** | [Francesco Passeri](https://francescopasseri.com) |
| **Author Email** | [info@francescopasseri.com](mailto:info@francescopasseri.com) |
| **Author URI** | https://francescopasseri.com |
| **Plugin Homepage** | https://francescopasseri.com |
| **Requires WordPress** | 6.2 |
| **Tested up to** | 6.5 |
| **Requires PHP** | 8.0 |
| **License** | GPLv2 or later |

## What it does

FP Performance Suite delivers a modular control center for WordPress administrators on shared hosting. It combines filesystem page caching, browser cache headers, asset optimization, WebP conversion, database cleanup, debug toggles, realtime log viewing, and hosting-specific presets inside a unified, safety-first dashboard.

## Features

### Core Performance
- **Filesystem Page Cache** - Instant purge controls and cache warmers with intelligent invalidation
- **Browser Cache Headers** - Manager with `.htaccess` automation when available
- **Asset Optimizer** - Minification, defer/async loading, DNS prefetch, preload directives, and heartbeat throttling
- **WebP Converter** - GD and Imagick support with lossy/lossless profiles, bulk actions, and coverage reporting
- **Database Cleaner** - Dry-run mode, transient cleanup, scheduled maintenance, and table optimization
- **Debug Toggler** - wp-config backups and realtime log viewer with filtering and tail controls
- **Hosting Presets** - Optimized settings for General, IONOS, and Aruba environments

### Advanced Features (NEW in v1.1.0)
- **Critical CSS** - Inline critical CSS for above-the-fold optimization
- **CDN Integration** - Multi-provider CDN support (CloudFlare, BunnyCDN, StackPath, Custom)
  - Automatic URL rewriting for assets
  - Domain sharding support
  - API-based cache purging
- **Performance Monitoring** - Track page load times, queries, and memory usage over time
  - Real-time metrics collection
  - 7-day and 30-day trend analysis
  - Sample-based monitoring to reduce overhead
- **Scheduled Reports** - Automated email reports with performance scores and suggestions
  - Daily, weekly, or monthly frequency
  - Beautiful HTML email templates
  - Customizable content sections
- **WordPress Site Health** - Native integration with WordPress Site Health
  - 4 custom health checks (Cache, WebP, Database, Assets)
  - Detailed diagnostic information
- **Query Monitor Integration** - Deep performance insights when Query Monitor is active
  - Cache hit/miss tracking
  - Memory and timing breakdowns
  - Custom metrics support

### Developer Experience
- **WP-CLI Commands** - Full command-line interface for automation
- **Centralized Logging** - Advanced logging system with levels and context
- **Rate Limiting** - Protection against abuse of resource-intensive operations
- **Event System** - Extensible event dispatcher with 15+ events
- **Repository Pattern** - Clean data access layer with WpOptionsRepository and TransientRepository
- **Value Objects** - Immutable objects for type safety (CacheSettings, PerformanceScore, etc.)
- **Enums** - Type-safe enumerations for hosting presets, cache types, log levels
- **Interfaces** - Contract-based architecture for better testability

## Installation

1. Copy the `fp-performance-suite` directory into `wp-content/plugins/` or upload the packaged ZIP.
2. Activate **FP Performance Suite** from the WordPress Plugins screen.
3. Navigate to **FP Performance** in the admin menu to configure caching, optimization, media, database, logs, presets, and safety options.

## Usage

- Use the **Dashboard** page to review the performance score and quick shortcuts to module panels.
- Configure **Cache** for page caching and browser caching, including purge and warmup routines.
- In **Assets**, enable minification, defer scripts, manage DNS prefetch, and adjust heartbeat behavior.
- Enable **Media** conversions to WebP by choosing GD or Imagick profiles and running bulk conversions.
- Schedule database tasks inside **Database** with dry-run reports before applying destructive actions.
- Manage **Logs** to toggle `WP_DEBUG`, tail log files, and restore backups.
- Apply environment-specific defaults through **Presets** and export/import custom settings under **Tools**.

## Hooks & Filters

### Core Hooks
- `fp_perfsuite_container_ready`: Fires after the dependency container has been built
- `fp_ps_required_capability`: Filter the capability required to view the admin pages
- `fp_ps_defer_skip_handles`: Filter script handles excluded from automatic deferral
- `fp_ps_db_scheduled_scope`: Filter the scope of scheduled database cleanups
- `fp_ps_gzip_enabled`: Filter the detected gzip compression status in the scorecard
- `fp_ps_gzip_detection_evidence`: Filter the evidence used for gzip detection scoring
- `fp_ps_require_critical_css`: Filter whether critical CSS is considered mandatory by the scorecard

### New Hooks (v1.1.0)

#### Lifecycle
- `fp_ps_plugin_activated` - Plugin activation
- `fp_ps_plugin_deactivated` - Plugin deactivation

#### Cache Events
- `fp_ps_cache_cleared` - After cache clear

#### WebP Events
- `fp_ps_webp_bulk_start` - Bulk conversion start
- `fp_ps_webp_converted` - Single image converted

#### Database Events
- `fp_ps_db_cleanup_complete` - Cleanup finished

#### .htaccess Events
- `fp_ps_htaccess_updated` - Rules injected
- `fp_ps_htaccess_section_removed` - Section removed

#### Logging Events
- `fp_ps_log_error`, `fp_ps_log_warning`, `fp_ps_log_info`, `fp_ps_log_debug` - Logging hooks

#### Rate Limiting
- `fp_ps_rate_limit_exceeded` - Rate limit exceeded

#### CDN Events
- `fp_ps_cdn_settings_updated` - CDN settings changed
- `fp_ps_cdn_purge_all` - CDN purge all
- `fp_ps_cdn_purge_file` - CDN purge file

**Complete Reference**: See [docs/HOOKS.md](docs/HOOKS.md) for detailed documentation and examples.

## Support

- Documentation and updates: https://francescopasseri.com
- GitHub issues: https://github.com/franpass87/FP-Performance/issues

## Development

### Setup
```bash
composer install
```

### Available Commands
```bash
# Testing
./vendor/bin/phpunit                    # Run all tests
./vendor/bin/phpunit tests/LoggerTest.php    # Run specific test

# Code Quality
./vendor/bin/phpcs                      # Check coding standards
./vendor/bin/phpstan analyse            # Static analysis

# Documentation
composer sync:author                    # Update author metadata
composer sync:docs                      # Sync documentation
composer changelog:from-git             # Generate changelog
```

### WP-CLI Commands (when installed)
```bash
wp fp-performance cache clear           # Clear page cache
wp fp-performance db cleanup --dry-run  # Database cleanup
wp fp-performance webp convert          # Convert images to WebP
wp fp-performance score                 # Show performance score
wp fp-performance info                  # Plugin information
```

### Testing
- Unit tests in `tests/` directory
- Test coverage for core utilities (Logger, RateLimiter, ServiceContainer)
- Value Objects tests (CacheSettings, PerformanceScore)
- Integration tests for services

### Architecture
- **PSR-4 Autoloading** - `FP\PerfSuite\` namespace
- **Dependency Injection** - ServiceContainer with lazy loading
- **Repository Pattern** - Clean data access layer
- **Event-Driven** - EventDispatcher with typed events
- **Interface-Based** - Contracts for testability

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history and the latest updates.

## Assumptions

- Tested with WordPress 6.5 and PHP 8.2 in shared hosting scenarios.
- Requires filesystem access for caching, `.htaccess` updates, and log tailing features.
