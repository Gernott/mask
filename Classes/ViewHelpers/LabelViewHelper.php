<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Helper\FieldHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LabelViewHelper extends AbstractViewHelper
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

        // if we have the whole field configuration
        if ($field) {
            // check if this field is in an repeating field
            if (isset($field['inlineParent']) && !is_array($field['inlineParent'])) {
                // if yes, the label is in the configuration
                $label = $field['label'];
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
