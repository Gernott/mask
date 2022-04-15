.. include:: ../Includes.txt

.. _fieldtypes:

===========
Field Types
===========

The Mask field types are basically a convenient combination of the :ref:`TCA column types <t3tca:columns-types>`. If certain criteria meet, a
field will be assigned to a Mask field type. The criteria are specific TCA settings, which define the type of the
field. For example the TCA type `input` can have many appearances. With :php:`renderType => 'inputLink'` set, the field
transforms into a link field. Mask will assign it to the field type `Link` then. This is how core fields are displayed
as the appropriate field type.

.. toctree::
   :maxdepth: 1
   :glob:

   Type/*
