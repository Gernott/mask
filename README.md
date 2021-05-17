![Page and Content masks for TYPO3](Documentation/Images/mask_banner.jpg)

[![Build Status](https://travis-ci.com/Gernott/mask.svg?branch=master)](https://travis-ci.com/Gernott/mask)

# Mask

Create your own content elements and page templates. Easy to use, even without programming skills, because of the
comfortable drag and drop user interface. All content is stored in structured database tables.

## What does it do?

Mask is a TYPO3 extension for creating content elements and extending page templates. It’s possible to add new fields
to any element. Fields can have several types, for example: text, file, relations, rich text, ...

## Quickstart

Download Mask with composer by running the command `composer require mask/mask` or install via extension manager.
As soon as you activate the extension, Mask will create a dummy extension `mask_project`. To prevent this you can
change the settings before you activate Mask. The easiest way to change the paths is to add this to your
`AdditionalConfiguration.php`:

```
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mask'] = [
    'backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Templates/',
    'backendlayout_pids' => '0,1',
    'content' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Templates/',
    'json' => 'EXT:sitepackage/Configuration/Mask/mask.json',
    'layouts' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Layouts/',
    'layouts_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Layouts/',
    'partials' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Partials/',
    'partials_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Partials/',
    'preview' => 'EXT:sitepackage/Resources/Public/Mask/',
];
```

Adjust the paths to your needs and activate the extension. When you visit the Mask backend, you will have the option to
create all missing files and folders defined here. This is also great to have in version control so others will have
this already set up when checking out.

Mask requires `fluid_styled_content` so go to your static includes in the template module and include it there.
**After** that also include the Mask static TypoScript.

That's it. Now you can start creating your own content elements!

## Mask versions

| Mask Version | TYPO3 Version | Release Date | Status              | More Info |
|--------------|---------------|--------------|---------------------|-----------|
| v7           | v10, v11.2    | 2021-05-12   | regular maintenance | [VueJS based Mask Backend](https://docs.typo3.org/p/mask/mask/master/en-us/ChangeLog/7.0/Index.html)|
| v6           | v10, v11.1    | 2020-10-08   | priority bugfixes   | [Palettes, Groups and more](https://docs.typo3.org/p/mask/mask/master/en-us/ChangeLog/6.0/Index.html)|
| v5           | v10           | 2020-04-18   | discontinued        | Please update to v7                                                                            |
| v4           | v9            | 2018-10-04   | occasional bugfixes |                                                                                                |
| v3           | v8            | 2017-05-23   | discontinued        |                                                                                                |
| v2           | v7            | 2016-05-10   | discontinued        |                                                                                                |
| v1           | v6, v7        | 2015-08-12   | discontinued        |                                                                                                |

## Advantages of Mask

* Mask stores the content in columns in database tables – not as XML (Flexform)
* Mask reuses existing database fields to conserve the database
* Mask works only with existing features of the TYPO3 core: backend layouts, Fluid, TypoScript
* Mask allows repeating content with IRRE technology
* Mask supports multi-language projects
* Mask supports workspaces and versioning
* The Mask backend is a single page application based on VueJS for even more comfort

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
