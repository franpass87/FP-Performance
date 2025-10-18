# FP Performance Suite Overview

Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.

## Key capabilities

- **Caching**: Filesystem page cache with purge controls, warmup tools, and browser cache headers.
- **Asset optimization**: HTML/CSS/JS minification, defer/async toggles, DNS prefetch, preload rules, and heartbeat throttling.
- **Media optimization**: WebP conversion powered by GD or Imagick with bulk processing, quality presets, and coverage reports.
- **Database maintenance**: Dry-run reporting, transient cleanup, scheduled maintenance, and table optimization.
- **Debug & logging**: Toggle `WP_DEBUG`, tail log files with filters, and restore safe backups of configuration files.
- **Presets & scoring**: Ready-made profiles for popular shared hosts and a technical performance score with actionable guidance.
- **Safety net**: PROCEDI confirmations, multisite awareness, and automated backups for `wp-config.php` and `.htaccess` changes.

## Module breakdown

| Module | Description |
| --- | --- |
| Dashboard | Summarises performance score, module status, and recent diagnostics. |
| Cache | Manages filesystem page cache, purge operations, and browser caching rules. |
| Assets | Provides HTML/JS/CSS optimization, script deferment, DNS prefetch, preload, and heartbeat throttling. |
| Media | Converts images to WebP with configurable quality and storage policies. |
| Database | Cleans transients, revisions, spam, and optimizes tables with dry-run previews and scheduling. |
| Logs | Controls debug mode, manages log files, and streams live log output with filtering. |
| Presets | Ships shared hosting defaults (General, IONOS, Aruba) and allows rollback. |
| Tools | Handles import/export, diagnostics, and maintenance helpers. |
| Settings | Centralises feature toggles, safety mode, and advanced options. |

## Requirements

- WordPress 6.2 or higher
- PHP 8.0 or higher (tested up to PHP 8.3)
- Filesystem write access for cache, logs, and backup files
- Optional Imagick extension for advanced WebP conversion (falls back to GD)
