<img src="https://forge.typo3.org/headerimages/3021.jpg" width="100%" />

mask
======================

Create your own content elements and page templates. Easy to use, even without programming skills because of the comfortable drag&drop system. Stored in structured database tables.

##What does it do?

Mask is a TYPO3-extension for creating contentelements and extend pagetemplates. It is possible to add new fields to any element. Fields can have serveral types, for example: text, file, relations, richtext,...

##Advantages of Mask

* Mask stores the content in columns in databasetables - not in XML (Flexform)
* Mask reuses existing database-fields to conserve the database
* Mask works only with existing features of the TYPO3-core: backend_layouts, fluid, typoscript, 
* Mask allows repeating content with IRRE-technology
* Mask supports multilanguage projects and resolves some language-bugs of TYPO3
* Mask supports workspaces and versioning
* Mask is written in Extbase, the modern way to create extensions

Installation
------------

To install the extension, perform the following steps:

1. Import and install the Extension in the TYPO3-Backend in Module **Extensionmanager**.

2. Include static template **Mask** in Module **Template** in your main TypoScript-Template.

3. After installation, check the extension-settings:

  * File with project-specific mask configuration. [basic.json]
  Mask stores the information, which is needed to generate contentelements and extend pagetemplates, into one file: mask.json. With this setting you can change the path to this file.
  Default is: typo3conf/mask.json

  * Folder for Content Fluid Templates (with ending slash). [basic.content]
  Mask generates a html-file with fluid-tags for each new contentelement. Here you can set the folder of this file.
  Default is: fileadmin/templates/content/

  * Folder for preview-images (with ending slash). [basic.preview]
  Mask takes a copy of the Mask-logo as preview-image for each new contentelement. Yes, afterwards you should change this image to your prefered preview-image or icon. Here you can set the path to the preview-images.
  Default is: fileadmin/templates/preview/

  * Folder for backend fluid templates (with ending slash). [basic.backend]
 With mask you can style the backend preview of your content elements. Here you can set the path to your backend fluid templates.
  Default is: fileadmin/templates/backend/
