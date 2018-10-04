<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class JsOpenParamsViewHelper extends AbstractViewHelper
{

    /**
     * Utility
     *
     * @var \MASK\Mask\Utility\GeneralUtility
     * @Inject()
     */
    protected $generalUtility;

    /**
     * Utility
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    public function initializeArguments()
    {
        $this->registerArgument('fieldKey', 'string', 'TCA Type', true);
        $this->registerArgument('elementKey', 'string', 'TCA Type', true);
        $this->registerArgument('property', 'string', 'value to search for', true);
        $this->registerArgument('field', 'array', '');
    }

    /**
     * Returns value from jsopenParams
     *
     * @return boolean $evalValue is set
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render()
    {
        $fieldKey = $this->arguments['fieldKey'];
        $elementKey = $this->arguments['elementKey'];
        $property = $this->arguments['property'];
        $field = $this->arguments['field'];

        $this->generalUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Utility\\GeneralUtility');
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');

        if ($field) {
            if ($field["inlineParent"]) {
                $type = $field["inlineParent"];
                $fieldKey = "tx_mask_" . $field["key"];
            } else {
                $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey, true);
            }
        } else {
            $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey);
        }
        return $this->generalUtility->getJsOpenParamValue($fieldKey, $property, $type);
    }
}
