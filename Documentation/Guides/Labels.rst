.. include:: ../Includes.txt

.. _labels-guide:

==========================
Change default Mask labels
==========================

Mask defines some default labels, which can be changed, if you know how.

Change "Mask Elements" in New Content Element Wizard
====================================================

This label can be easily changed by TSconfig.

.. code-block::

   mod.wizards.newContentElement.wizardItems.mask.header = Custom label

.. note::

   This does not work inside the newly introduced :file:`page.tsconfig` file in
   TYPO3 v12, as it does not take loading order into account.
