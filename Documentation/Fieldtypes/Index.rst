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

Size
^^^^
Defines the height of the selectbox. Example: If set to 2, the selectbox get a height of 2 entrys.

AutoSizeMax
^^^^^^^^^^^
If set, the height of the selectbox is set to the amount of values, but at maximum to this value.

foreign_table
^^^^^^^^^^^^^
Here you can auto-fill the selectbox with values from a databasetable. Just enter the tablename. You can access the value easy with TypoScript. Example: If set to pages, the selectbox is filled with all pages from your project.
In your fluid-template, you can use <f:cObject typoscriptObjectPath="lib.pages" data="{data}" />
And in your TypoScript setup you can access to all data of the selected pages-record:

.. code-block:: typoscript
	lib.pages = CONTENT
	lib.pages {
  	table = pages
	  select {
	    pidInList = root
	    recursive = 99
	    uidInList.field = tx_mask_select
	  }
  	renderObj = COA
	  renderObj {
	    10 = TEXT
	    10.field = title
	    10.wrap = <h1>|</h1>
	    20 = TEXT
	    20.field = tstamp
	    20.strftime = %d.%m.%Y
	    20.noTrimWrap = |<p>Last update: |</p>|
	  }
	}


foreign_table_where
^^^^^^^^^^^^^^^^^^^
If you use foreign_table and want to filter the values in your selectbox or set the sorting of the selectbox options, you can do this here. Example:
Sorting by title (Z-A): ORDER BY title DESC
Only default pages-doctype: AND doktype=1

renderType
^^^^^^^^^^
Change the type of the selectbox from singe to multiple, ore use checkboxes or a shuttle instead of a slectbox.

Maxitems
^^^^^^^^
If renderType is not set to Selectbox single, you can define the maximum allowed amount of items. The values will be stored commaseparated. So you can use TypoScript split to access to each value.

Items
^^^^^
Here you can define static values. It is allowed to use them standalone or in combination with foreign_table. Example with usage of foreign_table:
Please choose,0
Note, that the ids must be integers! If you use only static values without using foreign_table, ids should be startwith 1, because 0 resets the state to NULL.

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
Repeating field, using IRRE technique. You can add repeating subfields to this item.
Example: Create a slider with multiple slides.

Content
---------
Allow users to add content elements to the content element. This way editors can nest the content elements and can for instance build an accordion in a comfortable way.
Or if you want to create a media section, where only images, videos and audio elements are allowed, you can allow editors to only add the content elements that fit this requirement.

If you use a Backend Preview for your contentelement, you can add a ViewHelper to show an edit-icon for every child-element:
::

    {namespace mask=MASK\Mask\ViewHelpers}
    <f:for each="{data.tx_mask_mycontent}" as="data_item">
      <mask:editLink element="{data_item}"><img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-open.svg" width="16" height="16"> Edit element</mask:editLink><br />
    </f:for>

Tab
---------
With the tab field, you can add a tab divider to better organise your fields in several tabs, and make editor's life easier.
