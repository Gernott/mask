<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell <bb@webprofil.at>
 *
 */
class LinkoptionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Utility
     *
     * @var \MASK\Mask\Utility\GeneralUtility
     * @inject
     */
    protected $generalUtility;

    /**
     * Utility
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @inject
     */
    protected $fieldHelper;

    /**
     * Checks if a $evalValue is set in a field
     *
     * @param string $fieldKey TCA Type
     * @param string $elementKey key of the element
     * @param string $evalValue value to search for
     * @return boolean $evalValue is set
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render($fieldKey, $elementKey, $evalValue)
    {
        $this->generalUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Utility\\GeneralUtility');
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');

        $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey);
        return $this->generalUtility->isBlindLinkOptionSet($fieldKey, $evalValue, $type);
    }
}
