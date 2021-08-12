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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationLoader implements ConfigurationLoaderInterface
{
    protected $tabs = [];
    protected $fieldGroups = [];
    protected $tcaFields = [];
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
