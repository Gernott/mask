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
use MASK\Mask\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class ContentElementIconProvider implements IconProviderInterface
{

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * settings
     *
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(
        TableDefinitionCollection $tableDefinitionCollection,
        array $maskExtensionConfiguration,
        ResourceFactory $resourceFactory
    ) {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->resourceFactory = $resourceFactory;
    }

    public function prepareIconMarkup(Icon $icon, array $options = []): void
    {
        if (empty($options['contentElementKey'])) {
            throw new InvalidArgumentException('The option "contentElementKey" is required and must not be empty', 1440754978);
        }
        $icon->setMarkup($this->generateMarkup($options));
    }

    /**
     * Renders the actual icon
     */
    protected function generateMarkup(array $options): string
    {
        $styles = [];
        $element = $this->tableDefinitionCollection->getTable('tt_content')->elements->getElement($options['contentElementKey']);
        $previewIconAvailable = $this->isPreviewIconAvailable($options['contentElementKey']);
        $fontAwesomeKeyAvailable = trim($element->icon) !== '';

        // decide what kind of icon to render
        if ($fontAwesomeKeyAvailable && !$previewIconAvailable) {
            $color = $this->getColor($element->color);

            if ($color !== '') {
                $styles[] = 'color: #' . $color;
            }

            if (empty($styles)) {
                return '<span class="icon-unify" ><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($element->icon)) . '"></i></span>';
            }
            return '<span class="icon-unify" style="' . implode('; ', $styles) . '"><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($element->icon)) . '"></i></span>';
        }

        if ($previewIconAvailable) {
            return '<img src="' . str_replace(Environment::getPublicPath(), '', $this->getPreviewIconPath($options['contentElementKey'])) . '" alt="' . $element->label . '" title="' . $element->label . '"/>';
        }

        $color = $this->getColor($element->color);
        if ($color !== '') {
            $styles[] = 'background-color: #' . $color;
        }
        $styles[] = 'color: #fff';

        return '<span class="icon-unify mask-default-icon" style="' . implode('; ', $styles) . '">' . mb_substr($element->label, 0, 1) . '</span>';
    }

    /**
     * Checks if a preview icon is available in defined folder
     */
    protected function isPreviewIconAvailable(string $key): bool
    {
        return $this->getPreviewIconPath($key) !== '';
    }

    protected function getPreviewIconPath(string $key): string
    {
        if (!($this->maskExtensionConfiguration['preview'] ?? false)) {
            return '';
        }
        // search a fitting png or svg file in this path
        $fileExtensions = ['png', 'svg'];
        $previewPath = rtrim($this->maskExtensionConfiguration['preview'], '/');
        foreach ($fileExtensions as $fileExtension) {
            try {
                $icon = $this->resourceFactory->retrieveFileOrFolderObject($previewPath . '/' . $key . '.' . $fileExtension);
            } catch (InvalidArgumentException|FolderDoesNotExistException $e) {
                continue;
            }
            if ($icon instanceof File) {
                return '/' . ltrim($icon->getPublicUrl(), '/');
            }
        }

        return '';
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
