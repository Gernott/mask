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
        $this->registerArgument('invert', 'bool', 'Invert checkbox', false, 0);
        $this->registerArgument('default', 'string', 'default on or off?', false, 'off');
        $this->registerArgument('valueOff', 'string', 'off value', false, '0');
        $this->registerArgument('valueOn', 'string', 'on value', false, '1');
        $this->registerArgument('readOnly', 'bool', 'readonly', false, false);
        $this->registerArgument('value', 'string', 'current value');
        $this->registerArgument('eval', 'bool', 'is eval field');
        $this->registerArgument('link', 'bool', 'is link field');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $name = $arguments['name'];
        $eval = $arguments['eval'];
        $link = $arguments['link'];
        $path = '[storage][tca][--index--]';
        if ($eval || $link) {
            $path = '[dummy][--index--]';
        }
        $hashName = str_replace(['[', ']'], '', $name);
        $value = (string)$arguments['value'];
        $valueOn = $arguments['valueOn'];
        $valueOff = $arguments['valueOff'];
        $editMode = $arguments['editMode'];
        $invert = $arguments['invert'] ? ' checkbox-invert' : '';
        $readOnly = $arguments['readOnly'] ? ' disabled' : '';
        $disabled = $arguments['readOnly'] ? ' disabled="disabled"' : '';

        // Default values for new fields
        $default = $arguments['default'];
        if ((int)($default === 'on') xor (int)$invert) {
            $checked = !$editMode || ($value === $valueOn);
        } else {
            $checked = $editMode && ($value === $valueOn);
        }

        $checkedAttr = $checked ? ' checked' : '';
        $checkedValue = $checked ? $valueOn : $valueOff;

        $random = random_int(0, mt_getrandmax());
        $hash = $hashName . substr(md5($random . $name), 0, 9);
        $label = LocalizationUtility::translate($arguments['translationKey'], 'mask');

        $switchFunction = "this.checked?'$valueOn':'$valueOff';";
        $classTag = '';
        if ($eval) {
            $classTag = 'class="tx_mask_fieldcontent_eval"';
        }
        if ($link) {
            $classTag = 'class="tx_mask_fieldcontent_link"';
        }

        $html = [];
        $html[] = '<div class="js-update-id form-check form-switch checkbox checkbox-type-toggle' . $invert . $readOnly . ' ">';
        $html[] = '<input id="' . $hash . '" type="checkbox" class="form-check-input checkbox-input" value="1" onclick="document.getElementById(\'' . $hash . '_hidden\').value=' . $switchFunction . '"' . $checkedAttr . $disabled . '>';
        $html[] = '<label class="form-check-label checkbox-label" for="' . $hash . '">';
        $html[] = '<span class="form-check-label-text checkbox-label-text">' . $label . '</span>';
        $html[] = '</label>';
        $html[] = '<input id="' . $hash . '_hidden" ' . $classTag . ' type="hidden" name="tx_mask_tools_maskmask' . $path . $name . '" value="' . $checkedValue . '"/>';
        $html[] = '</div>';

        return implode("\r\n", $html);
    }
}
