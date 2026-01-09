# Change Log - HttpData

## [1.0.7] - 2026-01-09

### Added

- use of DataManipulator library in order to unify string message conventions
- RequestReader is invokable in order to create a PSR RequestInterface compatible object

### Changed

- UCWords for headers instead ucfirst

### Fixed

- Double breakline separating headers and message body, always appened after headers
- Multi line for headers set-cookie on responses

## [1.0.6] - 2025-12-27

### Added

- Checked full compatibility with php versions from 8.1 to 8.5
- Request Message parser from file, stream or raw string with the availability to send to globals, in order to retrieve, parse and use an HTTP message and use it as a web server
- Set-cookie header parser, in order to convert the header string to object
- SetCookie object, for create from handlers, add to headers as stringable and send with other headers or invoke to send from code

### Changed

- Message is now stringable
- Request is now stringable
- Response is now stringable
- Upgrade Phpunit to v10

### Fixed

- hide CURLStringFile for php > 8.1.0 in order to avoid deprecated warning message
- fix for multi upload using arrays of files with same name
- parse post body when is not a html form
- Fix linebreaks unifying for distincts operative systems

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
