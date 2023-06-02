.. include:: ../../Includes.txt

================
Mask version 8.2
================

Reusing fields between content elements
======================================
.. _migrateToReusingFields:

By default, mask shares field configurations across content elements,
allowing you to selectively overwrite labels and descriptions for individual fields.
Starting from mask version 8.2, it became possible to reuse fields across different elements.
When this behavior is enabled in the extension settings, you gain the ability
to override nearly every setting for individual fields and utilize them across multiple elements.
For instance, consider a dropdown field that needs to be reused with various select options.
This behavior is particularly useful when you have numerous elements with diverse options
but prefer not to expand your database with an excessive number of fields.

Please note that this feature is not enabled by default and needs to be explicitly enabled in the extension settings `general.reuse_fields`.
You can also enable it in your PHP configuration:

```
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['overrideSharedFields'] = true;
```

Caution should be exercised when enabling this option, as once reusing fields are enabled,
it is not possible to convert the configuration back to shared fields.
If you have existing content elements that you wish to configure with reusing fields, there are two options available.
You can either utilize the mask User Interface that shows a hint that you should reconfigure the configuration to migrate to reusing fields,
or alternatively, execute the provided restructuring command to facilitate the migration process:

Command with TYPO3 CLI:

.. code-block:: shell

   // composer mode
   vendor/bin/typo3 mask:restructureReusingFields

   // classic mode
   typo3/sysext/core/bin/typo3 mask:restructureReusingFields

Sponsoring
==========

Do you like the early adoption of Mask? You can now sponsor my (Nikita Hovratov)
free contribution to this project on `GitHub <https://github.com/sponsors/nhovratov>`__.
