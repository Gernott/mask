.. include:: ../../Includes.txt

.. _fields-date:

Date
====

.. figure:: ../../Images/FieldTypes/Date.svg
   :alt: Date
   :class: float-left
   :width: 64px

An input field for a date (stored as MySQL DATE type).

.. rst-class::  clear-both

.. code-block:: php

   'type' => 'input',
   'dbType' => 'date'

.. figure:: ../../Images/FieldTypes/DatePreview.png
   :alt: Date field
   :class: with-border

   Date field

Available TCA options
---------------------

*  :ref:`config.default <t3tca:tca_property_default>`
*  :ref:`config.placeholder <t3tca:tca_property_placeholder>`
*  :ref:`config.range.lower <t3tca:columns-input-datetime-properties-range>`
*  :ref:`config.range.upper <t3tca:columns-input-datetime-properties-range>`
*  :ref:`config.eval.required <t3tca:columns-input-properties-eval>`
*  :ref:`l10n_mode <t3tca:columns-properties-l10n-mode>`
*  :ref:`config.behaviour.allowLanguageSynchronization <t3tca:tca_property_behaviour_allowLanguageSynchronization>`
*  :ref:`config.eval.null <t3tca:columns-input-properties-eval>`
*  :ref:`config.mode <t3tca:tca_property_mode>`

See a complete overview of Date TCA options in the :ref:`official documentation <t3tca:columns-input-properties-dbtype>`.
