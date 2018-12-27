# PHPJavToolbox

PHPJavToolbox is a project which aims to make managing JAV collections easier.

**PHPJavToolbox is in active development**

It is meant for those who have a (large) collection unsorted JAV files and wishes to manage said collection.
See below for the feature's being build.

Check the [CHANGELOG](CHANGELOG.md) for the actual changes being worked on.

## Requirements
- PHP 7.3 
- MySQL
- ffmpeg
- [mt](https://github.com/mutschler/mt)
- [XXHSUM](https://cyan4973.github.io/xxHash/)
- md5sum/sha1sum/sha512sum

## Installation
#### Docker-compose
This project is aimed to be used within a docker container, and thus is developed with this setup in mind.
1. Create a `docker-compose.override.yml` file and override the appropiate parameters to accomodate to your local environment.
    1. Make sure the `/media` directory points to a directory on your machine which contains video files
    2. If using another docker path for media, override `MEDIA_DIR` and `THUMB_DIR` environment in the app container to match the directory
2. `docker-compose build`
3. Run composer's post install scripts
    1. `docker-compose exec app bash`
    2. `composer install`
    3. `php bin/console doctrine:database:create`
    4. `php bin/console doctrine:schema:update --force`
    5. `php bin/console doctrine:fixtures:load --no-interaction`

## Features
- (DONE) Scan local filesystem for JAV Titles
- (DONE) Check video consistency, mark broken (video) files
- (DONE) Filter/Only index actual video files
- (WIP) Build database with local titles (and duplicates)
- (WIP) Lookup external sources for title information and covers
- (WIP) Generate metadata for found titles
- (WIP) Generate thumbnails from files 
- (TODO) Build a filesystem with hardlinks using IDs, tags and actresses in a tree structure
- (TODO) Symlink extra/duplicate files
- (TODO) Sharing system
