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
use MASK\Mask\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class EvalViewHelper extends AbstractViewHelper
{

    /**
     * Utility
     *
     * @var GeneralUtility
     */
    protected $generalUtility;

    /**
     * Utility
     *
     * @var FieldHelper
     */
    protected $fieldHelper;

    public function __construct(GeneralUtility $generalUtility, FieldHelper $fieldHelper)
    {
        $this->generalUtility = $generalUtility;
        $this->fieldHelper = $fieldHelper;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('fieldKey', 'string', 'TCA type', true);
        $this->registerArgument('elementKey', 'string', 'TCA type', true);
        $this->registerArgument('evalValue', 'string', 'Value to search for', true);
        $this->registerArgument('field', 'array', '');
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @return bool $evalValue is set
     */
    public function render(): bool
    {
        $fieldKey = $this->arguments['fieldKey'];
        $elementKey = $this->arguments['elementKey'];
        $evalValue = $this->arguments['evalValue'];
        $field = $this->arguments['field'];

        if ($field) {
            if ($field['inlineParent']) {
                if ($field['inPalette']) {
                    if (is_array($field['inlineParent'])) {
                        $inlineParent = $field['inlineParent'][$elementKey];
                    } else {
                        $inlineParent = $field['inlineParent'];
                    }
                    $type = $this->fieldHelper->getFieldType($inlineParent, $elementKey);
                } else {
                    $type = $field['inlineParent'];
                    $fieldKey = 'tx_mask_' . $field['key'];
                }
            } else {
                $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey, true);
            }
        } else {
            $type = $this->fieldHelper->getFieldType($fieldKey, $elementKey);
        }
        return $this->generalUtility->isEvalValueSet($fieldKey, $evalValue, $type);
    }
}
