# Release Notes for IP Restrictor

## 1.3.0 - 2025-06-03

### Changed
- Replaced `dxw/cidr` dependency with `s1lentium/iptools` to remove vulnerable dependency on `phpseclib/phpseclib` v2

## 1.2.0 - 2024-07-11

### Changed
- Use Monolog for proper log rotation
- Cleanup unused classes

## 1.1.4 - 2024-03-20

### Changed
- Added remote IP address to log messages for better debugging

### Fixed
- Fixed log messages for front-end erroneously referring to control panel

## 1.1.3 - 2024-02-17

### Changed
- Updated src/config.php
- Updated README.md

## 1.1.2 - 2024-02-13

### Fixed
- Replaced getIsCpRequest() with getIsConsoleRequest() [#1](https://github.com/jrrdnx/craft-ip-restrictor/issues/1)

## 1.1.1 - 2024-02-13

### Fixed
- Don't redirect to plugin settings after install on console requests [#1](https://github.com/jrrdnx/craft-ip-restrictor/issues/1)

## 1.1.0 - 2024-02-10

### Changed
- Updated requirements for Craft CMS 5 compatibility
- Updated documentation URLs
- Updated icon

## 1.0.0 - 2024-01-13

### Initial release
