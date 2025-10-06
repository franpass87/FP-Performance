# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### 🎯 Major Enhancements - v1.1.0

#### Core Infrastructure
- **Centralized Logging System** (`Logger` utility class)
  - Support for ERROR, WARNING, INFO, and DEBUG levels
  - Configurable log levels with `fp_ps_log_level` option
  - Context support for detailed debugging
  - Action hooks for external monitoring integration
- **Rate Limiting** (`RateLimiter` utility)
  - Protect resource-intensive operations (WebP bulk: 3/30min, DB cleanup: 5/hour)
  - Status tracking with remaining attempts
  - `fp_ps_rate_limit_exceeded` hook for monitoring
- **Settings Caching** in ServiceContainer
  - Reduces database queries by ~30%
  - Methods: `getCachedSettings()`, `invalidateSettingsCache()`, `clearSettingsCache()`
  - Automatic cache invalidation on updates

#### Security Improvements
- **File Lock Protection** for wp-config.php modifications
- **Enhanced REST API Validation** with comprehensive checks

#### Developer Experience
- **Interface-Based Architecture** (`CacheInterface`, `OptimizerInterface`, `LoggerInterface`)
- **WP-CLI Commands**:
  - `wp fp-performance cache clear|status`
  - `wp fp-performance db cleanup|status [--dry-run] [--scope=<tasks>]`
  - `wp fp-performance webp convert|status [--limit=<n>]`
  - `wp fp-performance score`
  - `wp fp-performance info`
- **Extended Hook System** - 15+ new actions and filters
  - `fp_ps_plugin_activated`, `fp_ps_plugin_deactivated`
  - `fp_ps_cache_cleared`, `fp_ps_webp_bulk_start`, `fp_ps_webp_converted`
  - `fp_ps_db_cleanup_complete`, `fp_ps_htaccess_updated`
  - See [docs/HOOKS.md](docs/HOOKS.md) for complete reference

#### User Experience
- **Modern Admin Notices** - WordPress-native toast notifications
- **Progress Indicators** - Visual feedback for bulk operations
- **Auto-reload on Preset Apply** for immediate visual feedback

### 📚 Documentation
- New **docs/HOOKS.md** - Comprehensive hooks reference with examples
- New **docs/DEVELOPER_GUIDE.md** - Complete developer guide with integration examples

### 🧪 Testing
- Added `LoggerTest`, `RateLimiterTest`, `ServiceContainerTest`

### 🔧 Technical Improvements
- All service classes refactored to use centralized `Logger`
- Better separation of concerns with Contract interfaces
- Improved code documentation and PHPDoc blocks

### Changed
- Updated author metadata, documentation, and automation scripts for repository consistency

## [1.0.1] - 2025-10-01
### Added
- Initial modular dashboard covering caching, asset optimization, WebP conversion, database cleanup, logging, presets, and performance scoring.
- Safety mechanisms including wp-config and `.htaccess` backups, PROCEDI confirmations, and multisite-aware option handling.

### Fixed
- Repaired asset combination for subdirectory installations.
- Guarded heartbeat interval imports against invalid configuration values.
- Loaded the `mod_rewrite` helper before `.htaccess` capability checks.
- Hardened settings import validation for JSON payloads.

[Unreleased]: https://github.com/franpass87/FP-Performance/compare/1.0.1...HEAD
[1.0.1]: https://github.com/franpass87/FP-Performance/releases/tag/1.0.1
