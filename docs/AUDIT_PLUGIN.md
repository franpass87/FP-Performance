# Plugin Audit Report — FP Performance Suite — 2025-10-01

## Summary
- Files scanned: 62/62
- Issues found: 6 (Critical: 0 | High: 1 | Medium: 3 | Low: 2)
- Key risks:
  - Front-end asset combination rewrites bundles on every request, causing heavy disk I/O on shared hosting.
  - WebP bulk conversion runs synchronously in admin requests, risking timeouts and partial conversions.
  - Stored plugin version is outdated, threatening future migration logic and consistency checks.
- GitHub release workflow zips into a missing directory, so automated builds fail before publishing artifacts.
- Recommended priorities: ISSUE-001 → ISSUE-002 → ISSUE-003

## Manifest mismatch
- Current manifest hash `dd86f704656f93a2b818915c1680d6722374b0bffb69c18fbf56358b962e522f` differs from the last recorded `8f25d103ff8c9aa573ba6637cb1d802be6b5bfe2e6664fc809f7b4dc7b693987` after adding repository-level automation and documentation files.
- Added to scope and audited: `.codex-state.json`, `.github/workflows/build.yml`, `.gitignore`, `README.md`, `docs/AUDIT_PLUGIN.json`, `docs/AUDIT_PLUGIN.md`, and `docs/feature-suggestions.md`.
- Removed files: _none_.
- Prior manifest record: hash `9f4b0c9bbc6922b091b4847bfdc91e55bb562689af90f50fe5e6a244b66d3021` expanded the scope to include `.gitattributes`, GitHub workflow, README files, build script, Composer lockfile, and PHPCS/PHPStan/PHPUnit configs under `fp-performance-suite/`.

## Issues
### [High] Asset combination rewrites on every page load
- ID: ISSUE-001
- File: src/Services/Assets/Optimizer.php:524-587
- Snippet:
  ```php
  foreach ($files as $file) {
      $hashParts[] = $file['url'] . '|' . ($mtime ?: 0) . '|' . ($size ?: 0);
      $asset = file_get_contents($file['path']);
      if (false === $asset) {
          return null;
      }
      $contents .= '/* ' . $file['handle'] . " */\n" . $asset . "\n";
  }
  $contentsHash = md5($contents);
  if (file_exists($fullPath)) {
      $existingHash = md5_file($fullPath);
      if (is_string($existingHash) && $existingHash === $contentsHash) {
          return [
              'handles' => $handles,
              'url' => $url,
          ];
      }
  }
  ```

Diagnosis: When CSS/JS combination is enabled, every front-end request reads and concatenates each source file before checking whether the existing combined asset is up to date. Even cache hits still read the full contents of every dependency, so busy sites repeatedly perform large synchronous I/O and memory allocations.

Impact: Performance — shared hosting can hit CPU and disk limits, causing slow page loads or hitting resource throttling despite caching being enabled.

Repro steps (se applicabile): Enable “Combine CSS/JS” in the Assets page, load a front-end page, and trace filesystem calls (e.g. strace) to observe every request re-reading each asset.

Proposed fix (concise):

- Derive the combined filename from metadata first (mtime/size hash) without reading file contents.
- If the existing combined file is current, return immediately; only read and concatenate sources when regeneration is required.

Side effects / Regression risk: Low — ensure regeneration still occurs when sources change; add tests for cache hit/miss behavior.

Est. effort: M

Tags: #performance #assets #shared-hosting

### [Medium] WebP bulk conversion blocks admin requests
- ID: ISSUE-002
- File: src/Services/Media/WebPConverter.php:149-177
- Snippet:
  ```php
  $query = new WP_Query([
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'post_mime_type' => ['image/jpeg', 'image/png'],
      'posts_per_page' => $limit,
      'offset' => $offset,
      'fields' => 'ids',
  ]);
  foreach ($query->posts as $attachment_id) {
      $metadata = wp_get_attachment_metadata($attachment_id);
      $result = $this->processAttachment((int) $attachment_id, $metadata, $settings);
      if (!empty($result['converted'])) {
          $converted++;
      }
      if ($metadata !== $result['metadata']) {
          wp_update_attachment_metadata($attachment_id, $result['metadata']);
      }
  }
  ```

Diagnosis: Bulk conversion executes CPU-intensive Imagick/GD work sequentially inside a single admin POST request. Larger libraries or slower shared hosts can easily exceed memory/time limits, yielding partial conversions and confusing success messages.

Impact: Performance/UX — admins may see timeouts or white screens, leaving attachments half-converted and without retries.

Repro steps (se applicabile): Trigger “Run Bulk Conversion” with 50+ large images on a constrained host; observe request timing out or PHP max execution errors.

Proposed fix (concise):

- Queue conversions via WP-Cron or batched AJAX, persisting progress between requests.
- Persist batch state (e.g., attachment IDs left) and process small chunks asynchronously.

Side effects / Regression risk: Medium — need to ensure progress UI reflects background processing and avoids duplicate work.

Est. effort: M

Tags: #performance #media #cron #ux

### [Medium] Activation stores stale plugin version
- ID: ISSUE-003
- File: src/Plugin.php:105-111
- Snippet:
  ```php
  public static function onActivate(): void
  {
      update_option('fp_perfsuite_version', '1.0.0');
      $cleaner = new Cleaner(new Env());
      $cleaner->primeSchedules();
      $cleaner->maybeSchedule(true);
  }
  ```

Diagnosis: The activation hook records version `1.0.0`, while the plugin header and constant advertise `1.0.1`. Future upgrade routines keyed to this option will mis-detect installed versions, potentially re-running migrations or skipping required updates.

Impact: Functional/maintenance — upgrade logic or compatibility checks that rely on the stored version can break, leading to stale configurations or duplicate cron scheduling.

Repro steps (se applicabile): Activate the plugin, inspect the `fp_perfsuite_version` option, and compare with the plugin header version.

Proposed fix (concise):

- Store `FP_PERF_SUITE_VERSION` (or the header value) during activation and future upgrade tasks.

Side effects / Regression risk: Low — limited to version management logic.

Est. effort: S

Tags: #maintenance #upgrade

### [Low] Progress endpoint lacks readability guard
- ID: ISSUE-004
- File: src/Http/Routes.php:182-192
- Snippet:
  ```php
  $file = FP_PERF_SUITE_DIR . '/../.codex-state.json';
  if (!file_exists($file)) {
      return rest_ensure_response([]);
  }
  $data = json_decode((string) file_get_contents($file), true);
  if (!is_array($data)) {
      $data = [];
  }
  return rest_ensure_response($data);
  ```

Diagnosis: The REST handler calls `file_get_contents` without verifying readability or suppressing warnings. On hosts with `open_basedir` or tightened permissions, PHP emits warnings into the error log when attempting to read outside the plugin directory.

Impact: Compatibility/Noise — pollutes debug logs and may leak internal paths; repeated warnings slow responses when the endpoint is polled.

Repro steps (se applicabile): Restrict `open_basedir` to the plugin directory and hit `/wp-json/fp-ps/v1/progress`; observe warnings in PHP error log.

Proposed fix (concise):

- Check `is_readable` before reading, wrap `file_get_contents` with error suppression or WP_Filesystem, and bail safely when unreadable.

Side effects / Regression risk: Minimal — only affects diagnostic endpoint behavior when the state file is absent.

Est. effort: S

Tags: #compatibility #rest #logging

### [Low] Unlimited .htaccess backups accumulate
- ID: ISSUE-005
- File: src/Utils/Htaccess.php:39-48
- Snippet:
  ```php
  $backup = $file . '.bak-' . gmdate('YmdHis');
  $this->fs->copy($file, $backup, true);
  return $backup;
  ```

Diagnosis: Every rule injection/removal creates a timestamped `.htaccess.bak-*` file without any retention policy. Frequent toggling fills wp-content with backups, consuming quota-limited shared hosting storage.

Impact: Maintenance/storage — unnecessary disk usage can trigger hosting limits and slow directory operations.

Repro steps (se applicabile): Toggle browser cache settings repeatedly and inspect the root `.htaccess` directory; multiple backup files accumulate.

Proposed fix (concise):

- Limit retained backups (e.g., keep the latest N files) or overwrite a single deterministic backup before writing.

Side effects / Regression risk: Low — ensure at least one recovery point remains before edits.

Est. effort: S

Tags: #maintenance #filesystem

### [Medium] GitHub release workflow fails without build directory
- ID: ISSUE-006
- File: .github/workflows/build.yml:12-19
- Snippet:
  ```yaml
      - name: Prepare build
        run: |
          cd fp-performance-suite
          zip -r ../build/fp-performance-suite.zip . -x '*.git*'
  ```

Diagnosis: The workflow zips the plugin into `../build/fp-performance-suite.zip`, but no `build/` directory is created beforehand. `zip` aborts with “No such file or directory”, so automation never produces artifacts.

Impact: Release/CI — GitHub Actions builds fail on every run, blocking automated packaging and distribution of the plugin zip.

Proposed fix (concise):

- Create the `build/` directory (e.g., `mkdir -p ../build`) before invoking `zip`, or write directly into an existing workspace path.

Side effects / Regression risk: Minimal — confined to CI workflow; ensure the directory is cleaned or ignored afterwards.

Est. effort: S

Tags: #ci #release #workflow

## Conflicts & Duplicates
None observed.

## Deprecated & Compatibility
- Progress endpoint should guard `file_get_contents` with `is_readable` to avoid warnings on hosts with `open_basedir` restrictions (src/Http/Routes.php:182-192).
- PHP 8.2/8.3 warnings attesi: none detected during static review.

## Performance Hotspots
- Asset combination (src/Services/Assets/Optimizer.php:524-587): avoid rebuilding bundles on every hit; cache metadata and regenerate only when inputs change.
- WebP bulk conversion (src/Services/Media/WebPConverter.php:149-177): move heavy conversion to asynchronous jobs to prevent timeouts.

## i18n & A11y
- No i18n or accessibility blockers identified; text domain usage appears consistent.

## Test Coverage
- No automated coverage for REST controllers (logs, presets, cleanup). Consider adding PHPUnit or integration tests to cover permission checks and long-running tasks.

## Next Steps (per fase di FIX)
- Ordine consigliato: ISSUE-001, ISSUE-002, ISSUE-003, ISSUE-004, ISSUE-005, ISSUE-006.
- Safe-fix batch plan:
  - Lotto 1: ISSUE-001 (optimize asset combination), ISSUE-004 (progress endpoint guard).
  - Lotto 2: ISSUE-002 (asynchronous WebP conversion).
  - Lotto 3: ISSUE-003 (version option), ISSUE-005 (backup retention), ISSUE-006 (CI build directory creation).
