# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [unreleased]
### Added
- Thumbnails
  - Generate thumbnails using workers
  - Serve thumbnails using ThumbnailController
- Migrate inode data from `JavFile` table to `Inode` table

### Changed
- `Docker` changes
    - Use `composer install` instead of update when building docker image
    - Run migrations on startup to apply latest DB changes
- Controllers extend `AbstractController` instead of `Controller` (symfony changes)
- `JavFile::setPath` will also set the filename (using pathinfo) if filename is not set
- `JAVThumbnailService::generateThumbs` now fires `mt` in a correct manner
- Moved all information which is related to a file pointer from `JavFile` to `Inode`
    - This also prevents the same file to be checked multiple times if it's an exact duplicate
- Messages are always dispatched. It is up to the handler to check if processing is required
- Add couple of unit tests, more to be added in future updates

### Deleted
- Code cleanup

## v0.1.0
### Added
- Initial public release
- File scanner
- Check video integrity using FFmpeg
- Calculate file hashes
- Implemented messagebus to distribute workload over (multiple) workers

[unreleased]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.0...HEAD
