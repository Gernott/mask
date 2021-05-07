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

use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreUtility;

/**
 * General useful methods
 */
class GeneralUtility
{

    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    public function __construct(StorageRepository $storageRepository)
    {
        $this->storageRepository = $storageRepository;
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @param string $fieldKey TCA Type
     * @param string $evalValue value to search for
     * @param string $type elementtype
     * @return bool $evalValue is set
     */
    public function isEvalValueSet($fieldKey, $evalValue, $type = 'tt_content'): bool
    {
        $storage = $this->storageRepository->load();
        $found = false;
        if (isset($storage[$type]['tca'][$fieldKey]['config']['eval'])) {
            $evals = explode(',', $storage[$type]['tca'][$fieldKey]['config']['eval']);
            foreach ($evals as $index => $eval) {
                $evals[$index] = strtolower($eval);
            }
            $found = in_array(strtolower($evalValue), $evals, true);
        }
        return $found;
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @param string $fieldKey TCA Type
     * @param string $evalValue value to search for
     * @param string $type elementtype
     * @return bool $evalValue is set
     */
    public function isBlindLinkOptionSet($fieldKey, $evalValue, $type = 'tt_content'): bool
    {
        $storage = $this->storageRepository->load();
        $found = false;
        if (isset($storage[$type]['tca'][$fieldKey]['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'])) {
            $evals = explode(
                ',',
                $storage[$type]['tca'][$fieldKey]['config']['fieldControl']['linkPopup']['options']['blindLinkOptions']
            );
            $found = in_array(strtolower($evalValue), $evals, true);
        }
        return $found;
    }

    /**
     * Searches an array of strings and returns the first string, that is not a tab
     * @param array $fields
     * @return string $string
     */
    public static function getFirstNoneTabField($fields): string
    {
        if (count($fields)) {
            $potentialFirst = array_shift($fields);
            if (!is_string($potentialFirst) || strpos($potentialFirst, '--div--') !== false) {
                return self::getFirstNoneTabField($fields);
            }
            return $potentialFirst;
        }
        return '';
    }

    /**
     * Removes all the blank options from the tca
     * @param array $haystack
     * @return array
     */
    public static function removeBlankOptions($haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = self::removeBlankOptions($haystack[$key]);
            }
            if ((is_array($haystack[$key]) && empty($haystack[$key])) || (is_string($haystack[$key]) && $haystack[$key] === '')) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }

    /**
     * Check which template path to return
     *
     * @param $settings
     * @param $elementKey
     * @param bool $onlyTemplateName
     * @param null $path
     * @return string
     */
    public static function getTemplatePath(
        $settings,
        $elementKey,
        $onlyTemplateName = false,
        $path = null
    ): string {
        $elementKey = (string)$elementKey;
        if (!$path) {
            $path = self::getFileAbsFileName(rtrim($settings['content'], '/') . '/');
        }
        if ($path === '' || $elementKey === '') {
            return '';
        }
        $fileExtension = '.html';

        // check if an html file with underscores exist
        if (file_exists($path . CoreUtility::underscoredToUpperCamelCase($elementKey) . $fileExtension)) {
            $fileName = CoreUtility::underscoredToUpperCamelCase($elementKey);
        } elseif (file_exists($path . ucfirst($elementKey) . $fileExtension)) {
            $fileName = ucfirst($elementKey);
        } elseif (file_exists($path . $elementKey . $fileExtension)) {
            $fileName = $elementKey;
        } else {
            $fileName = CoreUtility::underscoredToUpperCamelCase($elementKey);
        }

        if ($onlyTemplateName) {
            return $fileName . $fileExtension;
        }
        return $path . $fileName . $fileExtension;
    }

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
    public static function getFileAbsFileName($filename): string
    {
        if ((string)$filename === '') {
            return '';
        }
        // Extension
        if (strpos($filename, 'EXT:') === 0) {
            [$extKey, $local] = explode('/', substr($filename, 4), 2);
            $filename = '';
            if ((string)$extKey !== '' && (string)$local !== '') {
                $filename = Environment::getPublicPath() . '/typo3conf/ext/' . $extKey . '/' . $local;
            }
        } elseif (!CoreUtility::isAbsPath($filename)) {
            // is relative. Prepended with the public web folder
            $filename = Environment::getPublicPath() . '/' . $filename;
        } elseif (!(
            CoreUtility::isFirstPartOfStr($filename, Environment::getProjectPath())
            || CoreUtility::isFirstPartOfStr($filename, Environment::getPublicPath())
        )) {
            // absolute, but set to blank if not allowed
            $filename = '';
        }
        if ((string)$filename !== '' && CoreUtility::validPathStr($filename)) {
            // checks backpath.
            return $filename;
        }
        return '';
    }
}
