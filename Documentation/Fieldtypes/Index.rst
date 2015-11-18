.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Fieldtypes
==========

String
------
An input-field for Text.

Integer
-------
An input-field for Integer-Numbers.

Float
-----
An input-field for Floating-Numbers.

Link
----
An input-field for TYPO3-links.

Date
----
An input-field for a Date (dd.mm.yyyy).

Datetime
--------
An input-field for a Date with Time (dd.mm.yyyy hh.ii.ss).

Text
----
A multiline Textfield.

Richtext
--------
A multiline Textfield with richtext-editor.

Checkbox
--------
One or more checkboxes.

Radiobutton
-----------
One or more radiobuttons.

Selectbox
---------
Selectbox with own values or relation to other database-table.

File
----
File-field with using of FAL.
You can display images in the frontend with f:image or with f:cObject:

.. code-block:: typoscript

	<f:for each="{data.tx_mask_image}" as="file">
		f:image example:
		<f:image src="{file.uid}" treatIdAsReference="1" width="200" />
		
		f:cObject example:
		<f:cObject typoscriptObjectPath="lib.my_image" data="{image: file.uid}" />
	</f:for>
	
In case of using f:cObject, use the following TypoScript in your setup-field:

.. code-block:: typoscript

	lib.my_image = IMAGE
	lib.my_image.file {
		import.field = tx_mask_image
		treatIdAsReference = 1
	}

Repeating
---------
Repeating field, using IRRE technic. You can add subfields to this item.
Example: Create a slider with multiple slides.
