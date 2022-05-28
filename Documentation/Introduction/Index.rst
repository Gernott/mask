.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

Intro video
===========

.. youtube:: g1EGnF5idCA

.. _what-it-does:

What is Mask
============

Mask is a content element builder that generates TypoScript, TSconfig and TCA on
the fly. You can build your own custom content elements in a user-friendly
backend module via drag and drop. Your configuration is stored in json files,
which can be shared across projects.

Mask provides different :ref:`field types <fieldtypes>`, that you can use to
customize your content elements. Depending on the type there are different
options available. Field types are grouped roughly into input, repeating and
structural fields. With these given tools, you can cover almost all typical
requirements for your projects. And if not, Mask can be extended
via :ref:`TCA overrides <tcaoverride-guide>` or :ref:`DataProcessors <data-processor-guide>`.

Advantages of Mask
==================

   Mask stores content separately in columns of database tables - **not** as XML (Flexform)

Flexform is a technique to define sheets and TCA fields inside a specified XML
definition. It was originally invented back in the days for `TemplaVoilà! <https://extensions.typo3.org/extension/templavoila>`__.
Then it gained popularity in other areas like extension plugin configuration,
because it was easy to define a bunch of fields without worrying about SQL
column definitions (besides the one needed for the flexform itself).

The big disadvantage is, that flexform is a so called anonymous construct and
is not designed for complex relations. It disallows inline fields inside inline
fields for example. The other thing is the lack of type safety, which is usually
accomplished with sql column types and appropriate PHP class attributes.

   Mask reuses existing database fields of the :sql:`tt_content` table to conserve the database

Whenever you create a new field in Mask, a new column in the :sql:`tt_content`
table will be created. But you can also choose to pick an existing TYPO3 core
field or another field you have already created. While this is great, you need
to take care your table won't exceed the :ref:`maximum row size <row-size-too-large>`.

   Mask uses already existing features of the TYPO3 core: Backend Layouts, Fluid, TypoScript, TCA

This is arguably Mask's unique selling point. With Mask, we didn't invent
something completely new. TYPO3 already provides all necessary technology to
create custom content elements. Mask eases the setup for this and provides a
nice user interface to interact with these APIs without the need to program
anything yourself.

   Mask is easy to update and TCA migrations are done automatically

TYPO3 continuously deprecates and breaks stuff in new versions (which is necessary).
And although there are now tools like rector, which ease the upgrades to a factor
of 100x it is even easier with Mask. You usually don't need to do anything, but
to update Mask to a newer version. This is possible because Mask provides
migrations for older json definitions. Keep an eye on necessary :ref:`upgrade wizards <upgrade>`
though.

   Mask allows repeating content and nesting content elements with IRRE-technology (Inline-Relational-Record-Editing)

It is very easy to create repeating (inline) fields with Mask. You can visually
see, how your fields are nested in the builder module. An inline field is
nothing else than a new table in your database, for which a relation is created
to. There is no limit how much you are allowed to nest, but keep it to a sane
amount for the sake of performance.

   | Mask supports multi-language projects
   | Mask supports workspaces and versioning
   | Mask supports frontend user restrictions

As already mentioned, Mask only uses TYPO3 core principles. This includes of
course language, workspaces and any kind of restrictions.

   The Mask backend is a single page application based on VueJS for even more comfort

In v7.0 Mask received a :ref:`complete overhaul <mask-v7.0>` for the backend
module. Instead of the good old jQuery spaghetti code, Mask runs on a clean
VueJS application in the background. You will notice it, as there are no
reloads of the frame between switching views anymore.

Why Mask
========

Mask is not the only content element creation extension. There are also other
similar extensions around. In contrast to Mask, the most other utilize flexforms
extensively. The downsides of using flexform is already discussed above. It is
up to you to decide, what style fits you better.

Creating custom content elements manually
=========================================

Everything that Mask offers can be done manually in your own extension, of
course. But this can be very tedious, as a lot of code is needed for one single
element in various places.

A content element consists of:

*  PHP (TCA) - Field definitions, general table configuration
*  PHP (TCA) - Registration in the cTypes (content element types) select box
*  PHP (TCA) - Overrides on per element level
*  PHP - Icon registration
*  TSconfig - Registration in the new content element wizard
*  TypoScript - Setup of the fluid template path
*  SQL - Extending the database schema
*  PHP - Data Processing

As you can see, there is a lot of initial setup and additional configuration
needed for creating one single element. Read the :ref:`official documentation for
custom content elements <t3coreapi:adding-your-own-content-elements>` for a
complete overview.

With Mask, you mostly don't have to care about all of this anymore and you can
concentrate on your real work: Creating **awesome** content elements for your
customer's (or whoever else's) website.

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
