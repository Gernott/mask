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
class LabelViewHelper extends AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    public function initializeArguments()
    {
        $this->registerArgument('elementKey', 'string', 'Key of element', true);
        $this->registerArgument('fieldKey', 'string', 'Key of field', true);
        $this->registerArgument('field', 'array', 'Whole field to better determine correct label');
        $this->registerArgument('table', 'string', 'tt_content or pages to better determine correct label');
    }

    /**
     * Returns the label of a field in an element
     */
    public function render(): string
    {
        $elementKey = $this->arguments['elementKey'];
        $fieldKey = $this->arguments['fieldKey'];
        $field = $this->arguments['field'];
        $table = $this->arguments['table'];

        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        // if we have the whole field configuration
        if ($field) {
            // check if this field is in an repeating field
            if ($field["inlineParent"]) {
                // if yes, the label is in the configuration
                $label = $field["label"];
            } else {
                // otherwise the type can only be tt_content or pages
                if ($table) {
                    // if we have table param, the type must be the table
                    $type = $table;
                } else {
                    // otherwise try to get the label, set param $excludeInlineFields to true
                    $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey, true);
                }
                $label = $this->fieldHelper->getLabel($elementKey, $fieldKey, $type);
            }
        } else {
            // if we don't have the field configuration, try the best to fetch the type and the correct label
            $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey, false);
            $label = $this->fieldHelper->getLabel($elementKey, $fieldKey, $type);
        }
        return $label;
    }
}
