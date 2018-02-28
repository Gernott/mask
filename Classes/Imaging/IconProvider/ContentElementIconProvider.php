<?php

namespace MASK\Mask\Imaging\IconProvider;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Core\Utility\PathUtility;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
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

/**
 * @package mask
 * @author Benjamin Butschell <bb@webprofil.at>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 */
class ContentElementIconProvider implements IconProviderInterface
{

    /**
     * StorageRepository
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * contentElement definiton
     * @var array
     */
    protected $contentElement;

    /**
     * SettingsService
     *
     * @var \MASK\Mask\Domain\Service\SettingsService
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    /**
     *
     * @param Icon $icon
     * @param array $options
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function prepareIconMarkup(Icon $icon, array $options = array())
    {
        // error checking
        if (empty($options['contentElementKey'])) {
            throw new \InvalidArgumentException('The option "contentElementKey" is required and must not be empty', 1440754978);
        }
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
        $this->settingsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Service\\SettingsService');
        $this->extSettings = $this->settingsService->get();
        $this->contentElement = $this->storageRepository->loadElement("tt_content", $options["contentElementKey"]);
        $icon->setMarkup($this->generateMarkup($icon, $options));
    }

    /**
     * Renders the actual icon
     * @param Icon $icon
     * @param array $options
     * @return string
     * @throws \InvalidArgumentException
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function generateMarkup(Icon $icon, array $options)
    {

        $previewIconAvailable = $this->isPreviewIconAvailable($options['contentElementKey']);
        $fontAwesomeKeyAvailable = $this->isFontAwesomeKeyAvailable($this->contentElement);


        // decide what kind of icon to render
        if ($fontAwesomeKeyAvailable) {

            $color = $this->getColor($this->contentElement);
            if ($color) {
                $styles[] = "color: #" . $color;
            }
            if (count($styles)) {
                $markup = '<span class="icon-unify" style="' . implode("; ", $styles) . '"><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement)) . '"></i></span>';
            } else {
                $markup = '<span class="icon-unify" ><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement)) . '"></i></span>';
            }
        } else if ($previewIconAvailable) {
            $markup = '<img src="' . PathUtility::getAbsoluteWebPath(PATH_site . ltrim($this->getPreviewIconPath($options['contentElementKey']), '/')) . '" alt="' . $this->contentElement["label"] . '" title="' . $this->contentElement["label"] . '"/>';
        } else {
//			$markup = '<img src="/typo3conf/ext/mask/Resources/Public/Icons/mask-ce-default.png" alt="' . $this->contentElement["label"] . '" title="' . $this->contentElement["label"] . '"/>';

            $color = $this->getColor($this->contentElement);
            if ($color) {
                $styles[] = "background-color: #" . $color;
            }
            $styles[] = "color: #fff";
            $markup = '<span class="icon-unify mask-default-icon" style="' . implode("; ", $styles) . '">' . substr($this->contentElement["label"], 0, 1) . '</span>';
        }

        return $markup;
    }

    /**
     * Checks if a preview icon is available in defined folder
     * @param string $key
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return boolean
     */
    protected function isPreviewIconAvailable($key)
    {
        if (file_exists(PATH_site . $this->getPreviewIconPath($key))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if content element has set a fontawesome key
     * @param array $element
     * @author Benjamin Butschell <bb@webprofil.at>
     * @todo implement
     * @return boolean
     */
    protected function isFontAwesomeKeyAvailable($element)
    {
        return trim($element["icon"]) != "";
    }

    /**
     * @param string $key
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return string
     */
    protected function getPreviewIconPath($key)
    {
        return $this->extSettings["preview"] . 'ce_' . $key . '.png';
    }

    /**
     * returns trimmed and unified font-awesome key
     * @param array $element
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return string
     */
    protected function getFontAwesomeKey($element)
    {
        return trim(str_replace("fa-", "", $element["icon"]));
    }

    /**
     * returns trimmed and unified hex-code
     * @param array $element
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return string
     */
    protected function getColor($element)
    {
        return trim(str_replace("#", "", $element["color"]));
    }
}
