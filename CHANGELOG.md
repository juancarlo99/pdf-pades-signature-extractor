# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-20
### Added
- Initial professionalization of the library: CI workflow, PHPCS (PSR-12), PHPStan config, EditorConfig, MIT License.
- Robust PKCS#7/CMS certificate extraction with OpenSSL CLI fallback.
- Improved tests and fixed sample paths.
- Dockerfile updated to include OpenSSL CLI.

### Changed
- `phpunit.xml` updated for PHPUnit 10.
- `PadesExtractor` now validates input file path and uses strict types.
