<?php
declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Helper\FieldHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @var FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    public function initializeArguments(): void
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
    public function render(): array
    {
        $key = $this->arguments['key'];
        $elementKey = $this->arguments['elementKey'];

        $this->fieldHelper = GeneralUtility::makeInstance(FieldHelper::class);
        $type = $this->fieldHelper->getFieldType($key, $elementKey);

        return $this->fieldHelper->getElementsWhichUseField($key, $type);
    }
}
