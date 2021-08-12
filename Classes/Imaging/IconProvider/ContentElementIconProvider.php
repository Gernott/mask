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

namespace MASK\Mask\Imaging\IconProvider;

use InvalidArgumentException;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

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
        $icon->setMarkup($this->generateMarkup($options));
    }

    /**
     * Renders the actual icon
     */
    protected function generateMarkup(array $options): string
    {
        $styles = [];
        $previewIconAvailable = $this->isPreviewIconAvailable($options['contentElementKey']);
        $fontAwesomeKeyAvailable = $this->isFontAwesomeKeyAvailable($this->contentElement);

        // decide what kind of icon to render
        if ($fontAwesomeKeyAvailable && !$previewIconAvailable) {
            $color = $this->getColor($this->contentElement['color']);

            if ($color) {
                $styles[] = 'color: #' . $color;
            }
            if (count($styles)) {
                $markup = '<span class="icon-unify" style="' . implode(
                    '; ',
                    $styles
                ) . '"><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement['icon'])) . '"></i></span>';
            } else {
                $markup = '<span class="icon-unify" ><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($this->contentElement['icon'])) . '"></i></span>';
            }
        } else {
            if ($previewIconAvailable) {
                $markup = '<img src="' . str_replace(
                    Environment::getPublicPath(),
                    '',
                    $this->getPreviewIconPath($options['contentElementKey'])
                ) . '" alt="' . $this->contentElement['label'] . '" title="' . $this->contentElement['label'] . '"/>';
            } else {
                $color = $this->getColor($this->contentElement['color']);
                if ($color) {
                    $styles[] = 'background-color: #' . $color;
                }
                $styles[] = 'color: #fff';
                $markup = '<span class="icon-unify mask-default-icon" style="' . implode(
                    '; ',
                    $styles
                ) . '">' . mb_substr($this->contentElement['label'], 0, 1) . '</span>';
            }
        }

        return $markup;
    }

    /**
     * Checks if a preview icon is available in defined folder
     */
    protected function isPreviewIconAvailable(string $key): bool
    {
        return file_exists($this->getPreviewIconPath($key));
    }

    /**
     * Checks if content element has set a fontawesome key
     */
    protected function isFontAwesomeKeyAvailable(array $element): bool
    {
        return isset($element['icon']) && trim($element['icon']) !== '';
    }

    protected function getPreviewIconPath(string $key): string
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
     */
    protected function getFontAwesomeKey(string $icon): string
    {
        return trim(str_replace('fa-', '', $icon));
    }

    /**
     * returns trimmed and unified hex-code
     */
    protected function getColor(string $color): string
    {
        return trim(str_replace('#', '', $color));
    }
}
