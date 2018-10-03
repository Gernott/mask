# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

