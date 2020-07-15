# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### [5.1.1] - 2020-07-15
### Important
- After updating from 5.1.0 or lower, clear the hard caches under Maintenance->Flush cache. Clearing red caches in not enough.
- Run database analyzer after that.

### Added
- Mask uses now the symfony DI.
- Added fe_group and editlock fields for access tab of mask inline records.

### Fixed
- Labels of inline tt_content records having a mask element as default are now displayed. [#327](https://github.com/Gernott/mask/pull/327)

### [5.1.0] - 2020-07-10
### Important
- There are some issues with the new fluid based page layout module. Especially if dealing with languages, please turn off this feature if encountering any issues in Settings -> Feature Toggles -> Fluid based page module.
- It may be necessary to run the Database Analyzer and/or save your mask elements to apply some changes.
- If you save your elements, the mask.json may change. Don't forget to put the changes in your version control system.

### Added
- Mask elements can now be found in TYPO3 global search [#191](https://github.com/Gernott/mask/issues/191)
- Added fluid_styled_content as dependency
- Added mask as global fluid namespace
- Added tests for core functionality of mask

### Changed
- Migrated Signal slots to PSR-14 events
- Replaced datepicker with TYPO3 standard
- General code cleanup and refactoring

### Removed
- Link element wizard config removed. [#79440](https://forge.typo3.org/issues/79440)
- rte_transform mode override removed. [#72856](https://forge.typo3.org/issues/72856)
- Removed parseFuncTSPath attribute in html code generation for RTE fields

### Fixed
- Fixed order of tabs for backend layout fields. [#315](https://github.com/Gernott/mask/pull/315)
- Backend Layouts translated correctly if referencing language files.
- Removed html in delete/purge dialog [#310](https://github.com/Gernott/mask/pull/310)
- Translation of inline fields in page records fixed [#309](https://github.com/Gernott/mask/pull/309)
- Deleted inline fields removed in mask.json [#307](https://github.com/Gernott/mask/pull/307)
- Validation of field keys works again in the element builder
- RTE presets in TSconfig are now applied [#306](https://github.com/Gernott/mask/pull/306)
- Removed the inline css in blockquote [#303](https://github.com/Gernott/mask/pull/303)
- Allowed content elements in nested content work again [#296](https://github.com/Gernott/mask/pull/296)
- Mask doesn't generate error log entries anymore [#294](https://github.com/Gernott/mask/pull/294)

## [5.0.0] - 2020-04-28

### Added
- Support for TYPO3 v10. Thank you [Jürgen Venne](https://github.com/juergen-venne) and all the sponsors!

### Changed
- basic code cleanup and minor refactoring
- complete redesign of Mask backend module
- hidden IRRE elements are now visible in the backend [#262](https://github.com/Gernott/mask/pull/262)
- declared strict_types in all classes for better code quality
- replaced deprecated composer option "replace" with extra/extension-key
- moved the mask backend module to the bottom of the admin tools

### Removed
- Support for TYPO3 v9 LTS was dropped. Use Mask v4.x.x for TYPO3 v9 LTS

### Fixed
- Sort inline fields recursively to output correct order of fields in editor [#267](https://github.com/Gernott/mask/pull/267)
- Added softref-config to rte fields [#266](https://github.com/Gernott/mask/pull/266)
- Fixed TCA default value of field parentid for Inline-Tables [#249](https://github.com/Gernott/mask/pull/249)
- Fixed the path resolution in backend preview images


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
