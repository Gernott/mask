.. include:: ../Includes.txt

.. _mask-events-api:

=====================
Element Change Events
=====================

If you want to execute additional actions after a element is created, updated or deleted,
you can use an EventListener to trigger your own actions.

The :php:`\MASK\Mask\Event\MaskAfterElementSavedEvent` event is directly executed after a content element is added or updated.
It has the modified TableDefinitionCollection and element key as parameters,
as well as information if the content element was created or updated.

The :php:`\MASK\Mask\Event\MaskAfterElementDeletedEvent` event is directly executed after a content element is deleted.
It has the modified TableDefinitionCollection and element key of the former existing content element as parameters.

MaskAfterElementSavedEvent EventListener example
================================================

.. code-block:: php
   :caption: EXT:some_extension/Classes/EventListener/MaskAfterElementSavedEventListener.php

   <?php

   declare(strict_types=1);

   namespace VENDOR\SomeExtension\EventListener;

   use MASK\Mask\Event\MaskAfterElementSavedEvent;

   class MaskAfterElementSavedEventListener
   {
       public function __invoke(MaskAfterElementSavedEvent $event): void
       {
            $tableDefinition = $event->getTableDefinitionCollection();
            $elementKey = $event->getElementKey();
            if ($event->isNewElement() {
               // do something if the element is newly created
            } else {
               // do something if the element is updated
            }
       }
   }


MaskAfterElementDeletedEvent EventListener example
==================================================

.. code-block:: php
   :caption: EXT:some_extension/Classes/EventListener/MaskAfterElementDeletedEventListener.php

   <?php

   declare(strict_types=1);

   namespace VENDOR\SomeExtension\EventListener;

   use MASK\Mask\Event\MaskAfterElementDeletedEvent;

   class MaskAfterElementDeletedEventListener
   {
       public function __invoke(MaskAfterElementDeletedEvent $event): void
       {
            $tableDefinition = $event->getTableDefinitionCollection();
            $elementKey = $event->getElementKey();
            // do something after the element is deleted
       }
   }

Register EventListeners
=======================

.. code-block:: yaml
   :caption: EXT:some_extension/Configuration/Services.yaml

   services:
    VENDOR\Extension\EventListener\MaskAfterElementSavedEventListener:
      tags:
        - name: event.listener
          identifier: 'executeCodeAfterChange'
          event: MASK\Mask\Event\MaskAfterElementSavedEvent
    VENDOR\Extension\EventListener\MaskAfterElementDeletedEventListener:
      tags:
        - name: event.listener
          identifier: 'executeCodeAfterDeletion'
          event: MASK\Mask\Event\MaskAfterElementDeletedEvent


Have a look at the :ref:`official documentation <t3coreapi:EventDispatcher>` for more information.
