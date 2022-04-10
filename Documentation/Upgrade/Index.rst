.. include:: ../Includes.txt

.. _upgrade:

=============
Upgrade Guide
=============

Mask tries to minimize breaking changes between each version. But sometimes small changes have to be done, which
require actions to be performed.

From v7.0.x or lower
====================

To further streamline the Mask API, the `options` key has to be removed from the
`elements` section in the json file. It was used to resolve field types, which
didn't have enough information by their own (e.g. type richtext).

There is a **compatibility layer** in Mask v7 which will trigger a deprecation
warning, if the field was migrated. This fallback will be removed in Mask v8.

.. note::

   This only affects installations, which were originally created on TYPO3 v7 / Mask v2 (or lower).

Either use the Upgrade Wizard manually and run "Update Mask JSON file (RTE options)"
or use cli commands. The Upgrade Wizard will only show up, if necessary.

Command with TYPO3 CLI:

::

   ./typo3/sysext/core/bin/typo3 upgrade:run moveRteOptions

Or with typo3_console:

::

   ./typo3cms upgrade:run moveRteOptions

.. _upgrade-from-6:

From v6 or lower
================

When upgrading to v7 you might experience an error in the Mask module:
`The requested resource "/module/tools/MaskMask" was not found.`. This can be
fixed by clearing the browser cache (CTRL + F5). The error is caused by a
changed way of how the module is registered.

From v5 or lower
================

richtextConfiguration has to be removed
---------------------------------------

Because of a `changed loading order <https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/10.2/Important-88655-ChangedLoadingOrderOfRTEConfiguration.html>`__
of the RTE configuration it is required to remove the `richtextConfiguration` entry from `mask.json` or else all of your
RTE fields will have the TYPO3 default RTE config set.

.. versionadded:: 6.2.0

Mask provides the UpgradeWizard `RemoveRichtextConfiguration.php` to remove all entries at once.

.. important::
   This upgrade wizard is only available in Mask v6. The reason is that since v7 the richtext configuration can be
   edited in mask directly. If you are planning to update to v7, first update to v6, run the upgrade wizard and then
   continue updating to v7.

Command with TYPO3 CLI:

::

   ./typo3/sysext/core/bin/typo3 upgrade:run removeRichtextConfiguration

Or with typo3_console:

::

   ./typo3cms upgrade:run removeRichtextConfiguration

Another way is to go to the **Upgrade** module and open the **Upgrade Wizard**. There you will find `Remove "richtextConfiguration" in mask.json`.
Click on `Execute` to start the upgrade.

From v3 or lower
================

New template name format
------------------------

Since version v4 Mask is based on the FLUIDTEMPLATE content object. This requires all template files to be named
in UpperCamelCase.

Before: `my_element.html`

After: `MyElement.html`

.. versionadded:: 6.2.0

If you have a lot of templates you can rename them with the UpgradeWizard `ConvertTemplatesToUppercase.php`.

Command with TYPO3 CLI:

::

   ./typo3/sysext/core/bin/typo3 upgrade:run convertTemplatesToUppercase

Or with typo3_console:

::

   ./typo3cms upgrade:run convertTemplatesToUppercase

Another way is to go to the **Upgrade** module and open the **Upgrade Wizard**. There you will find `Convert Mask templates to uppercase.`.
Click on `Execute` to start the upgrade.

From 3.1.0 or lower
===================

New filename format for preview images
--------------------------------------

Remove the `ce_` prefix from all content element preview images. Example: from `ce_key.png` to `key.png`


From TYPO3 v7 / Mask v2 or lower
================================

.. versionadded:: 7.1

TYPO3 v8 introduced a new language field :sql:`l10n_source`. While it provided
an upgrade wizard for the :sql:`tt_content` table, other tables weren't updated.
Mask now provides an UpgradeWizard, which updates its custom tables. Custom
tables are created, if you use the :ref:`inline <fields-inline>` field of Mask.

Either go to the Upgrade module and check for updates, or use it from the
command line.

Command with TYPO3 CLI:

.. code-block:: shell

   // composer mode
   vendor/bin/typo3 upgrade:run fillTranslationSourceField

   // classic mode
   typo3/sysext/core/bin/typo3 upgrade:run fillTranslationSourceField
