.. include:: ../Includes.txt

.. _cropvariants-guide:

=============
Crop Variants
=============

You can exactly define which :ref:`crop variants <t3tca:columns-imageManipulation>` a mask element should offer for its
images.

For specific content element
============================

One way to set the crop variants is by addressing a specific content element (cType). This does only work for images not
placed in a repeater field. For this reason the TCA should be extended in `Configuration/TCA/Overrides/tt_content.php`.

Example crop variant definition:

::

   $teaserCropVariants = [
       'teaser' => [
           'title' => 'Teaser',
           'allowedAspectRatios' => [
               'portrait' => [
                   'title' => 'Portrait',
                   'value' => 3 / 4
               ],
               'landscape' => [
                   'title' => 'Landscape',
                   'value' => 4 / 3
               ],
           ],
       ],
   ];

   $table = 'tt_content';
   $cType = 'mask_teaser';
   $column = 'tx_mask_teaser_image';

   $GLOBALS['TCA'][$table]['types'][$cType]['columnsOverrides'][$column]['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $teaserCropVariants;

The table is :php:`tt_content`, the cType is :php:`mask_teaser` (no `tx_` here) and the column is :php:`tx_mask_teaser_image`.

For specific image column
=========================

An alternative is to set the crop variant for a specific column. This is of course less specific and will be shared
across multiple content elements with the same image field. This is also the preferred way for images residing in
repeater fields.

Examples:

::

   // Use this for repeating fields. File: TCA/Overrides/tx_mask_teasers.php
   $table = 'tx_mask_teasers';
   $column = 'tx_mask_teaser_image';

   $GLOBALS['TCA'][$table]['columns'][$column]['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $teaserCropVariants;

   // Also works in tt_content. File: TCA/Overrides/tt_content.php
   $table = 'tt_content';
   $column = 'image';

   $GLOBALS['TCA'][$table]['columns'][$column]['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $teaserCropVariants;

See also:

*  :doc:`TCA Overrides <OverrideTCA>`
