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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Package\Exception as PackageException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * General useful methods
 * @internal
 * @todo Remove in Mask v8 and use TYPO3 core GeneralUtility instead.
 */
class GeneralUtility
{
    /**
     * Returns the absolute filename of a relative reference, resolves the "EXT:" prefix
     * (way of referring to files inside extensions) and checks that the file is inside
     * the TYPO3's base folder and implies a check with
     * \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr().
     *
     * "EXT:" prefix is also replaced if the extension is not installed
     *
     * @param string $filename The input filename/filepath to evaluate
     * @return string Returns the absolute filename of $filename if valid, otherwise blank string.
     */
    public static function getFileAbsFileName(string $filename): string
    {
        $originalFileName = $filename;
        if ($filename === '') {
            return '';
        }
        // Extension
        if (strpos($filename, 'EXT:') === 0) {
            [$extKey, $local] = explode('/', substr($filename, 4), 2);
            if ($extKey === '' || $local === '') {
                return '';
            }
            $filename = Environment::getPublicPath() . '/typo3conf/ext/' . $extKey . '/' . $local;
            // @todo Use TYPO3 GU::getFileAbsFileName method instead. A loaded extension should be required for extension paths.
            if (!ExtensionManagementUtility::isLoaded($extKey)) {
                trigger_error('Your sitepackage extension "' . $extKey . '" is not loaded. Mask v8 will require your extension to be loaded.', E_USER_DEPRECATED);
            }
            // This is for v11 with typo3/cms-composer-installers v4
            if (!file_exists($filename) && (new Typo3Version())->getMajorVersion() > 10) {
                try {
                    $filename = ExtensionManagementUtility::resolvePackagePath($originalFileName);
                } catch (PackageException $e) {
                    $filename = '';
                }
            }
        } elseif (!PathUtility::isAbsolutePath($filename)) {
            // is relative. Prepended with the public web folder
            $filename = Environment::getPublicPath() . '/' . $filename;
        } elseif (!(CompatibilityUtility::isFirstPartOfStr($filename, Environment::getProjectPath()) || CompatibilityUtility::isFirstPartOfStr($filename, Environment::getPublicPath()))) {
            // absolute, but set to blank if not allowed
            $filename = '';
        }
        if ($filename !== '' && CoreUtility::validPathStr($filename)) {
            // checks backpath.
            return $filename;
        }
        return '';
    }
}
