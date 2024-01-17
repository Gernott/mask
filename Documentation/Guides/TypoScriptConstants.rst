.. include:: ../Includes.txt

.. _typoscriptconstants-guide:

==============================
Accessing TypoScript constants
==============================

To access a TypoScript constant in content elements,
first make it available to all mask elements via TypoScript:

.. code-block:: typoscript

   lib.maskContentElement {
       settings {
           pageTitle = {$mysitepackage.page.title}
       }
   }

In your mask content element fluid template it can be accessed via
:html:`{settings.pageTitle}`.
