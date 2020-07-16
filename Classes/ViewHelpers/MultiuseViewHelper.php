<?php

declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Helper\FieldHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 */
class MultiuseViewHelper extends AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var FieldHelper
     */
    protected $fieldHelper;

    public function __construct(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

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

        $type = $this->fieldHelper->getFieldType($key, $elementKey);

        return $this->fieldHelper->getElementsWhichUseField($key, $type);
    }
}
