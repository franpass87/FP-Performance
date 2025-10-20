=== FP Performance Suite ===
Contributors: francescopasseri, franpass87
Tags: performance, caching, optimization, webp, database
Requires at least: 6.2
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Plugin Homepage: https://francescopasseri.com

Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.

== Description ==

FP Performance Suite delivers a modular control center for WordPress administrators on shared hosting. It combines page caching, browser cache headers, asset optimization, WebP conversion, database cleanup, debug toggles, realtime log viewing, and hosting-specific presets behind a unified dashboard with safety guards.

= Features =

* Filesystem page cache with instant purge controls.
* Browser cache headers manager with automatic `.htaccess` updates when available.
* Asset optimizer for minification, script deferral, DNS prefetch, preload rules, and heartbeat throttling.
* WebP converter supporting GD and Imagick with lossy/lossless profiles and coverage reporting.
* Advanced database optimizer with health score, fragmentation analysis, missing indexes detection, MyISAM to InnoDB conversion, charset optimization, plugin-specific cleanup (WooCommerce, Elementor, Yoast), trend reports, and automated snapshots.
* Debug toggler with wp-config backups and realtime log viewer with filtering.
* Preset bundles tailored for common shared hosting providers and a technical performance scorecard.
* Import/export of configuration, multisite-aware options, and accessibility-minded confirmations.

== Installation ==

1. Upload the `fp-performance-suite` folder to `/wp-content/plugins/` or install the ZIP via the WordPress dashboard.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Visit **FP Performance** in the admin menu to configure caching, optimization, and other modules.

== Frequently Asked Questions ==

= Does the plugin work on managed or shared hosting? =
Yes. The suite is built to respect shared hosting limits and does not require external services.

= Do I need Imagick to convert images to WebP? =
No. The converter supports both Imagick and GD. If Imagick is unavailable it will fall back to GD automatically.

= Will it modify my `wp-config.php` or `.htaccess`? =
Only when you enable features that require it. Every change is safeguarded with timestamped backups and clear confirmations.

== Screenshots ==

1. Dashboard overview with performance score and module shortcuts.
2. Cache controls showing purge options and status indicators.
3. Asset optimizer settings for script deferral and preload management.

== Hooks ==

* `fp_perfsuite_container_ready`: Fires after the service container is built.
* `fp_ps_required_capability`: Filter the capability required to access the admin pages.
* `fp_ps_defer_skip_handles`: Filter script handles excluded from automatic deferment.
* `fp_ps_db_scheduled_scope`: Filter scheduled cleanup scope before database maintenance runs.
* `fp_ps_gzip_enabled`: Filter the detected gzip compression status when calculating the score.
* `fp_ps_gzip_detection_evidence`: Filter gzip detection evidence in the performance score.
* `fp_ps_require_critical_css`: Filter whether critical CSS is considered mandatory in the score.

== Changelog ==

= 1.4.0 =
* 🎉 MAJOR: Advanced Database Optimization Suite
* NEW: Database Health Score (0-100%) with grade system (A-F)
* NEW: Fragmentation analysis with detailed metrics per table
* NEW: Missing indexes detection with priority recommendations
* NEW: Storage engine analysis and MyISAM to InnoDB conversion
* NEW: Charset analysis and automatic utf8mb4 conversion
* NEW: Advanced autoload optimization with plugin grouping
* NEW: Plugin-specific cleanup (WooCommerce, Elementor, Yoast SEO, ACF, CF7)
* NEW: Database snapshot system with trend analysis
* NEW: Growth projections (30/90 days) and ROI calculations
* NEW: Report export in JSON/CSV formats
* NEW: Automated health alerts via email
* NEW: 5 new WP-CLI commands (health, fragmentation, plugin-cleanup, report, convert-engine)
* IMPROVED: Database admin page with modern dashboard UI
* IMPROVED: Color-coded severity indicators
* ADDED: Backup system for critical operations
* ADDED: Operation history tracking (last 100 operations)
* Performance: Up to 50% faster queries with optimized indexes
* Storage: Recover 10-30% database space on average
* UX: Completely redesigned database interface

= 1.1.0 =
* Major enhancement release with 45+ improvements
* Added centralized logging system with configurable levels
* Added rate limiting for resource-intensive operations
* Added settings caching to reduce database queries
* Added WP-CLI commands for automation
* Extended hook system with 15+ new actions and filters
* Modern admin notices and progress indicators
* Comprehensive developer documentation
* See `CHANGELOG.md` for complete details

= 1.0.1 =
* Initial public release with caching, optimization, WebP, database, logging, and preset modules

== Upgrade Notice ==

= 1.4.0 =
MAJOR UPDATE: Advanced Database Optimization Suite with Health Score, fragmentation analysis, plugin-specific cleanup, trend reports, and 5 new WP-CLI commands. Fully backward compatible. Safe to upgrade.

= 1.1.0 =
Major feature release with improved logging, rate limiting, WP-CLI support, and extended hooks system. Safe to upgrade from 1.0.1.

= 1.0.1 =
Initial public release with caching, optimization, WebP, database, logging, and preset modules.

== Support ==

Support and documentation: https://francescopasseri.com
