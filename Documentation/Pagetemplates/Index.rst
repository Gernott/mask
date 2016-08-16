.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Pagetemplates
=============

After installing Mask and including the static TypoScript-template, you can start using Mask. Open the Backend-module **Mask**.

You can not create pagetemplates with mask. This has to be done in **List** module with **Backend Layouts**. First create a Backend Layout for each pagetemplate you need. (Example: Home, Content, 3-Columns,...).
Now you have the possibilit to extend any of these Backend Layouts with special fields in the Mask backend-module. This works in the same way as described in **Contentelements**.

A preview-image can be set in the Backend Layout record, not in Mask.

TypoScript
----------

Mask comes with a ready-to-use TypoScript snippet for templating. You can define your PAGE-Object like this:

.. code-block:: typoscript

	page < temp.mask.page

This loads the following TypoScript:

.. code-block:: typoscript

	temp.mask.page = PAGE
	temp.mask.page {
		10 = FLUIDTEMPLATE
		10 {
			file.stdWrap.cObject = CASE
			file.stdWrap.cObject {
				key.data = levelfield:-1, backend_layout_next_level, slide
				key.override.field = backend_layout
				default = TEXT
				default.value = fileadmin/templates/default.html
			}
		}
	}

So you have to save your HTML-pagetemplate here: fileadmin/templates/default.html
If you want, you can change the path or filename easy with one TypoScript-line:

.. code-block:: typoscript

	page.10.file.stdWrap.cObject.default.value = yourpath/yourfile.html

If you have more than one pagetemplate, you have to add and adjust these lines for each template:

.. code-block:: typoscript

	page.10.file.stdWrap.cObject {
		2 = TEXT
		2.value = fileadmin/templates/second.html
	}

You have to change the ID from 2 to your ID from the belonging Backend Layout. And you have to change the path and filename to your needs.
Remember: Each pagetemplate needs its own Backend Layout record. You find the ID of your Backend Layout in the List Module on hovering the icon of the Backend Layout.

If you like to add some CSS or Javascript files, use the regular TypoScript or read the official TSREF for more possibilities:

.. code-block:: typoscript

	page {
		includeCSS.styles = path/your.css
		includeCSS.lightbox = path/lightbox.css
		includeJS.jquery = path/jquery.js
		includeJS.scripts = path/your.js
	}


Contentcolumns
--------------

A pagetemplate nearly always needs one or more content columns. To create these, start in the Backend Layout record and define the needed columns. For each column define a **Column number**. This is the ID, which is used in the Database and in the following TypoScript.

The next step is adding the following Fluid-Code into your page-html-template, on each position where you want to place your columns:
<f:cObject typoscriptObjectPath="lib.content0"/>
Change the 0 of content0 to your column-ID from the Backend Layout record.

The last step ist to define each lib.content* in your TypoScript-setup with these two lines:

.. code-block:: typoscript

	lib.content0 < styles.content.get
	lib.content0.select.where = colPos=0

Change all 0s to your ID.

If no content appears in the frontend, be sure to check if you have included the static TypoScript-Template **Content Elements (fluid_styled_content)** first and after it **Mask (mask)**.


fluid_styled_content
--------------------

fluid_styled_content is a static TypoScript configuration, delivered by the TYPO3 core. It handles the regular contentelements of TYPO3 (Regular Text Element, Text & Images, ...) in the Backend and in the Frontend. It also provides the TypoScript snippet styles.content.get. You need this TypoScript Template to render content in your frontend.

It is very important to include the static Mask TypoScript-Template after the static fluid_styled_content TypoScript-Template!
Otherwise no Mask-content will be rendered in the Frontend.
The same rules apply for the old css_styled_content.