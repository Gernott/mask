<?php
declare(strict_types=1);

namespace MASK\Mask\Domain\Service;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides the settings users can make
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class SettingsService
{

    /**
     * Contains the settings of the current extension
     *
     * @var array
     * @api
     */
    protected $settings = [];

    /**
     * Contains the settings of the $_EXTCONF
     *
     * @var array
     */
    protected $extSettings;

    /**
     * Returns the settings
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function get(): array
    {
        if ($this->extSettings === null) {
            $this->extSettings = $this->getExtSettings();
        }

        return $this->extSettings;
    }

    /**
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getExtSettings(): array
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('mask');
    }
}
