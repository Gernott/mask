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
class FormTypeViewHelper extends AbstractViewHelper
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
        $this->registerArgument('fieldKey', 'string', 'Key if field', true);
        $this->registerArgument('type', 'string', 'Key of element', false, 'tt_content');
    }

    /**
     * Returns the label of a field in an element
     *
     * @return string formType
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render()
    {
        $elementKey = $this->arguments['elementKey'];
        $fieldKey = $this->arguments['fieldKey'];
        $type = $this->arguments['type'];

        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $formType = $this->fieldHelper->getFormType($fieldKey, $elementKey, $type);
        return $formType;
    }
}
