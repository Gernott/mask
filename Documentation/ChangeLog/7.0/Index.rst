.. include:: ../../Includes.txt

.. _mask-v7.0:

==============
Mask version 7
==============

All great things start with a simple idea. Mask v7 is really great. Now let me tell you why: If we look back at the Mask
v6 release, you will remember the new palette feature. What you don't know is how hard it was to implement this.
Especially the JavaScript and Fluid rendering part was horrible to deal with. A lot of jQuery spaghetti code, that did
some magic things like replacing placeholders when saving and keep the order correct at all times, is hard to debug.
This was mangled with the old jQuery UI draggable library. Keep in mind that everything is rendered with Fluid. This
means all the logic including recursive field structures coupled with a bunch of ViewHelpers were holding the Mask
Builder together.

Ok, enough of the rant. This is now all history. The whole Mask module has been rewritten from scratch. And it is now
completely based on `VueJS <https://vuejs.org/>`__. VueJS is one of the most popular JavaScript frameworks out there. It
allows us to define a data structure and work with Vue components. But the best thing is VueJS' reactivity pattern.
Everytime a form field changes, all other parts of the App are notified and update accordingly. All of this is coupled
with the well maintained `SortableJS <https://github.com/SortableJS/Sortable>`__ library.

You will notice there is now a loading screen between each view. With VueJS we got a so called single page application.
There is no reloading anymore. Everything is updated via Ajax.

To finish things up, Mask got a complete redesign and is now a lot more user friendly.

The goal with v7 was primarily to pave the way for many new features. Now it will be a lot easier to implement new
good stuff. So don't be dissapointed if this version doesn't include your long-awaited feature. After this there are
many versions to come: v7.1, v7.2, ... .

Let's explore what exactly changed:

.. toctree::
   :titlesonly:
   :maxdepth: 1

   NewAppearance
   IconAndColorPicker
   MultiStepWizard
   Validation
   MultiUse

More features
=============

* Already compatible with TYPO3 v11.2.0.
* The richtext configuration is now selectable per field.
* A new reset button is available which sweeps all your settings to the default values per field.
* All options are now linked to the official TYPO3 TCA documentation.
* Palettes and Tabs generate unique keys automatically so you don't need to specify them anymore.
* If folders or templates are missing they can now be created with just one click.
* HTML example opens now in a modal instead of a new window.
* Improved default SQL definitions (Existing definitions are kept).
* Greatly improved documentation with new Upgrade Guide and Tips and tricks sections.

Bugfixes
========

* If you have created a very large content element you might have encountered `this problem <https://github.com/Gernott/mask/issues/378>`__.
  With Mask v7 this won't happen anymore, as all fields are serialized into one POST parameter.

Technical improvements
======================

* jQuery code and fluid logic replaced completely with a VueJS application.
* In general jQuery usage is minimized.
* ViewHelper logic moved to a central `AjaxController`, which handles all VueJS requests.
* TCA options are now defined in a central `TcaFields.php` file.
* Default values for settings are now defined in a central `Defaults.php` file.
* Settings for fields and tab structure is now defined in `Tabs/*.php`.
* Replace extbase module registration with normal registration.
* Remove dead code.
* Unit Tests added.

`git diff main v6 --shortstat`:

::

   364 files changed, 12869 insertions(+), 14481 deletions(-)


Breaking changes
================

* `DataStructure/FieldType.php` renamed to `Enumeration/FieldType.php`

Removed classes
---------------

* ContentRepository.php
* IconRepository.php
* PageRepository.php
* Content.php
* Page.php
* All ViewHelpers removed besides that needed in Backend Previews.

Thank You
=========

A big thanks to all who voted for Mask in the `2021 budget poll <https://typo3.org/article/result-of-the-budget-poll-2021>`__!
We got a great amount granted for this project.

Thanks to Eric Haake, who suggested Mask in the first place!

And thanks to all who are using Mask and provide detailed issues and suggestions to improve this project. And all who
are actively helping others on slack. Without you this would not be possible.

Thanks!
