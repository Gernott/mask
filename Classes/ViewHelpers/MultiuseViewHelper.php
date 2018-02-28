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
class MultiuseViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @inject
     */
    protected $fieldHelper;

    /**
     * Returns all elements that use this field
     *
     * @param string $key TCA Type
     * @param string $elementKey Key of the element
     * @return array elements in use
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render($key, $elementKey)
    {
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $type = $this->fieldHelper->getFieldType($key, $elementKey);
        return $this->fieldHelper->getElementsWhichUseField($key, $type);
    }
}
