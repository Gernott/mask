# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.1.2] - 2019-08-27

### Changed
- new version for typo3 repository without .git folder


## [4.1.1] - 2019-08-27

### Changed
- added banners

### Fixed
- fixed missing reusable mask fields in extending pages
- added indexes to tt_content for parent fields
- fixed wrong default-paths in extension config
- changed EM paths from EXT:... to typo3conf/ext/....

## [4.1.0] - 2018-12-07

### Added
- Added support for "EXT:" paths in LocalConfiguration [#193](https://github.com/Gernott/mask/pull/193)

### Changed
- Fluid-Templates are now being generated all upperCamelCase, no more underscores, except for fallback[#186](https://github.com/Gernott/mask/pull/186)
- Default paths in LocalConfiguration now point to EXT:mask_project/...

### Fixed
- Fixed broken sorting in repeating elements [#181](https://github.com/Gernott/mask/pull/181)
- Fixed broken default checkboxes [#178](https://github.com/Gernott/mask/pull/178)
- Fixed TS-Conditions for hidden pages and content elements [#203](https://github.com/Gernott/mask/pull/204)
- Prevent error in starttime and endtime when using strict_mode
- Changed default value of date field to null as per MySQL Standard [#197](https://github.com/Gernott/mask/pull/197)
- Fixed broken 'Activate/Deactivate content element' action [[BUGFIX] fix wrong locallang structure](https://github.com/Gernott/mask/commit/3701f2bdf698f7f2fb266a889ff41c8a255b7318)
- Fixed broken LocalConfiguration paths when missing trailing slash

## [4.0.0] - 2018-10-03

### Added
- Support TYPO3 v9. [#168](https://github.com/Gernott/mask/pull/168)
- All contentelements are now natively rendered with FLUIDTEMPLATE and are enriched with dataprocessors
- Link backend header of element to its edit page. [#159](https://github.com/Gernott/mask/pull/159)
- Added Localization Tab to every field
- Added possibility to use enableMultiSelectFilterTextfield with select boxes
- Added a hint that updating the validation of a repeating field could cause problems

### Changed
- Sort complete `mask.json` alphabetically to avoid merge conflicts. [#163](https://github.com/Gernott/mask/pull/163)
- Default paths in extension configuration changed from outdated fileadmin paths to a dummy site package structure
- All Fluid-Templates/Partials/Layouts have to begin with an uppercase letter, as is standard with fluid

### Removed
- Support for TYPO3 v8 LTS was dropped. [#168](https://github.com/Gernott/mask/pull/168)

### Fixed
- Use `mb_substr` instead of `substr` to avoid encoding problems with German "umlauts". [#167](https://github.com/Gernott/mask/pull/167)
- set correct icons for all content elements and add them to their own group in CType selectbox
- the configuration of the link wizard is now saved
