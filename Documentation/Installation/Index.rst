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

#. Include static template **Content Elements (fluid_styled_content)** first and after it **Mask (mask)** in Module **Template** in your main TypoScript-Template.

.. figure:: ../Images/AdministratorManual/TypoScriptTemplate.png
   :alt: Include TypoScript Template

   Include TypoScript Template

Extension Settings
------------------

General
_______

json
....

| File with project specific mask configuration.
| Mask stores the information, which is needed to generate contentelements and extend pagetemplates into one file: mask.json. With this setting you can change the path to this file.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/mask.json`

backendlayout_pids
..................

| PageIds from where the in PageTS defined backend layouts should be loaded (comma separated)
| Default: :code:`0,1`

Frontend
________

content
.......

| Folder for Content Fluid Templates (with trailing slash).
| Mask generates a html file with fluid tags for each new contentelement. Here you can set the path to this file.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Frontend/Templates/`

layouts
.......

| Folder for Content Fluid Layouts (with trailing slash).
| Here you can set the path to the fluid layouts of your mask templates.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Frontend/Layouts/`

partials
........

| Folder for Content Fluid Partials (with trailing slash).
| Here you can set the path to the fluid partials of your mask templates.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Frontend/Partials/`

Backend
_______

backend
.......

| Folder for Backend Preview Templates (with trailing slash).
| Here you can set the path the fluid templates for backend previews of your content elements.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Backend/Templates/`

layouts_backend
...............

| Folder for Backend Preview Layouts (with trailing slash).
| Here you can set the path to the fluid layouts of your mask backend previews.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Backend/Layouts/`

partials_backend
................

| Folder for Backend Preview Partials (with trailing slash).
| Here you can set the path to the fluid partials of your mask backend previews.
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Backend/Partials/`

preview
.......

| Folder for preview images (with trailing slash).
| You can change the preview image of contentelements to your prefered png image or png icon (32x32 pixel).
| Store them with the key of the contentelement as filename (e.g. mykey.png)
| Default: :code:`typo3conf/ext/mask_project/Resources/Private/Mask/Backend/Previews/`
|

.. figure:: ../Images/AdministratorManual/ExtensionConfiguration.png
   :alt: Extension Manager options

   Extension Configuration options in **Settings -> Extension Configuration**
