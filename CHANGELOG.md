# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [unreleased]
### Added
- Thumbnails
  - Generate thumbnails using workers
  - Serve thumbnails using ThumbnailController

### Changed
- `Docker` Use `composer install` instead of update when building docker image
- Controllers extend `AbstractController` instead of `Controller` (symfony changes)
- `JavFile::setPath` will also set the filename (using pathinfo) if filename is not set
- `JAVThumbnailService::generateThumbs` now fires `mt` in a correct manner

## v0.1.0
### Added
- Initial public release
- File scanner
- Check video integrity using FFmpeg
- Calculate file hashes
- Implemented messagebus to distribute workload over (multiple) workers

[unreleased]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.0...HEAD
