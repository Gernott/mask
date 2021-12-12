.. include:: ../Includes.txt

.. _installation:

============
Installation
============

.. note::
   Before you start using mask, you should have set up your :ref:`sitepackage<sitepackage>`.

Add Mask as dependency
======================

It is important to add Mask as a dependency in your `ext_emconf.php` of your sitepackage. This ensures, Mask is loaded
**before** your Theme extension. Only then, you can override the generated TCA from Mask in your Overrides folder.

::

   $EM_CONF[$_EXTKEY] = [
       'constraints' => [
           'depends' => [
               'mask' => '' // Add the minimum version here or leave blank for any version.
           ]
       ]
   ];

For composer installations also add the requirement to your extension's
`composer.json` file. In TYPO3 v11 the `ext_emconf.php` file is not even needed
anymore, when in composer mode.

.. code-block:: json

   {
      "require": {
         "mask/mask": "^7"
      }
   }

Install and configure
=====================

Download Mask with composer by running the command `composer require mask/mask` or install via extension manager.
The first thing you have to do is to define the paths to various template directories.
The easiest way to change the paths is to add the snippet below to your `LocalConfiguration.php` in the :php:`EXTENSIONS` section:

::

   ...
   'mask' = [
       'loader_identifier' => 'json',
       'backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Templates/',
       'backendlayout_pids' => '0,1',
       'content' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Templates/',
       'json' => 'EXT:sitepackage/Configuration/Mask/mask.json',
       'content_elements_folder' => 'EXT:sitepackage/Configuration/Mask/ContentElements',
       'backend_layouts_folder' => 'EXT:sitepackage/Configuration/Mask/BackendLayouts',
       'layouts' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Layouts/',
       'layouts_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Layouts/',
       'partials' => 'EXT:sitepackage/Resources/Private/Mask/Frontend/Partials/',
       'partials_backend' => 'EXT:sitepackage/Resources/Private/Mask/Backend/Partials/',
       'preview' => 'EXT:sitepackage/Resources/Public/Mask/',
   ],
   ...

Explanations for the settings can be found :ref:`here <extension-settings>`.
Adjust the paths to your needs and activate the extension. When you visit the Mask backend, you will have the option to
create all missing files and folders defined here.

.. note::
   This is also great to have in version control so others will have this already set up when checking out the project.

.. versionadded:: 7.1.0

If you didn't configure yet anything and visit the Mask module, you are able to
auto-configure the paths. You simply have to provide an extension key of a
**loaded** extension.

.. figure:: ../Images/Mask7.1/AutoConfiguration.png
   :alt: Mask auto-configuration
   :class: with-border

   Mask warns you about missing configuration

Include TypoScript
==================

Mask works best with :ref:`fluid_styled_content <fluid-styled-content>` so go to your static includes in the template
module and include it there. **After** that also include the Mask static TypoScript.

.. figure:: ../Images/AdministratorManual/TypoScriptTemplate.png
   :alt: Include TypoScript Template
   :class: with-border

   Include TypoScript Template

That's it. Now you can start creating your own content elements!
