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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper that translates a language label, if the result is empty, the label will be returned.
 *
 * Example:
 * <mask:translateLabel key="{key}" />
 */
class TranslateLabelViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'mixed', '', true);
        $this->registerArgument('element', 'string', '', true);
        $this->registerArgument('extensionName', 'string', '');
    }

    /**
     * The given key will be translated. If the result is empty, the key will be returned.
     *
     * @return string
     */
    public function render(): string
    {
        $key = $this->arguments['key'];
        $element = $this->arguments['element'];
        $extensionName = $this->arguments['extensionName'];

        if (is_array($key)) {
            return $key[$element] ?? '';
        }

        if (empty($key) || strpos($key, 'LLL') > 0) {
            return $key;
        }

        $request = $this->renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName ?? $request->getControllerExtensionName();
        $result = LocalizationUtility::translate($key, $extensionName);
        return empty($result) ? $key : $result;
    }
}
