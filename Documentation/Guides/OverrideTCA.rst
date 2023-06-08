.. include:: ../Includes.txt

.. _tcaoverride-guide:

=============
TCA Overrides
=============

Mask has a bunch of options integrated from :ref:`TCA <t3tca:start>`
(Table Configuration Array), but it will probably never have the complete power
to provide **every** option. There are simply too many possibilities, which
need to be considered. Therefore it is advised, to manually override the
generated Mask TCA, if more options are desired.

How to override Mask TCA
========================

.. note::

   First make sure, you added Mask as a dependency in your sitepackage, like
   explained in the :ref:`installation guide <installation>`.

Fields in tt_content
--------------------

It works exactly like overriding core TCA. You only need to know, what Mask
generates. Normal fields defined in the root-level are belonging to the table
:sql:`tt_content` and are always prefixed with `tx_mask_`. To override TCA from
such a field, just extend the TCA in
`Configuration/TCA/Overrides/tt_content.php`.

Example:

.. code-block:: php
   :caption: EXT:sitepackage/Configuration/TCA/Overrides/tt_content.php

   $GLOBALS['TCA']['tt_content']['columns']['tx_mask_your_field']['config']['some_option'] = 'some_value';

Furthermore, starting from mask version 8.2, you have the option to enable field reuse,
allowing you to configure and reuse fields directly in the mask UI or through the JSON configuration.
For more detailed information, please refer to the :ref`"Create Content Elements" section <content-elements-shared-reuse>`,
where you can find additional guidance and instructions on this topic.
With reusing fields feature enabled, Mask will automatically generate TCA columnsOverride
for almost all available options, making it easier to configure and customize your elements.

Fields in custom tables
-----------------------

As soon as you create a :ref:`repeating <fields-inline>` field, Mask creates a
new custom table. Therefore you need to change the key to the table's name.
Extend the TCA in `Configuration/TCA/Overrides/tx_mask_custom_table.php`

Example:

.. code-block:: php
   :caption: EXT:sitepackage/Configuration/TCA/Overrides/tx_mask_custom_table.php

   $GLOBALS['TCA']['tx_mask_custom_table']['columns']['tx_mask_your_field']['config']['some_option'] = 'some_value';


Inline content elements
-----------------------

With the pure might of TCA it is also possible to override configuration of
inline children. The most common usage in Mask would probably be to adjust
default values in type `Content` inline children.

In this example we override the default value of `space_before_class` to
`medium`, when it is created inside of your Mask element with the field
`tx_mask_my_content_field`.

.. code-block:: php
   :caption: EXT:sitepackage/Configuration/TCA/Overrides/tt_content.php

   $GLOBALS['TCA']['tt_content']['columns']['tx_mask_my_content_field']['config']['overrideChildTca']['columns']['space_before_class']['config']['default'] = 'medium';

.. note::

   It is not possible to assign different default values depending on the `CType`,
   because the first opened element will be persisted, when switching the type.

Refer to the official TCA documentation of :ref:`overrideChildTca <t3tca:columns-inline-properties-overrideChildTca>`
for in depth information.

This is all you need to know. You have the full capabilities of the TCA and
you are not limited by Mask's features. Mask strives to add as many features of
the TCA as possible, but you don't have to wait for that.

See also:

*  :doc:`Crop Variants <CropVariants>`
