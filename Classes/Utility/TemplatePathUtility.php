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

namespace MASK\Mask\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility as CoreUtility;

/**
 * Finds the correct template name for the given element key.
 * Backwards compatibility given for non-UpperCamelCase filenames.
 * @internal
 */
class TemplatePathUtility
{
    /**
     * Check which template path to return
     */
    public static function getTemplatePath(
        array $settings,
        string $elementKey,
        bool $onlyTemplateName = false,
        ?string $path = null,
        bool $removeExtension = false
    ): string {
        if ($path === null) {
            $path = GeneralUtility::getFileAbsFileName(rtrim($settings['content'] ?? '', '/') . '/');
        }
        if ($path === '' || $elementKey === '') {
            return '';
        }
        $path = rtrim($path, '/') . '/';
        $fileExtension = '.html';

        // check if a html file with underscores exist
        if (file_exists($path . CoreUtility::underscoredToUpperCamelCase($elementKey) . $fileExtension)) {
            $fileName = CoreUtility::underscoredToUpperCamelCase($elementKey);
        } elseif (file_exists($path . ucfirst($elementKey) . $fileExtension)) {
            $fileName = ucfirst($elementKey);
        } elseif (file_exists($path . $elementKey . $fileExtension)) {
            $fileName = $elementKey;
        } else {
            $fileName = CoreUtility::underscoredToUpperCamelCase($elementKey);
        }

        if ($removeExtension) {
            $fileExtension = '';
        }

        if ($onlyTemplateName) {
            return $fileName . $fileExtension;
        }
        return $path . $fileName . $fileExtension;
    }
}
