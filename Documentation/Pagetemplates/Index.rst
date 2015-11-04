.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Pagetemplates
=============

After installing Mask and include the static TypoScript-template, you can start using Mask. Open the Backend-module **Mask**.

You can not create pagetemplates with mask. This have to be done in **List** module with **Backend Layouts**. First create a Backend Layout for each pagetemplate you need. (Example: Home, Content, 3-Columns,...).
Now you have the possibility in the Mask backend-module, to extend any backendlayout with special fields. This is the same way as described in **Contentelements**.

A preview-image can be set in the Backend Layout record, not in Mask.

TypoScript
----------

Mask comes with a ready-to-use TypoScript for Templating. Define your PAGE-Object like this:

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

If you have more than one pagetemplate, add for each element this lines:

.. code-block:: typoscript

	page.10.file.stdWrap.cObject {
		2 = TEXT
		2.value = fileadmin/templates/second.html
	}

You have to change the ID from 2 to your ID from the belonging Backend Layout. And you have to change the path and filename to your needs.
Remember: Each pagetemplate needs his own Backend Layout record. You find the ID of your Backend Layout in the List Module on hovering the Icon of the Backend Layout.

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

A pagetemplate need almost one or more content columns. To create them start in the Backend Layout record and define the needed columns. For each column define a **Column number**. This is the ID, which is used in the Database and in the following TypoScript.

The next step is adding the following Fluid-code into your page-html-template, on each position where you want to place your columns:
<f:cObject typoscriptObjectPath="lib.content0"/>
Change the 0 of content0 to your column-ID from the Backend Layout record.

The last step ist to define each lib.content* in your TypoScript-setup with this two lines:

.. code-block:: typoscript

	lib.content0 < temp.mask.content
	lib.content0.select.where = colPos=0`

Change all 0 to your ID.

Tipp: You can also use styles.content.get, if you want to use css_styled_content. See next chapter for more details.

If no content appears in the frontend, be sure you have included the static TypoScript-Template **Mask**. See **Installation** for more Details.


css_styled_content
------------------

css_styled_content is a static TypoScript configuration, delivered by the TYPO3 core. It handles the regular contentelements of TYPO3 (Regular Text Element, Text & Images, ...) in Backend and in the Frontend.

So if you don't want to use this elements in your project, you don't need to include the css_styled_content TypoScript.

If you like to use the regular contentelements and Mask-contentelements together, it is very important to include the static Mask TypoScript-Template after the static css_styled_content TypoScript-Template!
Otherwise no Mask-content is displayed in the Frontend.


Language information
--------------------

Mask works with TYPO3 features. So it is fully compatible with the TYPO3 languagehandling.
But in TYPO3 there are some Bugs with languages. It is not possible to use contentelements with the language-display-setting "all languages". In frontend the element appears twice or the element don't appear at all.
Mask bypass this Bug. You only have to use **temp.mask.content** instead of **styles.content.get**.
But be careful: temp.mask.content doesn't work with language-fallback!
