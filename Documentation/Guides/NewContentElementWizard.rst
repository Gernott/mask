.. include:: ../Includes.txt

.. _labels-guide:
.. _new-content-element-wizard-guide:

========================================
New Content Element Wizard customization
========================================

Per default, all Mask Content Elements are grouped in "Mask Elements". If you
want to group your elements in different tabs, you can do this by overriding
page TsConfig. Unfortunately, Mask doesn't provide a proper API for this, so
it looks a little tricky to do. You can also use this example to simply change
the label of the Mask group. See the comments in the code.

.. code-block:: typoscript

   mod.wizards.newContentElement.wizardItems {
       # Create new custom group
       myOtherGroup {
           header = My other Group
           after = mask
           # Copy config of Mask elements
           elements.mask_element_1 < mod.wizards.newContentElement.wizardItems.mask.elements.mask_element_1
           elements.mask_element_2 < mod.wizards.newContentElement.wizardItems.mask.elements.mask_element_2
           elements.mask_element_3 < mod.wizards.newContentElement.wizardItems.mask.elements.mask_element_3
           # Add to show list
           show = mask_element_1,mask_element_2,mask_element_3
       }

       mask {
           # Override title of the Mask group
           header = My Elements
           before = common
           # Remove all elements, which shouldn't be here.
           elements.mask_element_1 >
           elements.mask_element_2 >
           elements.mask_element_3 >
       }
   }

.. note::

   This does not work inside the newly introduced :file:`page.tsconfig` file in
   TYPO3 v12.
