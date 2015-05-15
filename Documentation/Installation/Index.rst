.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Target group: **Administrators**

.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Import and install the Extension in the TYPO3-Backend in Module **Extensionmanager**.

#. Include static template **Mask** in Module **Template** in your main TypoScript-Template.



.. figure:: ../Images/AdministratorManual/TypoScriptTemplate.png
   :alt: Include TypoScript Template

   Include TypoScript Template

After installation, check the extension-settings:

File with project-specific mask configuration. [basic.json]
  Mask stores the information, which is needed to generate contentelements and extend pagetemplates, into one file: mask.json. With this setting you can change the path to this file.
  
  
  Default is: typo3conf/mask.json

Folder for Content Fluid Templates (with ending slash). [basic.content]
  Mask generates a html-file with fluid-tags for each new contentelement. Here you can set the folder of this file.
 
  
  Default is: fileadmin/templates/content/

Folder for preview-images (with ending slash). [basic.preview]
  Mask takes a copy of the Mask-logo as preview-image for each new contentelement. Yes, afterwards you should change this image to your prefered preview-image or icon. Here you can set the path to the preview-images.
  
  
  Default is: fileadmin/templates/preview/


.. figure:: ../Images/AdministratorManual/ExtensionManager.png
   :alt: Extension Manager options

   Extension Manager options
