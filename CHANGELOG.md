# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.3.2]

### Added
- Link backend header of element to its edit page. [#159](https://github.com/Gernott/mask/pull/159)

### Changed
- Sort complete `mask.json` alphabetically to avoid merge conflicts. [#163](https://github.com/Gernott/mask/pull/163)

### Fixed
- Use `mb_substr` instead of `substr` to avoid encoding problems with German "umlauts". [#167](https://github.com/Gernott/mask/pull/167)
- fix problem with losing configuration for link fields

