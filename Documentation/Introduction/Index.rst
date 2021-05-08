.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

.. _what-it-does:

What does it do?
================

Mask is a TYPO3-extension for creating content elements and extending page templates. It is possible to add new fields
to any backend layout and create completely customized content elements without writing any line of code. Fields can
be one of several types. For example: text, file, select, checkbox, ...

Advantages of Mask
==================

* Mask stores content seperately in columns of database tables - **not** as XML (Flexform)
* Mask reuses existing database fields of the `tt_content` table to conserve the database
* Mask uses already existing features of the TYPO3 core: Backend Layouts, Fluid, TypoScript, TCA
* Mask allows repeating content and nesting content elements with IRRE-technology
* Mask supports multi-language projects
* Mask supports workspaces and versioning
* Mask supports frontend user restrictions
* The Mask backend is a single page application based on VueJS for even more comfort

Who is Mask for
===============

Mask is ideal for TYPO3 integrators who have experience with the Fluid templating engine. Even without knowing any PHP,
it is possible for them to create complex content elements. But also developers who are able to create everything
manually profit from Mask: Creating content elements is extremely fast and everything can be extended with TCA
overrides. If that's not enough, the extension `mask_export <https://github.com/IchHabRecht/mask_export>`__ enables you
to export all the generated TCA and TypoScript into an own static extension.

.. _screenshots:

Screenshots
===========

.. figure:: ../Images/IntroductionManual/BackendScreenshot.png
   :alt: Backend Screenshot

   The Backend-Module looks like this, after creating the first contentelement.
