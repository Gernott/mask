.. include:: ../../Includes.txt

.. _fields-media:

Media
=====

.. figure:: ../../Images/FieldTypes/Media.svg
   :alt: Media
   :class: float-left
   :width: 64px

Media field using :ref:`FAL<t3coreapi:using-fal>`.

.. rst-class::  clear-both

.. code-block:: php

   'type' => 'inline',
   'foreign_table' => 'sys_file_reference',
   'overrideChildTca' => [
       'columns' => [
           'uid_local' => [
               'config' => [
                   'appearance' => [
                       'elementBrowserAllowed' => 'vimeo,youtube',
                   ],
               ],
           ],
       ],
   ],

.. figure:: ../../Images/FieldTypes/MediaPreview.png
   :alt: Media field
   :class: with-border

   Media field

Available TCA options
---------------------

*  `onlineMedia` (custom Mask option, which toggles available online media types)
*  :ref:`config.minitems <t3tca:tca_property_minitems>`
*  :ref:`config.maxitems <t3tca:tca_property_maxitems>`
*  :ref:`allowedFileExtensions <t3tca:columns-group-properties-appearance>` (used in :ref:`overrideChildTca <columns-inline-properties-overrideChildTca>`)
*  :ref:`config.appearance.collapseAll <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.expandSingle <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.useSortable <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.elementBrowserEnabled <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.fileUploadAllowed <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.fileByUrlAllowed <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.createNewRelationLinkTitle <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.enabledControls <t3tca:columns-inline-properties-appearance>`
*  :ref:`l10n_mode <t3tca:columns-properties-l10n-mode>`
*  :ref:`config.behaviour.allowLanguageSynchronization <t3tca:tca_property_behaviour_allowLanguageSynchronization>`
*  :ref:`config.appearance.showPossibleLocalizationRecords <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.showAllLocalizationLink <t3tca:columns-inline-properties-appearance>`
*  :ref:`config.appearance.showSynchronizationLink <t3tca:columns-inline-properties-appearance>`

See a complete overview of Inline TCA options in the :ref:`official documentation <t3tca:columns-inline>`.
