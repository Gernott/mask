.. include:: ../Includes.txt

.. _use-existing-tca-fields-guide:

=======================
Use existing TCA fields
=======================

If you want to use already existing TCA fields, beyond the core fields, you can
use an EventListener to modify the :php:`$allowedFields` array.


EventListener example
=====================

.. code-block:: php
   :caption: EXT:some_extension/Classes/EventListener/MaskAllowedFieldsEventListener.php

   <?php

   declare(strict_types=1);

   namespace VENDOR\SomeExtension\EventListener;

   use MASK\Mask\Event\MaskAllowedFieldsEvent;

   class MaskAllowedFieldsEventListener
   {
       public function __invoke(MaskAllowedFieldsEvent $event): void
       {
           // Add field
           $event->addField('teaser');

           // Remove field
           $event->removeField('imagecols');

           // Get all allowed fields
           $allowedFields = $event->getAllowedFields();

           // Do your magic and set allowed fields
           $event->setAllowedFields($allowedFields);
       }
   }

Register EventListener
======================

.. code-block:: yaml
   :caption: EXT:some_extension/Configuration/Services.yaml

   services:
    VENDOR\Extension\EventListener\MaskAllowedFieldsEventListener:
      tags:
        - name: event.listener
          identifier: 'customizeAllowedFields'
          event: MASK\Mask\Event\MaskAllowedFieldsEvent


Have a look at the :ref:`official documentation <t3coreapi:EventDispatcher>` for more information.
