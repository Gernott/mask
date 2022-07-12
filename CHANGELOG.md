# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [7.2.7] - 2021-07-12

### Fixed
- Do not show core fields as shared in palettes
- Handle ResourceDoesNotExistException in PreviewIconResolver (Thanks to Markus Klein) [#501](https://github.com/Gernott/mask/issues/501)
- Do not show migration notice for empty Mask definitions [#502](https://github.com/Gernott/mask/issues/502)

### Fixed
- Followup bugfix. Resolves error when creating the first element.

## [7.2.6] - 2021-06-23

### Fixed
- Followup bugfix. Resolves error when creating the first element.

## [7.2.5] - 2021-06-22

### Fixed
- Persisting of shared array-like fields (e.g. items) [#495](https://github.com/Gernott/mask/issues/495)

## [7.2.4] - 2021-06-17

### Fixed
- Backend layout preview icon path in TYPO3 v10

## [7.2.3] - 2021-06-13

### Fixed
- Removed unused added sys_file_reference database columns of Mask

### Changed
- Updated troubleshooting guide

## [7.2.2] - 2021-05-30

### Fixed
- Compatibility TYPO3 v10 relation fetching [#490](https://github.com/Gernott/mask/issues/490)

## [7.2.1] - 2021-05-26

### Fixed
- Relation records like category, group and select are now correctly translated [#489](https://github.com/Gernott/mask/issues/489)
- Content Element search now works on the element key, too.

## [7.2.0] - 2021-05-25

The new v7.2 version comes with a lot of new features!

Read the [release article](https://docs.typo3.org/p/mask/mask/main/en-us/ChangeLog/7.2/Index.html) to learn more about
the new version.

### Added
- Category field
- Slug field
- Media field
- Better UX
- Migration Manager
- New TYPO3 v11 features
- much, much more ...

## [7.1.31] - 2022-05-20

### Fixed
- Exception in Upgrade Wizard if path to templates is empty (Thanks to Georg Ringer)
- Default value for type select "selectIcons"

## [7.1.30] - 2022-05-08

### Fixed
- Error in JsonSplitLoader when path to backend layouts folder is not defined

### Added
- Index for tt_content CType

## [7.1.29] - 2022-04-30

### Fixed
- Remove orphan inline fields
- Remove field key selector if dragged into inline field
- Prevent (new) core fields to be dragged into inline field

## [7.1.28] - 2022-04-20

### Fixed
- Compatibility fix for very old Mask file fields

## [7.1.27] - 2022-04-15

### Fixed
- Set TableDefinitionCollection to shared: false in DI

## [7.1.26] - 2022-04-14

### Fixed
- Fix side effects of modifying the TableDefinitionCollection
- Fix MoveRteOptions Update Wizard adding TCA for core fields

## [7.1.25] - 2022-04-13

### Fixed
- The bodytext field of Mask elements can now be searched [#484](https://github.com/Gernott/mask/issues/484)

## [7.1.24] - 2022-04-10

### Fixed
- Some warnings in the console

### Added
- UpgradeWizard for filling l10_source. Relevant, if you upgraded from TYPO3 v7 or lower.

## [7.1.23] - 2022-03-22

### Fixed
- Empty "backend" setting no longer causing frontend templates to load
- Select item values now trimmed [#482](https://github.com/Gernott/mask/issues/482)

### Added
-  \#StandWithUkraine

## [7.1.22] - 2022-03-17

### Fixed
- Fix FolderDoesNotExistException (thanks Martin Lipp)

## [7.1.21] - 2022-03-17

### Fixed
- Compatibility with typo3/cms-composer-installers v4 [#477](https://github.com/Gernott/mask/issues/477), [#478](https://github.com/Gernott/mask/issues/478)

## [7.1.20] - 2022-03-10

### Fixed
- Catch exception for orphan table definitions

## [7.1.19] - 2022-02-18

### Fixed
- Richtext fields are now searchable in LiveSearch (Thanks Nitori) [#471](https://github.com/Gernott/mask/pull/471)
- Fixed search fields generation in general
- Error prevented, when creating empty inline field
- Error prevented, when creating empty palette

## [7.1.18] - 2022-02-11

### Fixed
- The auto-configuration module now generates the backend layout folder [#469](https://github.com/Gernott/mask/issues/469)
- Fixed warning in PHP 8 due to generation of invalid TCA [#470](https://github.com/Gernott/mask/issues/470)

## [7.1.17] - 2022-01-27

### Fixed
- Inline items disabled in LIVE workspace are now visible in custom workspace. [#468](https://github.com/Gernott/mask/issues/468)
- Disabled records are no longer loaded in backend preview.
- Page inline fields are now translated correctly.
- Avoid deprecated method call to fetchAll.
- Auto-Configuration won't remove config for other loader anymore.

## [7.1.16] - 2022-01-20

### Fixed
- Prevent ConvertTemplates wizard from renaming every file [#467](https://github.com/Gernott/mask/issues/467)

## [7.1.15] - 2022-01-17

### Fixed
- Fix preview mode
- Prevent empty TCA error for extension fields
- Fix backend layout resolving [#466](https://github.com/Gernott/mask/issues/466)

## [7.1.14] - 2022-01-04

### Fixed
- JsonSplitLoader resolves now nested inline fields correctly (Thanks to Can Karadağ)
- Fetch workspace ID from context API (thanks to Susanne Moog)
- php-cs-fixer tests in CI

## [7.1.13] - 2021-12-25

### Fixed
- Config cleaner fixes

### Changed
- Improved documentation introduction

## [7.1.12] - 2021-12-22

### Fixed
- HTML example generation works again on first save

## [7.1.11] - 2021-12-22

### Fixed
- Labels and descriptions are visible again in inline fields

## [7.1.10] - 2021-12-22

### Fixed
- Followup fix: Call to a member function isRenderable() on null

## [7.1.9] - 2021-12-22

### Fixed
- Preserve order of elements

## [7.1.8] - 2021-12-22

### Fixed
- Order of inline fields
- Compatibility layer for missing descriptions

## [7.1.7] - 2021-12-20

### Fixed
- TypeError when facing orphan rte options [#458](https://github.com/Gernott/mask/issues/458)

## [7.1.6] - 2021-12-19

### Fixed
- Core fields of old mask json files do not throw exceptions anymore [#457](https://github.com/Gernott/mask/issues/457)

## [7.1.5] - 2021-12-18

### Fixed
- Add compatibility layer for breaking change [Read more](https://docs.typo3.org/p/mask/mask/main/en-us/Upgrade/Index.html#from-v7-0-x-or-lower)
- Default values for type content
- Default TCA output values now always applied (Compatibility fix) [Read more](https://github.com/Gernott/mask/commit/f81dec13f4883155385b805985e8d90ca7e9be68)
- Add compatibility layer for old link TCA format (wizards)

### Added
- Unknown/outdated configuration now removed, when persisting mask json

## [7.1.4] - 2021-12-18

### Fixed
- Error with file fields (TCA attribute empty)
- Another doctrine/dbal compatibility
- Error in database update schema, when field does not exist

## [7.1.3] - 2021-12-15

### Fixed
- Upgrade Wizard now correctly updates old rte format
- PHP 8 warnings
- Type Safety for cTypes

## [7.1.2] - 2021-12-14

### Fixed
Compatibility PHP 8.1 [#455](https://github.com/Gernott/mask/issues/455)

## [7.1.1] - 2021-12-14

### Fixed
Compatibility with doctrine/dbal 2.10 [#454](https://github.com/Gernott/mask/issues/454)

## [7.1.0] - 2021-12-14

This is a big release (despite only the minor version has changed)! Huge cleanup
of the code, which enabled cool new features.

Read the [release article](https://docs.typo3.org/p/mask/mask/main/en-us/ChangeLog/7.1/Index.html) to learn more about
the new version.

### Added
- JsonSplitLoader (split the Mask configuration into multiple json files)
- API for retrieving the Mask configuration
- Colorpicker field
- Description Overrides
- Auto-Configuration to get started faster with Mask

## [7.0.31] - 2021-12-08

### Fixed
- Backend layout rootline lookup (Thanks to Tyrone Rogers)
- Deprecations

## [7.0.30] - 2021-11-27

### Fixed
- Fix backend module not loading, if be-layout icon path is wrong.

## [7.0.29] - 2021-11-18

### Fixed
- Internal value of cType is not shown twice [#444](https://github.com/Gernott/mask/issues/444)

### Added
- Compatibility b13/container [#449](https://github.com/Gernott/mask/issues/449)

## [7.0.28] - 2021-11-09

### Fixed
- A lot of fixes regarding validation in the Mask builder.
- PHP 8 warnings [#440](https://github.com/Gernott/mask/issues/440)

## [7.0.27] - 2021-11-03

### Fixed
- File References resolved in custom tt_content type [#439](https://github.com/Gernott/mask/issues/439)

## [7.0.26] - 2021-11-02

### Fixed
- Do not check for TCA internal type `db` anymore (TYPO3 v11).
- Other bugfixes

## [7.0.25] - 2021-10-26

### Fixed
- PHP 8.0 warnings [#438](https://github.com/Gernott/mask/issues/438)
- The new TCA type `category` is detected as type select
- Fix deprecation by using the new TCA type `language` for `sys_language_uid`

## [7.0.24] - 2021-10-20

### Fixed
- Error when overriding core labels [#436](https://github.com/Gernott/mask/issues/436)

## [7.0.23] - 2021-10-20

### Fixed
- Another PHP 8.0 warning

## [7.0.22] - 2021-10-12

### Fixed
- Fixed errors regarding backend layouts

## [7.0.21] - 2021-10-05

### Added
- TYPO3 v11 LTS support

## [7.0.20] - 2021-09-28

### Fixed
- TCA overrides of mask fields no longer cause errors

## [7.0.19] - 2021-09-07

### Added
- Backwards compatibility for old link allowedExtensions TCA

## [7.0.18] - 2021-09-07

### Added
- TYPO3 11.4 support

### Fixed
- Some PHP 8 undefined array key errors

## [7.0.17] - 2021-09-03

### Fixed
- Some PHP 8 undefined array key errors

## [7.0.16] - 2021-08-09

### Added
- PHP 8.0 support

### Fixed
- Localized strings translated in list view

## [7.0.15] - 2021-07-18

### Fixed
- Localized strings for labels work again
- Fixed JS-error when moving fields from outside into palette

## [7.0.14] - 2021-07-13

### Added
- Compatibility TYPO3 v11.3

## [7.0.13] - 2021-06-30

### Fixed
- multi-usage indicator behaviour
- Grouping of Mask fields in CType select

### Removed
- TCA option showRemovedLocalizationRecords

### Changed
- Database index for content fields

## [7.0.12] - 2021-06-25

### Fixed
- Missing rte_ckeditor extension does no longer break Mask [#417](https://github.com/Gernott/mask/issues/417)

### Changed
- Varchar size for link fields changed back to 255 [Read more](https://docs.typo3.org/p/mask/mask/main/en-us/Troubleshooting/Index.html#on-save-error-row-size-too-large)

### Added
- Troubleshooting guide for `row size too large` error.

## [7.0.11] - 2021-06-22

### Fixed
- Page templates do not create templates on save
- Inline fields never shown as multiuse

### Added
- Guides for crop variants and rte config

## [7.0.10] - 2021-06-16

### Fixed
- Corrected behaviour of backend_layout_next_level
- Css fixes

## [7.0.9] - 2021-06-14

### Fixed
- Prevent error when saving empty element

## [7.0.8] - 2021-06-14

### Fixed
- Non-existing tca definitions in mask.json are now ignored to prevent errors

## [7.0.7] - 2021-06-11

### Fixed
- JSON label structure for fields in palettes in inline fields persisted correctly (new save required!)

## [7.0.6] - 2021-06-10

### Fixed
- l10n_mode loading

## [7.0.5] - 2021-06-01

### Fixed
- Loading existing field does not override sql definition anymore
- Field labels now also loaded when field is in palette
- Some edgecase validation behaviour fixed

### Added
- Show label of mask fields in existing key select
- Improved sticky styling for sidebar

## [7.0.4] - 2021-05-31

### Fixed
- Fix file allowedFileExtensions not loading

### Changed
- Changed mask.json default path

## [7.0.3] - 2021-05-28

### Fixed
- Core field bodytext now generates html ViewHelper

### Changed
- Improved CSS Styling

## [7.0.2] - 2021-05-20

### Fixed
- Hide existing field select in palette of inline fields

## [7.0.1] - 2021-05-20

### Fixed
- Checkboxes can be clicked again
- Css fixes

### Added
- Travis CI test execution
- Improved documentation

## [7.0.0] - 2021-05-12

This huge release contains a completetely rewritten Mask Backend!
Check out the [release article](https://docs.typo3.org/p/mask/mask/main/en-us/ChangeLog/7.0/Index.html) for in depth information.

### Changed
- Completey new Mask Backend based on VueJS
- New responsive UI/UX
- Updated documentation

### Added
- New Upgrade Guide / Tips and tricks
- Compatibility TYPO3 v11.2

## [6.6.7] - 2021-05-05

### Added
- Frontend User Group restrictions are now taken into account for inline elements [#404](https://github.com/Gernott/mask/issues/404)

## [6.6.6] - 2021-05-05

### Fixed
- Some checkboxes do no longer reset to default value [#402](https://github.com/Gernott/mask/issues/402)

## [6.6.5] - 2021-04-20

### Fixed
- Fix blindLinkOptions not editable when two fields have the same key [#397](https://github.com/Gernott/mask/issues/397)

## [6.6.4] - 2021-04-17

### Fixed
- Fix error when mask key contains the string 'pages' and is placed in inline field [#400](https://github.com/Gernott/mask/issues/400)

## [6.6.3] - 2021-03-31

### Fixed
- Fix incorrect text field wrap inversion

## [6.6.2] - 2021-03-30

### Fixed
- Cast element key to string when resolving template file

## [6.6.1] - 2021-03-24

### Fixed
- Fix exception for editors when editing irre element [#392](https://github.com/gernott/mask/issues/392)

## [6.6.0] - 2021-03-15

### Added
- Support for TYPO3 v11.1.0

### Fixed
- Date formatting of timestamp field

## [6.5.2] - 2021-03-12

### Fixed
- Add responsive styling for notebook viewport widths [#391](https://github.com/gernott/mask/issues/391)

## [6.5.1] - 2021-03-02

### Fixed
- Fix InvalidEnumerationValueException in backend [#390](https://github.com/gernott/mask/issues/390)

## [6.5.0] - 2021-03-02

## Added
- TCA definition for parent field of fields of type content [#389](https://github.com/gernott/mask/issues/389)

### Fixed
- Infinite loop if irre field and containing content field have same key

## [6.4.6] - 2021-02-25

### Fixed
- Empty backend layout icons do not throw error [#388](https://github.com/gernott/mask/issues/388)

## [6.4.5] - 2021-02-23

### Fixed
- Backend layouts with icon identifier work now [#385](https://github.com/gernott/mask/issues/385)

## [6.4.4] - 2021-02-08

### Fixed
- Always add softref to RTE fields [#383](https://github.com/gernott/mask/issues/383)

## [6.4.3] - 2021-02-07

### Fixed
- Load correct cType list [#382](https://github.com/gernott/mask/issues/382)

## [6.4.2] - 2021-01-31

### Fixed
- Fix old to new date format conversion

## [6.4.1] - 2021-01-18

### Fixed
- Default field of float type converted to type number

## [6.4.0] - 2021-01-11

### Fixed
- Core icon paths [#379](https://github.com/Gernott/mask/issues/379)

### Changed
- Inline syntax for format viewhelper in html generation

## [6.3.0] - 2020-12-15

### Fixed
- Backend preview now shows changes from workspace [#377](https://github.com/Gernott/mask/issues/377)

### Added
- Crowdin translation

### [6.2.2] - 2020-10-27

### Fixed

- Documentation toctree rendered again

### [6.2.1] - 2020-10-27

### Fixed

- Fixed wrong path in ConvertTemplate Upgrade Wizard when using "EXT:"

### Changed

- Switched to new TYPO3 documentation server rendering.
- Improved documentation.

### [6.2.0] - 2020-10-23

### Fixed
- Fixed a JavaScript function not existing in older chrome browsers.
- Richtext fields now correctly identified as richtext and not text.

### Added
- Update Wizards to migrate mask.json and template names from older installations.

### [6.1.0] - 2020-10-21

### Fixed

- Tabs / palettes for page templates only override pages table now.
- page templates with numeric keys don't cause type errors anymore.
- Inline fields added to frontend data array if in palette.

### Added

- Enumeration for field types added and replaced across all code.

### [6.0.1] - 2020-10-06

### Fixed

- Labels of repeating fields are rendered again
- Inline fields added correctly again

### [6.0.0] - 2020-10-05

### Added
- Support for palettes and linebreaks
- TCA group element
- timestamp element

There should be no problem to upgrade from 5 to 6 in most cases.
For more details read the [release notes](https://github.com/gernott/mask/blob/main/Documentation/ChangeLog/6.0/Index.rst)

### [5.1.3] - 2020-09-02

### Fixed
- Fields in page templates are now shown, if backend layout has non numeric name. [#345](https://github.com/Gernott/mask/issues/345)
- Saving mask elements do not cause change in indexes when running db analyzer anymore.
- JQuery is minified again.

### [5.1.2] - 2020-07-21

### Added
- Option to disable phone links. [#336](https://github.com/Gernott/mask/pull/336)

### Changed
- Undone symfony DI change because of breaking changes

### Fixed
- Fields `editlock` and `fe_group` now generated on element save. [#332](https://github.com/nhovratov/mask/commit/fb31c3cc219f8517559318eae2dcc2d3e993bcf2)

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
