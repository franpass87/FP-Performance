# FP Performance Suite

FP Performance Suite is a modular performance optimization plugin tailored for shared hosting environments. It combines caching, asset optimization, database cleanup, image conversion, logging controls, and provider-specific presets behind a unified dashboard.

## Features

- **Filesystem Page Cache** with safe controls and quick purge.
- **Browser Cache Headers** with optional `.htaccess` automation (Apache aware).
- **Asset Optimizer** for HTML minification, JS defer/async, DNS prefetch, preload management, heartbeat throttling, and emoji removal.
- **WebP Converter** supporting Imagick and GD with lossy/lossless options, bulk processing, and coverage reporting.
- **Database Cleaner** featuring dry-run reports, chunked operations, transient cleanup, and table optimization with confirmation.
- **Realtime Log Center** to toggle `WP_DEBUG`, review tailing logs with filters, and restore wp-config backups.
- **Preset Bundles** for Generale, IONOS, and Aruba shared hosting defaults with rollback capability.
- **Technical SEO Performance Score** (0–100) with actionable suggestions and quick navigation.
- **Import/Export** JSON settings, diagnostic tests, and multisite-aware options.
- **Accessibility & Safety**: risk semaphores, PROCEDI confirmation for destructive actions, nonce/capability checks, and wp-config/.htaccess backups.

## Requirements

- WordPress 6.2+
- PHP 8.0 – 8.3
- Shared hosting compatible (no external services required)

## Development

1. Clone the repository into `wp-content/plugins/fp-performance-suite`.
2. Run `composer dump-autoload` if you plan to use Composer autoloader (a lightweight PSR-4 autoloader ships in the plugin for convenience).
3. Activate the plugin via the WordPress admin.

### Testing

Sample PHPUnit tests are available under `/tests`. Run them with:

```bash
phpunit --bootstrap tests/bootstrap.php tests
```

## Safety Notes

- wp-config.php and .htaccess modifications are guarded with timestamped backups.
- Database operations support dry-run mode by default and require PROCEDI confirmation for destructive batches.
- WebP conversions keep originals unless explicitly disabled.
- Settings include a “Safety mode” to constrain high-risk operations.

## Uninstall

Uninstalling the plugin removes all plugin-specific options.

## Release process

Refer to [README-BUILD.md](README-BUILD.md) for the complete packaging workflow. The typical release steps are:

1. Run `bash build.sh --bump=patch` (or `--set-version=1.2.3`) to update the plugin version and generate a clean ZIP in `build/`.
2. Review the generated archive and commit the version bump and build artefacts.
3. Push a tag like `v1.2.3` to trigger the GitHub Action that uploads the packaged plugin as a workflow artifact.
