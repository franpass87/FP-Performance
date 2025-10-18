# FP Performance Suite Architecture

## High-level structure

FP Performance Suite is organised around a lightweight service container (`FP\PerfSuite\ServiceContainer`) that wires together modular services for caching, asset optimization, media conversion, database maintenance, logging, presets, and scoring. The plugin boots through `FP\PerfSuite\Plugin::init()`, registers services, and exposes them to WordPress hooks via dedicated admin pages and HTTP routes.

```
WordPress hooks
      |
      v
FP\PerfSuite\Plugin::init()
      |
      +-- ServiceContainer
              |
              +-- Admin UI (Menu, Assets, Pages)
              +-- Services\Cache (PageCache, Headers)
              +-- Services\Assets\Optimizer
              +-- Services\Media\WebPConverter
              +-- Services\DB\Cleaner
              +-- Services\Logs (DebugToggler, RealtimeLog)
              +-- Services\Presets\Manager
              +-- Services\Score\Scorer
```

## Core services

- **PageCache**: Manages filesystem-based page caching, purge routines, warmers, and integration with presets.
- **Headers**: Generates browser caching headers and coordinates `.htaccess` updates through the `Utils\Htaccess` helper.
- **Optimizer**: Applies HTML/JS/CSS minification, defer/async rules, DNS prefetch, preload hints, heartbeat throttling, and emoji removal.
- **WebPConverter**: Converts media uploads to WebP via GD or Imagick, tracks coverage, and handles bulk operations.
- **Cleaner**: Performs database cleanup tasks, primes cron schedules, and exposes scope filters for custom maintenance.
- **DebugToggler & RealtimeLog**: Toggle WordPress debug flags, manage `wp-config.php` backups, and stream log output.
- **PresetManager**: Aggregates service defaults for General, IONOS, and Aruba profiles with rollback support.
- **Scorer**: Computes technical performance scores and aggregates module insights.

## Admin interface

The admin menu is registered by `Admin\Menu`, which wires submenu pages for Dashboard, Cache, Assets, Media, Database, Presets, Logs, Tools, and Settings. Each page lives under `Admin\Pages\*` with dedicated view templates in `views/`.

Admin assets (styles/scripts) are enqueued by `Admin\Assets`, while HTTP endpoints for AJAX and REST-style interactions are registered through `Http\Routes`.

## Data storage & configuration

- Plugin options are stored under namespaced keys (e.g., `fp_perfsuite_*`).
- Cached pages live within the plugin directory using the `Utils\Fs` helper to abstract filesystem operations.
- `.htaccess` and `wp-config.php` changes trigger timestamped backups via `Utils\Fs` before applying modifications.
- Schedules rely on WordPress cron hooks; `Cleaner::CRON_HOOK` orchestrates recurring database maintenance.

## Hooks & filters

- `fp_perfsuite_container_ready`: Fires once the service container is assembled, enabling service overrides.
- `fp_ps_required_capability`: Filter to adjust required capability for accessing admin pages.
- `fp_ps_defer_skip_handles`: Filter to customise script handles excluded from defer/async automation.
- `fp_ps_db_scheduled_scope`: Filter the scope of database cleanup tasks triggered by cron.
- `fp_ps_gzip_enabled`: Filter to override gzip detection results in the performance score.
- `fp_ps_gzip_detection_evidence`: Filter to supply alternate gzip evidence strings.
- `fp_ps_require_critical_css`: Filter to indicate whether critical CSS is mandatory for scoring.

## Flows

1. **Activation**: `Plugin::onActivate()` determines the current version, stores it in `fp_perfsuite_version`, and primes database cleanup schedules.
2. **Boot**: During `plugins_loaded`, the service container is created, hooks are registered, and admin routes/pages are bootstrapped.
3. **Request handling**: On `init`, services register their hooks (cache warmers, asset filters, WebP handlers, cron).
4. **Cron**: The database cleaner schedules tasks via `maybeSchedule()` and executes scoped cleanups on `Cleaner::CRON_HOOK`.
5. **Deactivation**: `Plugin::onDeactivate()` clears scheduled database maintenance hooks.

## Internationalisation

The plugin loads its text domain `fp-performance-suite` from the `/languages` directory using `load_plugin_textdomain()` during `init`.
