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

use MASK\Mask\Utility\TemplatePathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class PreviewIconResolver
{
    /**
     * @var array<string, string>
     */
    protected array $maskExtensionConfiguration;

    public function __construct(
        array $maskExtensionConfiguration
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
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
        $previewPaths = TemplatePathUtility::getPaths($this->maskExtensionConfiguration['preview']);
        if (!count($previewPaths)) {
            return '';
        }
        // search a fitting png or svg file in this path
        $fileExtensions = ['png', 'svg'];
        foreach ($previewPaths as $previewPath) {
            $previewPath = rtrim($previewPath, '/');
            foreach ($fileExtensions as $fileExtension) {
                $extPathToIcon = $previewPath . '/' . $key . '.' . $fileExtension;
                $absolutePathToIcon = GeneralUtility::getFileAbsFileName($extPathToIcon);
                if ($absolutePathToIcon === '' || !file_exists($absolutePathToIcon)) {
                    continue;
                }
                $resource = PathUtility::getPublicResourceWebPath($extPathToIcon);
                return '/' . ltrim($resource, '/');
            }
        }

        return '';
    }
}
