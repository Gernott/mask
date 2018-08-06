<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class FormTypeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @inject
     */
    protected $fieldHelper;

    /**
     * Returns the label of a field in an element
     *
     * @param string $elementKey Key of Element
     * @param string $fieldKey Key if Field
     * @param string $type table
     * @return string formType
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render($elementKey, $fieldKey, $type = "tt_content")
    {
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $formType = $this->fieldHelper->getFormType($fieldKey, $elementKey, $type);
        return $formType;
    }
}
