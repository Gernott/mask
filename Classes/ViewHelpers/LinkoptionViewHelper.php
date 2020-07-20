<?php
declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell <bb@webprofil.at>
 *
 */
class LinkoptionViewHelper extends AbstractViewHelper
{

    /**
     * Utility
     *
     * @var GeneralUtility
     * @Inject()
     */
    protected $generalUtility;

    /**
     * Utility
     *
     * @var FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    public function initializeArguments(): void
    {
        $this->registerArgument('fieldKey', 'string', 'TCA Type', true);
        $this->registerArgument('elementKey', 'string', 'Key of element', true);
        $this->registerArgument('evalValue', 'string', 'value to search for', true);
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @return boolean $evalValue is set
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render(): bool
    {
        $fieldKey = $this->arguments['fieldKey'];
        $elementKey = $this->arguments['elementKey'];
        $evalValue = $this->arguments['evalValue'];

        $this->generalUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GeneralUtility::class);
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FieldHelper::class);

        $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey);
        return $this->generalUtility->isBlindLinkOptionSet($fieldKey, $evalValue, $type);
    }
}
