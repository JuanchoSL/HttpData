# Change Log HttpData

## [1.0.2] - 2025-06-07

### Added

### Changed

- Change composer support from php v8.1

### Fixed

- retrieve data and mimetype from stdin

## [1.0.1] - 2025-06-02

### Added

- Methods documentation
- params definitions
- error control when streams can not be opened or readed
- github actions in order to launch tests on commit to develop branch

### Changed

- update composer, fixing required versions in order to use stable releases

### Fixed

- extract headers from origin when we are creating a ServerRequest from a Request, instead \_SERVER globals
- extract body from non POST requests for php version prior 8.4

## [1.0.0] - 2025-04-22

### Added

- Initial release, first version

### Changed

### Fixed
