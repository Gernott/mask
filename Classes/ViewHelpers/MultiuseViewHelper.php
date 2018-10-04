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
class MultiuseViewHelper extends AbstractViewHelper
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
        $this->registerArgument('key', 'string', 'TCA Type', true);
        $this->registerArgument('elementKey', 'string', 'Key of element', true);
    }

    /**
     * Returns all elements that use this field
     *
     * @return array elements in use
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render()
    {
        $key = $this->arguments['key'];
        $elementKey = $this->arguments['elementKey'];

        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $type = $this->fieldHelper->getFieldType($key, $elementKey);

        return $this->fieldHelper->getElementsWhichUseField($key, $type);
    }
}
