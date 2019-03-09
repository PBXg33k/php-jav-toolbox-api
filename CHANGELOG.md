3# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.4] - 2019-03-09
### Added
- Cleanup command
  - Will run check (again) before deleing file from disk 
    - use `--yes` to skip confirmation
    - use `--dry-run` to do a dry run (will update consistency if checks passes)
- Option to delete files from disk 
  - Related JavFile entry will be deleted from the database
### Changed
- CLI output updated
  - Use of sections
  - Progress bars
- Scan command overhaul
  - More use of events
  - Option to mute output using the `--silent` argument
- Refactored `FileScanService`
  - Use Symfony Finder instead of directoryIterator
    - Latter had a memory issue
- Failure of thumbnail generation (because a stalled mt process) will try to send a SIGKILL signal to the process to kill it by force.
- Changed the way thumbnails are handled
  - Thumnails are bound to inode instead of filenames
- Changed the way filenames are parsed
  - Use multiple parsers instead of one single regular expression pattern
- Changed Dockerfile
  - Add xdebug
  - Run composer install instead of update on build 

## [0.1.3] - 2019-01-12
### Changed
- Bugfixes
  - `Inode::meta` was not being populated causing `GetVideoMetadataMessageHandler` to trigger on already processed entries
  - Duplicate `Title` records were inserted into db
    - Patched code
    - Added migration which removes duplicate records and adds an unique constraint in db
  - Updated `composer.json`
  - Enhanced `JAVProcessorService::extractID`
  

## [0.1.2] - 2019-01-10
### Changed
- Bugfix on the Dockerfile

## [0.1.1] - 2019-01-10
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
- JavJack's faulty download check is based on filename instead of length (filename is either videoplayback or hexidecimal value)

### Removed
- Code cleanup

## 0.1.0
### Added
- Initial public release
- File scanner
- Check video integrity using FFmpeg
- Calculate file hashes
- Implemented messagebus to distribute workload over (multiple) workers

[Unreleased]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.4...HEAD
[0.1.4]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/PBXg33k/php-jav-toolbox-api/compare/v0.1.0...v0.1.1
