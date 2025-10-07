# Change Log HttpData

## [1.0.5] - 2025-10-07

### Added

### Changed

### Fixed

- fix for moveTo method on uploadFile

## [1.0.4] - 2025-08-31

### Added

- check for uri reserved chars in order to encode it at userinfo

### Changed

- removed auto urlencode for user and password into withUserInfo function

### Fixed

- when an upload file fails, create a stream from empty string in order to fill the object with the assigned error

## [1.0.3] - 2025-06-16

### Added

- UriFactory->fromGlobals method can extract domain data from 'HTTP_HOST', 'SERVER_NAME' or 'HOSTNAME' server global
- UriFactory->fromGlobals method can extract uri data from
  - REQUEST_URI server global
  - SCRIPT_URL or PATH_INFO with a combination with QUERY_STRING globals

### Changed

- ServerRequestFactory->fromGlobals method can extract uri from UriFactory->fromGlobals

### Fixed

- request target now is extracted from uri
- ensure that query params string is encoded

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
