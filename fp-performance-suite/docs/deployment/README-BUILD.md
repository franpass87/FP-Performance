# Build & Release Guide

## Prerequisites

- PHP 8.2 (CLI) with `php` available in your PATH.
- Composer 2.x.
- zip and rsync utilities (available by default on macOS/Linux, for Windows use WSL or Git Bash).

## Local build workflow

### Bump version automatically

```bash
bash build.sh --bump=patch
```

### Set an explicit version

```bash
bash build.sh --set-version=1.2.3
```

Both commands will:

1. Update the plugin header version (using `tools/bump-version.php`).
2. Install production dependencies with Composer (no dev packages).
3. Generate an optimized Composer autoloader.
4. Stage runtime files under `build/fp-performance-suite/`.
5. Produce a distributable ZIP archive in `build/` (timestamped by default).
6. Print the final version number and ZIP path.

Use `--zip-name=<custom-name>` to override the default archive filename.

## GitHub Action release

Push a tag that starts with `v` (e.g. `v1.2.3`). The `Build plugin zip` workflow will:

1. Install PHP 8.2.
2. Install Composer dependencies without dev requirements.
3. Recreate the clean build directory with the same exclusions as `build.sh`.
4. Create a ZIP archive named `fp-performance-suite-<tag>.zip`.
5. Publish the ZIP as a workflow artifact named `plugin-zip`.

Download the artifact from the workflow run associated with the tag to obtain the packaged plugin.
