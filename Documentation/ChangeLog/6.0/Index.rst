.. include:: ../../Includes.txt

==============
Mask version 6
==============

Mask had a very long way. With the first alpha version released on the
12th August 2015 one of the most popular additions to TYPO3 entered the TER.
Back then the current LTS version of TYPO3 was 6.2 and now, 4 versions later,
Mask still supports the latest LTS 10.4 thanks to sponsors and volunteers.
The early idea to stay close to the core payed off big time! Updates with mask
are easy peasy and the mask.json can be used across multiple versions.

Nevertheless, a bit of dust has gathered over the years. Many TCA options have
changed, lots of old issues were never resolved and some frequently requested
features were never implemented. This changes with Mask version 6!

Features
========

.. toctree::
   :titlesonly:
   :maxdepth: 1

   Palettes
   Group
   RelationsResolved
   Timestamp
   CheckboxUI
   SelectIcons
   FieldSelection
   ImageOverlay
   LanguageSync
   NewDefaults
   HTMLGeneration

Bugfixes
========

This is a list of all bugfixes since version 5: ::

   2020-10-05 [BUGFIX] Display bodytext core field always as richtext (Commit d65e2e0 by Nikita Hovratov)
   2020-10-02 [BUGFIX] Fix missing labels for pages fields (Commit 24fc75e by Nikita Hovratov)
   2020-09-28 [BUGFIX] Show "includePrefixOption" only for text fields (Commit 824e2ab by Nikita Hovratov)
   2020-09-28 [BUGFIX] Allow removing field and use same key as before in element (Commit 62f8491 by Nikita Hovratov)
   2020-09-28 [BUGFIX] Unset some update suggestion keys that cause errors (Commit 023f813 by Nikita Hovratov)
   2020-09-28 [BUGFIX] Remove new lines of description in element wizard (Commit ef40fcf by Nikita Hovratov)
   2020-09-28 [BUGFIX] Fix getLabel if core field is already in palette (Commit e62e042 by Nikita Hovratov)
   2020-09-25 [BUGFIX] Update label and key sync after focusout event (Commit 34a4780 by Nikita Hovratov)
   2020-09-25 [BUGFIX] Fix sorting of repeating elements in workspace (Commit 6963b0c by Nikita Hovratov)
   2020-09-25 [!!!][BUGFIX] Always sort inline fields by order when fetching them (Commit 7b69cc0 by Nikita Hovratov)
   2020-09-25 [BUGFIX] Restrict FluidTemplate data override to pages (Commit b57cefb by Nikita Hovratov)
   2020-09-25 [BUGFIX] Remove wrong extra comma in unset argument list (Commit 4cf9a8e by Nikita Hovratov)
   2020-09-25 [BUGFIX] Add workspace restriction for inline tables (Commit abc3be3 by Nikita Hovratov)
   2020-09-24 [BUGFIX] Do not allow having Content fields with same key (Commit 4072731 by Nikita Hovratov)
   2020-09-24 [BUGFIX] Fix getFieldType for mask inline tables (Commit 1d7b4f6 by Nikita Hovratov)
   2020-09-24 [BUGFIX] Fix css on chrome (Commit 4ca640c by Nikita Hovratov)
   2020-09-23 [BUGFIX] Fix getFieldType method if elementKey is set (Commit 497ed16 by Nikita Hovratov)
   2020-09-23 [BUGFIX] Fix html code generation for palette fields (#355) (Commit 26741b5 by Nikita Hovratov)
   2020-09-23 [BUGFIX] Only show existing fields that are not used otherwise (Commit d56672d by Nikita Hovratov)
   2020-09-22 [BUGFIX] Check if field exists in tca before merging (Commit 9860df9 by Nikita Hovratov)
   2020-09-22 [BUGFIX] Fix element key check (Commit 59d084e by Nikita Hovratov)
   2020-09-22 [BUGFIX] Disallow palettes to have the same key as inline fields (Commit bd2e3da by Nikita Hovratov)
   2020-09-21 [BUGFIX] Fix sorting when new element is added (Commit 005128c by Nikita Hovratov)
   2020-09-14 [BUGFIX] Check if inline fields have children before inserting (Commit f181e65 by Nikita Hovratov)
   2020-09-14 [BUGFIX] Fix function call (Commit 8241a98 by Nikita Hovratov)
   2020-09-13 [BUGFIX] Fix styling for mobile view (Commit c8a14c5 by Nikita Hovratov)
   2020-07-18 [BUGFIX] Show error messages as red notifications (Commit 00b1166 by Nikita Hovratov)
   2020-07-17 [BUGFIX] Correct field name in index and show database update result (Commit ae450f0 by Nikita Hovratov)
   2020-07-17 [BUGFIX] Prevent errors of non existing tables (Commit 82903ce by Nikita Hovratov)

This list has been created by using `git log v5.1.3..v6.0.0 --abbrev-commit --grep='BUGFIX'  --pretty='%ad %s (Commit %h by %an)' --date=short`.

Technical improvements
======================

- Symfony DI
- Usage of the DefaultTcaSchema to automatically enrich database fields
- Many tests added
- Massive code cleanup (php-cs-fixer, rector)
- Refactoring of many methods
- JavaScript refactored to use Require JS

Breaking changes
================

- Removed AbstractCodeGenerator
- Removed JsonCodeGenerator
- The method "getFormType" has been extracted from FieldHelper to StorageRepository
- The method "sortInlineFieldsByOrder" has been extracted from WizardController to StorageRepository.
- Removed temp.mask.page TypoScript snippet
