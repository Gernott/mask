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
Now you have the possibility to extend any of these Backend Layouts with special fields in the Mask backend-module. This works in the same way as described in **Contentelements**.
You can also define backend layouts via page ts in your sitepackage (read below).
A preview-image can be set in the Backend Layout record, not in Mask.

Site Packages
----------

Mask can only help you with creating custom content elements and render them in
own fluid templates. To get started with creating your TYPO3 website in general
you need what's called a sitepackage. This is a special extension specialized
in defining html layouts, css, JavaScript, typoscript, ... so basically it's a
Theme-Extension.

There are many resources out there to get started. These are a few of those:

- https://www.sitepackagebuilder.com/
- https://extensions.typo3.org/extension/bootstrap_package/
- https://docs.typo3.org/m/typo3/tutorial-sitepackage/master/en-us/

fluid_styled_content
--------------------

fluid_styled_content is a static TypoScript configuration, delivered by the TYPO3 core. It handles the regular contentelements of TYPO3 (Regular Text Element, Text & Images, ...) in the Backend and in the Frontend. It also provides the TypoScript snippet styles.content.get. You need this TypoScript Template to render content in your frontend.

It is very important to include the static Mask TypoScript-Template after the static fluid_styled_content TypoScript-Template!
Otherwise no Mask-content will be rendered in the Frontend.
