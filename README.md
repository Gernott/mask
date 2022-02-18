![Page and Content masks for TYPO3](Resources/Public/Images/banner.jpg)

[![release](https://img.shields.io/github/v/release/gernott/mask?sort=semver)](https://github.com/Gernott/mask)
[![TYPO3](https://img.shields.io/badge/TYPO3-v11-ff8700)](https://typo3.org/)
[![TYPO3](https://img.shields.io/badge/TYPO3-v10-ff8700)](https://typo3.org/)
[![Tests](https://img.shields.io/github/workflow/status/Gernott/mask/Unit%20Tests?label=Tests)](https://github.com/Gernott/mask/actions/workflows/tests.yaml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

[![Verified TYPO3 Extension](Resources/Public/Images/verified.svg) Verified TYPO3 Extension](https://typo3.com/typo3-cms/verified-extensions-integrations-for-typo3/extensions/mask-create-custom-content-elements-in-typo3)

[:blue_book: Official Documentation](https://docs.typo3.org/p/mask/mask/main/en-us/Index.html)

# Mask

Mask is a content element builder that generates TypoScript, TSconfig and TCA on
the fly. You can build your own custom content elements in a user-friendly
backend module via drag and drop. Your configuration is stored in json files,
which can be shared across projects.

Mask provides different field types, that you can use to
customize your content elements. Depending on the type there are different
options available. Field types are grouped roughly into input, repeating and
structural fields. With these given tools, you can cover almost all typical
requirements for your projects. And if not, Mask can be extended
via TCA overrides or DataProcessors.

## Quickstart

1. Add Mask as a dependency in your `ext_emconf.php` and/or `composer.json` of your sitepackage.
2. Download Mask with composer by running the command `composer require mask/mask` or install via extension manager.
3. Activate Mask in the extension manager (not needed in TYPO3 v11 composer mode).
4. Mask requires `fluid_styled_content` so go to your static includes in the template module and include it there.
5. Also include the Mask static TypoScript.
6. Navigate to the Mask module and enter your sitepackage extension key for auto-configuration (your extension must be loaded!).
7. Start creating your own content elements!

## Manual configuration

If you don't want to use the default folder structure created by the auto-configuration, you can adjust every path in
the extension configuration of Mask.

## Advantages of Mask

* Mask stores the content in columns in database tables – not as XML (Flexform)
* Mask reuses existing database fields to conserve the database
* Mask works only with existing features of the TYPO3 core: backend layouts, Fluid, TypoScript
* Silent TCA migrations allow for easy TYPO3 upgrades to new major versions
* Mask allows repeating content with IRRE technology
* Mask supports multi-language projects
* Mask supports workspaces and versioning
* The Mask backend is a single page application based on VueJS for even more comfort

Read a detailed explanation for each advantage and why to use Mask over other alternatives in the [official documentation](https://docs.typo3.org/p/mask/mask/main/en-us/Introduction/Index.html).

## Mask versions

| Mask Version | TYPO3 Version | Release Date | Status              | More Info |
|--------------|---------------|--------------|---------------------|-----------|
| v7.1         | v10, v11      | 2021-12-14   | regular maintenance | [JsonSplitLoader, new API](https://docs.typo3.org/p/mask/mask/main/en-us/ChangeLog/7.1/Index.html) |
| v7.0         | v10, v11      | 2021-05-12   | discontinued        | [VueJS based Mask Backend](https://docs.typo3.org/p/mask/mask/master/en-us/ChangeLog/7.0/Index.html)|
| v6           | v10, v11.1    | 2020-10-08   | critical bugfixes   | [Palettes, Groups and more](https://docs.typo3.org/p/mask/mask/master/en-us/ChangeLog/6.0/Index.html)|
| v5           | v10           | 2020-04-18   | discontinued        | Please update to v7                                                                            |
| v4           | v9            | 2018-10-04   | discontinued        |                                                                                                |
| v3           | v8            | 2017-05-23   | discontinued        |                                                                                                |
| v2           | v7            | 2016-05-10   | discontinued        |                                                                                                |
| v1           | v6, v7        | 2015-08-12   | discontinued        |                                                                                                |

## Need help?

* Read how to install, configure and use mask in the [official documentation](https://docs.typo3.org/p/mask/mask/master/en-us/)
* Join the "#ext-mask" channel on [TYPO3 Slack](https://typo3.org/community/meet/chat-slack) and ask the mask community.
* [Visit our website](https://mask.webprofil.at) to find more information about mask

## Found a bug?

* First check out the master branch and verify that the issue is not yet solved
* Have a look at the existing [issues](https://github.com/gernott/mask/issues/), to prevent duplicates
* If not found, report the bug in our [issue tracker](https://github.com/gernott/mask/issues/new/)

## Like a new feature?

* Have a look at our [wishlist](https://mask.webprofil.at/featurelist/overview/)
* If your idea is not listed here, get in [contact](https://mask.webprofil.at/imprint/) with us
* If you want to sponsor a feature, get in [contact](https://mask.webprofil.at/imprint/) with us
* If you want to develop a feature, get in [contact](https://mask.webprofil.at/imprint/) to plan a strategy
