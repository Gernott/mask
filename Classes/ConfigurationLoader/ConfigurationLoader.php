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

namespace MASK\Mask\ConfigurationLoader;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /**
     * @var array
     */
    protected $tabs = [];

    /**
     * @var array
     */
    protected $fieldGroups = [];

    /**
     * @var array
     */
    protected $tcaFields = [];

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @param string $tab
     * @return array
     */
    public function loadTab(string $tab): array
    {
        if (isset($this->tabs[$tab])) {
            return $this->tabs[$tab];
        }
        $tabConfiguration = require GeneralUtility::getFileAbsFileName('EXT:mask/Configuration/Mask/Tabs/' . $tab . '.php');
        $this->tabs[$tab] = $tabConfiguration;
        return $tabConfiguration;
    }

    /**
     * @return array
     */
    public function loadFieldGroups(): array
    {
        if (!empty($this->fieldGroups)) {
            return $this->fieldGroups;
        }
        $this->fieldGroups = require GeneralUtility::getFileAbsFileName('EXT:mask/Configuration/Mask/FieldGroups.php');
        return $this->fieldGroups;
    }

    /**
     * @return array
     */
    public function loadTcaFields(): array
    {
        if (!empty($this->tcaFields)) {
            return $this->tcaFields;
        }
        $this->tcaFields = require GeneralUtility::getFileAbsFileName('EXT:mask/Configuration/Mask/TcaFields.php');

        $typo3Version = new Typo3Version();

        // levelLinksPosition "none" deprecated since TYPO3 v11.
        if ($typo3Version->getMajorVersion() > 10) {
            unset($this->tcaFields['config.appearance.levelLinksPosition']['items']['none']);
        }

        // Remove options not available in current TYPO3 version.
        $documentationBase = 'https://docs.typo3.org/m/typo3/reference-tca/' . $typo3Version->getBranch() . '/en-us/';
        foreach ($this->tcaFields as $key => $tcaField) {
            $isAvailable = true;
            if (array_key_exists('version', $tcaField)) {
                $availableVersion = (string)$tcaField['version'];
                $availableVersionConstraint = explode(' ', $availableVersion);
                if (isset($availableVersionConstraint[1])) {
                    $isAvailable = (bool)version_compare((string)$typo3Version->getMajorVersion(), $availableVersionConstraint[1], $availableVersionConstraint[0]);
                } else {
                    $isAvailable = version_compare((string)$typo3Version->getMajorVersion(), $availableVersionConstraint[0], '=');
                }
            }

            if (!$isAvailable) {
                unset($this->tcaFields[$key]);
                continue;
            }

            // Set documentation link
            if (isset($tcaField['documentation'][$typo3Version->getMajorVersion()])) {
                $this->tcaFields[$key]['documentation'] = $documentationBase . $tcaField['documentation'][$typo3Version->getMajorVersion()];
            } elseif ($tcaField['collision'] ?? false) {
                unset($tcaField['collision']);
                foreach ($tcaField as $fieldType => $fieldTypeConfig) {
                    if (isset($fieldTypeConfig['documentation'][$typo3Version->getMajorVersion()])) {
                        $this->tcaFields[$key][$fieldType]['documentation'] = $documentationBase . $fieldTypeConfig['documentation'][$typo3Version->getMajorVersion()];
                    } else {
                        $this->tcaFields[$key][$fieldType]['documentation'] = '';
                    }
                }
            } else {
                $this->tcaFields[$key]['documentation'] = '';
            }
        }

        return $this->tcaFields;
    }

    /**
     * @return array
     */
    public function loadDefaults(): array
    {
        if (!empty($this->defaults)) {
            return $this->defaults;
        }
        $this->defaults = require GeneralUtility::getFileAbsFileName('EXT:mask/Configuration/Mask/Defaults.php');
        return $this->defaults;
    }
}
