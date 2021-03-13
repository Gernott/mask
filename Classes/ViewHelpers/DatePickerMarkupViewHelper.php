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

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DatePickerMarkupViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('field', 'array', 'The field config array.', true);
        $this->registerArgument('name', 'string', 'The name for the input', true);
        $this->registerArgument('value', 'mixed', 'Value of the date', true);
        $this->registerArgument('key', 'string', 'The field key', true);
        $this->registerArgument('id', 'string', 'A unique id for the label toggling', true);
        $this->registerArgument('type', 'string', 'The type of the date (date or datetime)');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $evals = GeneralUtility::trimExplode(',', $arguments['field']['config']['eval']);
        $type = 'date';
        if (in_array('datetime', $evals)) {
            $type = 'datetime';
        }
        $type = $arguments['type'] ?? $type;
        $name = $arguments['name'];
        $value = $arguments['value'];
        $key = $arguments['key'];
        $id = $arguments['id'];
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() == 10) {
            return self::getMarkupForTYPO3Version10($type, $name, $value, $key, $id);
        } else {
            return self::getMarkupForTYPO3Version11($type, $name, $value, $key, $id);
        }
    }

    protected static function getMarkupForTYPO3Version11($type, $name, $value, $key, $id)
    {
        $dateIcon = self::getDateIconMarkup();
        return
<<<HEREDOC
            <div class="t3js-formengine-field-item">
                <div class="form-control-wrap">
                    <div class="form-wizards-wrap">
                        <div class="form-wizards-element">
                            <div class="input-group">
                                <div class="form-control-clearable form-control">
                                    <input
                                            id="timestamp-upper-$key-$id"
                                            class="t3js-datetimepicker form-control t3js-clearable flatpickr-input"
                                            data-date-type="$type"
                                            name="$name"
                                            value="$value"
                                    >
                                </div>
                                <input type="hidden">
                                <span class="input-group-btn">
                                    <label class="btn btn-default" for="timestamp-upper-$key-$id">
                                        $dateIcon
                                    </label>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
HEREDOC;
    }

    protected static function getMarkupForTYPO3Version10($type, $name, $value, $key, $id)
    {
        $dateIcon = self::getDateIconMarkup();
        return
<<<HEREDOC
            <div class="t3js-formengine-field-item">
                <div class="form-control-wrap">
                    <div class="input-group">
                        <input
                            id="timestamp-upper-$key-$id"
                            class="t3js-datetimepicker form-control t3js-clearable"
                            data-date-type="$type"
                            name="$name"
                            value="$value"
                        >
                        <span class="input-group-btn">
                            <label class="btn btn-default" for="timestamp-upper-$key-$id">
                                $dateIcon
                            </label>
                        </span>
                    </div>
                </div>
            </div>
HEREDOC;
    }

    protected static function getDateIconMarkup()
    {
        /** @var IconFactory $iconRepository */
        $iconRepository = GeneralUtility::makeInstance(IconFactory::class);
        return $iconRepository->getIcon('actions-edit-pick-date', Icon::SIZE_SMALL)->render();
    }
}
