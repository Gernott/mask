.. include:: ../../Includes.txt

================
Mask version 8.0
================

The new TYPO3 version 12.0 has been released in October and there are already
some extensions compatible (32 as of time of writing). This is nice to see and
Mask jumps on that train of early adopters, as well! As Mask is a verified
extension our minimum goal is to deliver a compatible version one month before
the LTS release. This version (v8.1) will now be the deprecation free and all
new features adopted version.

Breaking changes
================

*  Dropped support for TYPO3 v10.
*  It is now required that the configured extension paths are pointing to a loaded extension.
*  In **composer mode**, template paths need to have the `EXT:` prefix. Paths with `typo3conf` won't work any longer.

The following methods have been removed:

*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->loadField()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->loadInlineFields()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->loadElement()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->getFormType()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->getElementsWhichUseField()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->findFirstNonEmptyLabel()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->getLabel()`
*  :php:`\MASK\Mask\Domain\Repository\StorageRepository->getFieldType()`

The following classes have been removed:

*  :php:`MASK\Mask\Helper\FieldHelper`
*  :php:`MASK\Mask\ViewHelpers\EditLinkViewHelper`
*  :php:`MASK\Mask\ViewHelpers\ContentViewHelper`

Migration
=========

All removed methods of :php:`StorageRepository` and :php:`FieldHelper` can be
found in :php:`MASK\Mask\Definition\TableDefinitionCollection`. This service can
be injected via dependency injection.

Instead of using the Mask ViewHelpers you can use :html:`be:link.editRecord` and
:html:`f:cObject` with the :typoscript:`lib.tx_mask.content` TypoScript snippet.

Important
=========

TYPO3 has removed FontAwesome from the core, which Mask relies heavily on.
There is a compatibility extension though `friendsoftypo3/fontawesome-provider`,
which will be automatically installed via composer. The TER version (as of time
of writing) hasn't been published yet. Keep watch on the key `fontawesome_provider`
in the TER.

Issue reporting
===============

Please report any problems, that you might encounter. This version is not yet
deprecation-free, so don't open issues for that. These are already listed on the
project page. New TCA features will be adopted later as well.

Sponsoring
==========

Do you like the early adoption of Mask? You can now sponsor my (Nikita Hovratov)
free contribution to this project on `GitHub <https://github.com/sponsors/nhovratov>`__.
