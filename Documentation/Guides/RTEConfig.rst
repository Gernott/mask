.. include:: ../Includes.txt

.. _rteconfig-guide:

==========
RTE Config
==========

You can exactly define which :ref:`RTE config <cms-rte-ckeditor:config-examples>` a mask element should offer for its richtext fields.

For this, you have to follow these 3 steps:

Create the config
-----------------

In your sitepackage, add the config files you need, e.g. `sitepackage/Configuration/RTE/Custom.yaml` and `sitepackage/Configuration/RTE/Simple.yaml`.
To get the right keys, the :ref:`CKeditor configurator <https://ckeditor.com/latest/samples/toolbarconfigurator/index.html#basic>` is helpful.

Load the config
---------------

In `sitepackage/ext_localconf.php`, wire the new config files as RTE preset:

::

   $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['custom'] = 'EXT:sitepackage/Configuration/RTE/Custom.yaml';
   $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['simple'] = 'EXT:sitepackage/Configuration/RTE/Simple.yaml';


Target the config
----------------

In TsConfig (e.g. `sitepackage/Configuration/TsConfig/Page/RTE.tsconfig`), assign the config to the desired field, using the element's cType as well as the configs key:

::
   RTE.default.preset = custom
   RTE.config.tt_content.tx_mask_content.types.mask_teaser.preset = simple
