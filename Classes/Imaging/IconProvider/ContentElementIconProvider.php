<?php
declare(strict_types=1);

namespace MASK\Mask\Imaging\IconProvider;

use InvalidArgumentException;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

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
     * @var StorageRepository
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
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    public function __construct(StorageRepository $storageRepository, SettingsService $settingsService)
    {
        $this->storageRepository = $storageRepository;
        $this->settingsService = $settingsService;
        $this->extSettings = $settingsService->get();
    }

    /**
     *
     * @param Icon $icon
     * @param array $options
     * @throws Exception
     */
    public function prepareIconMarkup(Icon $icon, array $options = []): void
    {
        // error checking
        if (empty($options['contentElementKey'])) {
            throw new InvalidArgumentException(
                'The option "contentElementKey" is required and must not be empty',
                1440754978
            );
        }
        $this->contentElement = $this->storageRepository->loadElement('tt_content', $options['contentElementKey']);
        $icon->setMarkup($this->generateMarkup($icon, $options));
    }

    /**
     * Renders the actual icon
     * @param Icon $icon
     * @param array $options
     * @return string
     * @throws InvalidArgumentException
     */
    protected function generateMarkup(Icon $icon, array $options): string
    {

        $previewIconAvailable = $this->isPreviewIconAvailable($options['contentElementKey']);
        $fontAwesomeKeyAvailable = $this->isFontAwesomeKeyAvailable($this->contentElement);

        // decide what kind of icon to render
        if ($fontAwesomeKeyAvailable && !$previewIconAvailable) {

            $color = $this->getColor($this->contentElement);
            $styles = [];

            if ($color) {
                $styles[] = 'color: #' . $color;
            }
            if (count($styles)) {
                $markup = '<span class="icon-unify" style="' . implode('; ',
                        $styles) . '"><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement)) . '"></i></span>';
            } else {
                $markup = '<span class="icon-unify" ><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement)) . '"></i></span>';
            }
        } else {
            if ($previewIconAvailable) {
                $markup = '<img src="' . str_replace(Environment::getPublicPath(), '',
                        $this->getPreviewIconPath($options['contentElementKey'])) . '" alt="' . $this->contentElement['label'] . '" title="' . $this->contentElement['label'] . '"/>';
            } else {
                $color = $this->getColor($this->contentElement);
                if ($color) {
                    $styles[] = 'background-color: #' . $color;
                }
                $styles[] = 'color: #fff';
                $markup = '<span class="icon-unify mask-default-icon" style="' . implode('; ',
                        $styles) . '">' . mb_substr($this->contentElement['label'], 0, 1) . '</span>';
            }
        }

        return $markup;
    }

    /**
     * Checks if a preview icon is available in defined folder
     * @param string $key
     * @return boolean
     */
    protected function isPreviewIconAvailable($key): bool
    {
        return file_exists($this->getPreviewIconPath($key));
    }

    /**
     * Checks if content element has set a fontawesome key
     * @param array $element
     * @return boolean
     */
    protected function isFontAwesomeKeyAvailable($element): bool
    {
        return isset($element['icon']) && trim($element['icon']) !== '';
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getPreviewIconPath($key): string
    {
        // the path to the file
        $filePath = function ($key) {
            return MaskUtility::getFileAbsFileName(
                    rtrim($this->extSettings['preview'], '/') . '/'
                ) . $key . '.';
        };

        // search a fitting png or svg file in this path
        $fileExtensions = ['png', 'svg'];
        foreach ($fileExtensions as $fileExtension) {
            $iconPath = $filePath($key) . $fileExtension;
            if (file_exists($iconPath)) {
                return $iconPath;
            }
        }

        // if nothing found, return the path to the png file
        return $filePath($key) . '.png';
    }

    /**
     * returns trimmed and unified font-awesome key
     * @param array $element
     * @return string
     */
    protected function getFontAwesomeKey($element): string
    {
        return trim(str_replace('fa-', '', $element['icon']));
    }

    /**
     * returns trimmed and unified hex-code
     * @param array $element
     * @return string
     */
    protected function getColor($element): string
    {
        return trim(str_replace('#', '', $element['color']));
    }
}
