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

::

   $GLOBALS['TCA']['tt_content']['columns']['tx_mask_your_field']['config']['some_option'] = 'some_value';


Fields in custom tables
-----------------------

As soon as you create a :ref:`repeating <fields-inline>` field, Mask creates a
new custom table. Therefore you need to change the key to the table's name.
Extend the TCA in `Configuration/TCA/Overrides/tx_mask_custom_table.php`

Example:

::

   $GLOBALS['TCA']['tx_mask_custom_table']['columns']['tx_mask_your_field']['config']['some_option'] = 'some_value';


This is all you need to know. You have the full capabilities of the TCA and
you are not limited by Mask's features. Mask strives to add as many features of
the TCA as possible, but you don't have to wait for that.

See also:

*  :doc:`Crop Variants <CropVariants>`
