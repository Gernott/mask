.. include:: ../../Includes.txt

.. _fields-link:

Link
====

.. figure:: ../../Images/FieldTypes/Link.svg
   :alt: Link
   :class: float-left
   :width: 64px

An input field for links.

.. rst-class::  clear-both

.. code-block:: php

   'type' => 'input',
   'renderType' => 'inputLink'

.. figure:: ../../Images/FieldTypes/LinkPreview.png
   :alt: Link field
   :class: with-border

   Link field

Available TCA options
---------------------

*  :ref:`config.placeholder <t3tca:tca_property_placeholder>`
*  :ref:`config.size <t3tca:columns-input-properties-size>`
*  :ref:`config.eval.required <t3tca:columns-input-properties-eval>`
*  :ref:`config.fieldControl.linkPopup.options.allowedExtensions <t3tca:tca_property_fieldControl_linkPopup>`
*  :ref:`config.fieldControl.linkPopup.options.blindLinkOptions <t3tca:tca_property_fieldControl_linkPopup>`
*  :ref:`l10n_mode <t3tca:columns-properties-l10n-mode>`
*  :ref:`config.behaviour.allowLanguageSynchronization <t3tca:tca_property_behaviour_allowLanguageSynchronization>`
*  :ref:`config.eval.null <t3tca:columns-input-properties-eval>`
*  :ref:`config.mode <t3tca:tca_property_mode>`

See a complete overview of inputLink TCA options in the :ref:`official documentation <t3tca:columns-input-renderType-inputLink>`.
