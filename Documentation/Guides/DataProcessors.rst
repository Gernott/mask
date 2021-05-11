.. include:: ../Includes.txt

.. _data-processor-guide:

===============
Data Processors
===============

You can process the data of any content element before it is assigned to the fluid template with the help
of :ref:`DataProcessors <t3coreapi:ConfigureCE-DataProcessors>`. They can help you to reduce overusage of ViewHelpers.
In general fluid templates shouldn't include too much logic.

For example imagine you have some kind of product content element with these fields:

* title (:ref:`String <fields-string>`)
* price (:ref:`Float <fields-float>`)

Element key: `product`.

You probably also would want to show the price with vat added.
Now what if you only want to enter the net amount? You probably would either do some mathematical operations in fluid
directly or create a ViewHelper for that. But there is a better way by using a data processor:

::

   <?php

   namespace VENDOR\Extension\DataProcessing;

   use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
   use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

   class VatProcessor implements DataProcessorInterface
   {
       /**
        * @param ContentObjectRenderer $cObj The data of the content element or page
        * @param array $contentObjectConfiguration The configuration of Content Object
        * @param array $processorConfiguration The configuration of this processor
        * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
        * @return array the processed data as key/value store
        */
       public function process(
           ContentObjectRenderer $cObj,
           array $contentObjectConfiguration,
           array $processorConfiguration,
           array $processedData
       ) {
           $vatMultiplier = 1 + $processorConfiguration['vat'] / 100;
           $processedData['data']['priceGross'] = $processedData['data']['tx_mask_price'] * $vatMultiplier;
           return $processedData;
       }
   }

You can create the data processor in the folder `Classes/DataProcessing/VatProcessor.php` of your :ref:`sitepackage <sitepackage>`.
The `VatProcessor` implements the `DataProcessorInterface` which forces us to implement the `process` method. There are
some parameters we can work with:

:php:`$cObj`
   The ContentObjectRenderer. You can do any stdWrap operation with it.

:php:`$contentObjectConfiguration`
   Contains the configuration of the current content object.

:php:`$processorConfiguration`
   Here you can define custom options. See TypoScript configuration section.

:php:`$processedData`
   This is the exact data array you would work with in your fluid templates.

Now we can manipulate the data in :php:`$processedData` the way we want it. In our case we enhance the array with
another entry `priceGross`. For this we use the `price` defined in our content element and the additional parameter
`vat` which we will define in the next section.

Register DataProcessor
======================

Now we need to register and configure the data processor for our specific content element. For this we need to add a
little bit of TypoScript:

.. code-block:: typoscript

   tt_content {
      mask_product {
         dataProcessing {
            110 = VENDOR\Extension\DataProcessing\VatProcessor
            110 {
               vat = 19
            }
         }
      }
   }

The key `mask_product` represents the CType of our Mask element. Mask adds this `mask_` prefix automatically to your
specified element key. You can pass any additional parameters to the dataProcessor. They will be available in the
:php:`$processorConfiguration` array.

.. note::

   Mask reserves the key 100 for its own `MaskProcessor`. Mask uses it to fill :ref:`Inline <fields-inline>` and :ref:`File <fields-file>`
   fields into the data array.

Use in fluid template
=====================

Finally we can use our processed data in the fluid template `Product.html`:

.. code-block:: html

   Title: {data.tx_mask_title} <!-- Some product title -->
   Price net: {data.tx_mask_price -> f:format.number(decimals: '2')}€ <!-- 100.00€ -->
   Price gross: {data.priceGross -> f:format.number(decimals: '2')}€ <!-- 119.00€ -->

Note that the added entry `priceGross` does not contain the `tx_mask_` prefix.

This way you don't have to rely on multiple ViewHelpers and fluid logic. Move the logic away from you view by using
DataProcessors.

Have a look at the :ref:`official documentation <t3coreapi:content-elements-custom-data-processor>` for more examples.
