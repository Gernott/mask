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

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        ?string $commaSeparatedPaths = null,
        bool $removeExtension = false
    ): string {
        if ($commaSeparatedPaths === null) {
            $paths = static::getAbsolutePaths($settings['content']);
        } else {
            $paths = static::getAbsolutePaths($commaSeparatedPaths);
        }
        if (count($paths) === 0 || $elementKey === '') {
            return '';
        }

        foreach ($paths as $path) {
            $path = rtrim($path, '/') . '/';
            $fileExtension = '.html';

            // check if a html file with underscores exist
            $exists = false;
            if (file_exists($path . GeneralUtility::underscoredToUpperCamelCase($elementKey) . $fileExtension)) {
                $fileName = GeneralUtility::underscoredToUpperCamelCase($elementKey);
                $exists = true;
            } elseif (file_exists($path . ucfirst($elementKey) . $fileExtension)) {
                $fileName = ucfirst($elementKey);
                $exists = true;
            } elseif (file_exists($path . $elementKey . $fileExtension)) {
                $fileName = $elementKey;
                $exists = true;
            } else {
                $fileName = GeneralUtility::underscoredToUpperCamelCase($elementKey);
            }

            if ($removeExtension) {
                $fileExtension = '';
            }

            if ($exists) {
                if ($onlyTemplateName) {
                    return $fileName . $fileExtension;
                }
                return $path . $fileName . $fileExtension;
            }
        }

        //non-existing template file
        if ($onlyTemplateName) {
            return $fileName . $fileExtension;
        }
        return $path . $fileName . $fileExtension;
    }

    /**
     * Split a string of comma-separated paths and make them absolute.
     * Remove empty paths.
     *
     * @return string[]
     */
    public static function getAbsolutePaths(string $commaSeparatedPaths): array
    {
        $paths = GeneralUtility::trimExplode(',', $commaSeparatedPaths);
        foreach ($paths as $key => $path) {
            $paths[$key] = GeneralUtility::getFileAbsFileName($path);
        }
        return array_filter($paths);
    }

    /**
     * Split a string of comma-separated paths into an array.
     * Remove empty values.
     *
     * @return string[]
     */
    public static function getPaths(string $commaSeparatedPaths): array
    {
        $paths = GeneralUtility::trimExplode(',', $commaSeparatedPaths);
        return array_filter($paths);
    }
}
