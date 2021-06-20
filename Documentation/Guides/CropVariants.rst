.. include:: ../Includes.txt

.. _cropvariants-guide:

=============
Crop Variants
=============

You can exactly define which :ref:`crop Variants <t3tca:columns-imageManipulation>` a mask element should offer for its images.

Images that are not in a repeater live in `tt_content`. So the for example you will do in `TCA/Overrides/tt_content.php`:

::

$teaserCropVariants = [
      // Please note, that the array for overrideChildTca is merged with the child TCA, so are the crop variants that are defined   in the child TCA (most likely sys_file_reference). Because you cannot remove crop variants easily, it is possible to disable    them for certain field types by setting the array key for a crop variant disabled to the value true
      'default' => [
           'disabled' => true,
       ],
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

   $GLOBALS['TCA']['tt_content']['types']['mask_teaser']['columnsOverrides']['tx_mask_teaser_image']['config']['overrideChildTca']  ['columns']['crop']['config']['cropVariants'] = $teaserCropVariants;

The table is `tt_content`, the cType is `mask_teaser` (no `tx_` here) and the column is `tx_mask_teaser_image`, that gives us the code above.

If you have a repeater in which the images are placed (so one mask element may contain mutltiple items of the same element), you have to omit the `cType` and address the repeating table.

::

   $GLOBALS['TCA']['tx_mask_teasers']['columns']['tx_mask_teaser_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = $teaserCropVariants;
