<?php

namespace MASK\Mask\DataProcessing;

use MASK\Mask\Helper\InlineHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class MaskProcessor implements DataProcessorInterface
{

    /**
     * InlineHelper
     *
     * @var \MASK\Mask\Helper\InlineHelper
     * @Inject()
     */
    protected $inlineHelper;

    /**
     * Process data of a record to add files and inline elements of mask fields
     *
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->inlineHelper = $objectManager->get(InlineHelper::class);

        $this->inlineHelper->addFilesToData($processedData['data'], "tt_content");
        $this->inlineHelper->addIrreToData($processedData['data']);
        return $processedData;
    }
}
