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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CheckboxViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'name of checkbox', true);
        $this->registerArgument('editMode', 'bool', 'edit mode', true);
        $this->registerArgument('translationKey', 'string', 'Translation for Label', true);
        $this->registerArgument('invert', 'bool', 'Invert checkbox', false, false);
        $this->registerArgument('default', 'int', 'default value', false, 0);
        $this->registerArgument('value', 'int', 'default value');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $name = $arguments['name'];
        $hashName = str_replace(['[', ']'], '', $name);
        $value = $arguments['value'];
        $editMode = $arguments['editMode'];
        $invert = $arguments['invert'] ? ' checkbox-invert' : '';

        // Default values for new fields
        $default = $arguments['default'];
        if ($default === 1) {
            $checked = !$editMode || $value;
        } else {
            $checked = $editMode && $value;
        }

        $checkedAttr = $checked ? ' checked' : '';
        $checkedValue = $checked ? '1' : '0';

        $random = mt_rand();
        $hash = $hashName . substr(md5($random . $name), 0, 9);
        $label = LocalizationUtility::translate($arguments['translationKey'], 'mask');

        $html[] = '<div class="js-update-id checkbox checkbox-type-toggle' . $invert . '">';
        $html[] = '<input id="' . $hash . '" type="checkbox" class="checkbox-input" value="1" onclick="document.getElementById(\'' . $hash . '_hidden\').value=this.checked?1:0;"' . $checkedAttr . '>';
        $html[] = '<label class="checkbox-label" for="' . $hash . '">';
        $html[] = '<span class="checkbox-label-text">' . $label . '</span>';
        $html[] = '</label>';
        $html[] = '<input id="' . $hash . '_hidden" type="hidden" name="tx_mask_tools_maskmask[storage][tca][--index--][config]' . $name . '" value="' . $checkedValue . '"/>';
        $html[] = '</div>';

        return implode("\r\n", $html);
    }
}
