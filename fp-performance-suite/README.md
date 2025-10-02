# FP Performance Suite

> Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.

| Meta | Value |
| --- | --- |
| **Name** | FP Performance Suite |
| **Version** | 1.0.1 |
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

- Filesystem page cache with instant purge controls and cache warmers.
- Browser cache headers manager with `.htaccess` automation when available.
- Asset optimizer for minification, defer/async loading, DNS prefetch, preload directives, and heartbeat throttling.
- WebP converter supporting GD and Imagick with lossy/lossless profiles, bulk actions, and coverage reporting.
- Database cleaner with dry-run mode, transient cleanup, scheduled maintenance, and table optimization.
- Debug toggler with wp-config backups and realtime log viewer with filtering and tail controls.
- Hosting presets for General, IONOS, and Aruba environments plus a technical performance scorecard.
- Import/export of configuration, multisite-aware options, and accessibility-minded confirmations.

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

- `fp_perfsuite_container_ready`: Fires after the dependency container has been built.
- `fp_ps_required_capability`: Filter the capability required to view the admin pages.
- `fp_ps_defer_skip_handles`: Filter script handles excluded from automatic deferral.
- `fp_ps_db_scheduled_scope`: Filter the scope of scheduled database cleanups.
- `fp_ps_gzip_enabled`: Filter the detected gzip compression status in the scorecard.
- `fp_ps_gzip_detection_evidence`: Filter the evidence used for gzip detection scoring.
- `fp_ps_require_critical_css`: Filter whether critical CSS is considered mandatory by the scorecard.

## Support

- Documentation and updates: https://francescopasseri.com
- GitHub issues: https://github.com/franpass87/FP-Performance/issues

## Development

- Install dependencies with `composer install`.
- Run automated author metadata updates with `composer sync:author` (set `APPLY=1` to persist changes).
- Synchronize documentation placeholders with `composer sync:docs`.
- Generate changelog boilerplate with `composer changelog:from-git`.
- Execute unit tests via `./vendor/bin/phpunit` (bootstrap under `tests/bootstrap.php`).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history and the latest updates.

## Assumptions

- Tested with WordPress 6.5 and PHP 8.2 in shared hosting scenarios.
- Requires filesystem access for caching, `.htaccess` updates, and log tailing features.
