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

namespace MASK\Mask\Imaging;

use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class PreviewIconResolver
{
    /**
     * Mask extension settings
     *
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(
        array $maskExtensionConfiguration,
        ResourceFactory $resourceFactory
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Checks if a preview icon is available in defined folder
     */
    public function isPreviewIconAvailable(string $key): bool
    {
        return $this->getPreviewIconPath($key) !== '';
    }

    public function getPreviewIconPath(string $key): string
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
            } catch (\InvalidArgumentException|FolderDoesNotExistException|ResourceDoesNotExistException $e) {
                continue;
            }
            if ($icon instanceof File && $icon->exists()) {
                return '/' . ltrim($icon->getPublicUrl(), '/');
            }
        }

        return '';
    }
}
