# FP Performance Suite FAQ

## Does the plugin require server-level modules?
No. FP Performance Suite runs on standard shared hosting and only needs PHP 8.0+ with filesystem write access. `.htaccess` changes are optional and skipped automatically if unsupported.

## Can I disable modules I do not need?
Yes. Each module (cache, assets, media, database, logs) has toggles within the Settings page. Disabled modules stop registering hooks and stop scheduling tasks.

## How do WebP conversions handle existing images?
The Media module keeps original files unless explicitly disabled. Conversions can run in bulk, respect Imagick or GD availability, and report coverage percentages for tracking progress.

## What happens if cron jobs are disabled on my site?
The Database cleaner schedules maintenance through WordPress cron. If cron is disabled, you can trigger tasks manually from the Database page or configure an external cron hitting `wp-cron.php`.

## Can I override the required capability for the admin menu?
Yes. Use the `fp_ps_required_capability` filter to return a custom capability string, e.g. `return 'manage_network';` for multisite setups.

## How do I integrate the plugin with deployment pipelines?
Use the provided Composer scripts: `composer sync:author` to normalise metadata, `composer sync:docs` for documentation placeholders, and `composer changelog:from-git` to scaffold changelog entries. Combine them with your CI steps before packaging.

## Does the page cache support multisite?
The cache stores pages under site-specific directories. Multisite compatibility is covered in the container bootstrap, and presets respect individual site settings.

## How can I submit bugs or feature requests?
Open an issue on GitHub at https://github.com/franpass87/FP-Performance/issues or reach out via https://francescopasseri.com for commercial support.
