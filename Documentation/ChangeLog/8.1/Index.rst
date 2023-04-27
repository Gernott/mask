.. include:: ../../Includes.txt

================
Mask version 8.1
================

It is March 2023 and TYPO3 v12 is close to feature freeze. The right time to
finish migrating the last deprecations and implementing new features. Aaand it's
done. Enjoy Mask v8.1 delivered right on time.

Deprecation free
================

TYPO3 v12 introduced A LOT of new TCA features, but at the same time an equal
amount of deprecations. These are now tackled and your TYPO3 instance won't
flood your deprecation log anymore.

New field types
===============

There are some new types due to newly introduced TCA types.

Email
-----

.. figure:: ../../Images/FieldTypes/Email.svg
   :alt: Email
   :class: float-left
   :width: 64px

This replaces the old way of using the `String` field together with eval
`email`.

Folder
------

.. figure:: ../../Images/FieldTypes/Folder.svg
   :alt: Folder
   :class: float-left
   :width: 64px

This replaces the old way of using the `Group` field together with internal_type
`folder`.

New TCA features
================

*  :ref:`elementBrowserEntryPoints (group, folder) <t3tca:columns-group-properties-elementBrowserEntryPoints>`
*  :ref:`min (string, text) <t3tca:columns-input-properties-min>`

New PSR-14 MaskAllowedFieldsEvent
=================================

The newly introduced :php:`\MASK\Mask\Event\MaskAllowedFieldsEvent` allows to
add custom existing fields to be available in the Mask module. Refer to this
:ref:`guide <use-existing-tca-fields-guide>` on how to use it.

Thanks to Thomas Scholze (@twaurisch)!

ES6 modules for TYPO3 v12
=========================

The JavaScript of Mask is now bundled and migrated to ES6 modules, which is a
new feature in TYPO3 v12, instead of using RequireJS. In TYPO3 v11 another AMD
bundle is generated for compatibility.

Thanks to Benjamin Franzke (@bnf)!

Upgrade Wizard necessary
========================

The persistence / matching of type Content fields has changed. It is necessary
to run the upgrade wizard with the title `Migrate Mask Content fields`.

Command with TYPO3 CLI:

.. code-block:: shell

   // composer mode
   vendor/bin/typo3 upgrade:run migrateContentFields

   // classic mode
   typo3/sysext/core/bin/typo3 upgrade:run migrateContentFields

Sponsoring
==========

Do you like the early adoption of Mask? You can now sponsor my (Nikita Hovratov)
free contribution to this project on `GitHub <https://github.com/sponsors/nhovratov>`__.
