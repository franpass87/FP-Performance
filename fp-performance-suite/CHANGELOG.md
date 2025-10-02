# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Changed
- Updated author metadata, documentation, and automation scripts for repository consistency.

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
