<?php

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
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * Provides the settings users can make
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class SettingsService
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager = null;

    /**
     * Contains the settings of the $_EXTCONF
     *
     * @var array
     */
    protected $extSettings;

    /**
     * Returns the settings
     * @return array
     */
    public function get()
    {
        $this->extSettings = $this->getExtSettings();
        return $this->extSettings;
    }

    /**
     * Returns an array with the settings from $_EXTCONF
     * @return array
     */
    protected function getExtSettings()
    {
        $extSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
        if (empty($extSettings)) {
            $extSettings = $_EXTCONF;
        }
        return $extSettings;
    }

    /**
     * Returns an array with backend specific paths, based on typoscript settings
     * @return array
     */
    public function getBackendSettings() {
        $extSettings = $this->getExtensionTyposcriptSettings();

        $settings = array();
        $settings['backend'] = $this->setTemplate($extSettings['settings']['beview'], 'template');
        $settings['layouts_backend'] = $this->setTemplate($extSettings['settings']['beview'], 'layout');
        $settings['partials_backend'] = $this->setTemplate($extSettings['settings']['beview'], 'partial');
        $settings['preview'] = $this->setTemplate($extSettings['settings']['beview'], 'preview');
        $settings['content'] = $this->setTemplate($extSettings['settings']['beview'], 'content');

        return $settings;
    }

    /**
     * Returns an array with backend specific paths, based on typoscript settings
     * @return array
     */
    public function getFrontendSettings() {
        $extSettings = $this->getExtensionTyposcriptSettings();

        $settings = array();
        $settings['frontend'] = $this->setTemplate($extSettings['settings']['feview'], 'template');
        $settings['layouts'] = $this->setTemplate($extSettings['settings']['feview'], 'layout');
        $settings['partials'] = $this->setTemplate($extSettings['settings']['feview'], 'partial');

        return $settings;
    }

    /**
     * Set template for view (backend and frontend)
     *
     * @param $templatePaths
     * @param $templateKey
     *
     * @return string
     */
    public function setTemplate($templatePaths, $templateKey) {
        $possibleTemplatePaths = $templatePaths[$templateKey . 'RootPaths'];
        /*
        $templatePathAndFilename = null;
        foreach ($possibleTemplatePaths as $possibleTemplatePath) {
            if (is_dir(\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($possibleTemplatePath))) {
                $templatePathAndFilename = $possibleTemplatePath;
            }
        }
        */

        return $possibleTemplatePaths;
    }

    /**
     * Returns mask typoscript settings
     *
     * @return array
     */
    protected function getExtensionTyposcriptSettings() {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\Object\\ObjectManager');
        $configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
        $extSettings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'mask');
        return $extSettings;
    }
}
