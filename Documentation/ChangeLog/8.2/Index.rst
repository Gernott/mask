.. include:: ../../Includes.txt

================
Mask version 8.2
================

Overriding fields across content elements
=========================================
.. _migrateToReusingFields:

By default, Mask shares field configuration across content elements, but allows
you to selectively overwrite labels and descriptions for individual fields.
Starting from Mask v8.2, it became possible to override field configuration
across different elements. When this behavior is enabled in the extension
settings, you gain the ability to override nearly every setting for individual
fields and utilize them across multiple elements. For instance, consider a
dropdown field that needs to be reused with various select options. This
behavior is particularly useful when you have numerous elements with diverse
options but prefer not to expand your database with an excessive number of
fields.

Please note that this feature is not enabled by default (except for Core fields)
and needs to be explicitly enabled in the extension settings
:typoscript:`general.override_shared_fields`. You can also enable it in your PHP
configuration:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['overrideSharedFields'] = true;


Caution should be exercised when enabling this feature, as once enabled, it is
not possible to convert the configuration back to shared fields. If you have
existing content elements that you wish to configure with overriding fields,
there are two options available. You can either utilize the Mask User Interface
that shows a hint that you should reconfigure the configuration to migrate to
override fields, or alternatively, execute the provided restructuring command to
facilitate the migration process:

Command with TYPO3 CLI:

.. code-block:: shell

   // composer mode
   vendor/bin/typo3 mask:restructureOverrideFields

   // classic mode
   typo3/sysext/core/bin/typo3 mask:restructureOverrideFields


Special Thanks
==============

This feature was initiated and made by Sebastian Dernbach `sebastiande <https://github.com/sebastiande>`__,
who spent countless evenings to make it happen.
Thank you very much for your effort on pushing this feature to Mask! Chapeau!
