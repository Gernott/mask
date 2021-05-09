.. include:: ../Includes.txt

.. _installation:

============
Installation
============

.. note::
   Before you start installing mask, you should have set up your :ref:`sitepackage<sitepackage>`.

Install and configure
=====================

Download Mask with composer by running the command `composer require mask/mask` or install via extension manager.
As soon as you activate the extension, Mask will create a dummy extension `mask_project`. To prevent this you can
change the settings before you activate Mask. The easiest way to change the paths is to add this to your
`AdditionalConfiguration.php`:

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mask'] = [
       'backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Templates/',
       'backendlayout_pids' => '0,1',
       'content' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Templates/',
       'json' => 'EXT:sitepackage/Configuration/Mask/mask.json',
       'layouts' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Layouts/',
       'layouts_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Layouts/',
       'partials' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Partials/',
       'partials_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Partials/',
       'preview' => 'EXT:sitepackage/Resources/Public/Mask/',
   ];

Explanations for the settings can be found :ref:`here <extension-settings>`.
Adjust the paths to your needs and activate the extension. When you visit the Mask backend, you will have the option to
create all missing files and folders defined here.

.. note::
   This is also great to have in version control so others will have
   this already set up when checking out.

Include TypoScript
==================

Mask works best with :ref:`fluid_styled_content <fluid-styled-content>` so go to your static includes in the template
module and include it there. **After** that also include the Mask static TypoScript.

.. figure:: ../Images/AdministratorManual/TypoScriptTemplate.png
   :alt: Include TypoScript Template
   :class: with-border

   Include TypoScript Template

That's it. Now you can start creating your own content elements!
