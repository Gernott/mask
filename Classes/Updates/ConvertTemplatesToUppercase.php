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

namespace MASK\Mask\Updates;

use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class ConvertTemplatesToUppercase implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'convertTemplatesToUppercase';
    }

    public function getTitle(): string
    {
        return 'Convert Mask templates to uppercase.';
    }

    public function getDescription(): string
    {
        return 'This update wizard converts snake_case template names to UpperCamelCase to comply with Fluid standards.';
    }

    public function executeUpdate(): bool
    {
        foreach ($this->getTemplates() as $file) {
            $name = $file->getFilename();
            $upperCase = GeneralUtility::underscoredToUpperCamelCase($name);
            rename(
                $file->getPath() . '/' . $name,
                $file->getPath() . '/' . $upperCase
            );
        }
        return true;
    }

    public function updateNecessary(): bool
    {
        return count($this->getTemplates() ?: []) > 0;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    protected function getTemplates(): ?Finder
    {
        $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('mask');

        if (($settings['content'] ?? '') === '') {
            return null;
        }

        if (strpos($settings['content'], 'EXT:') === 0) {
            $absolutePath = MaskUtility::getFileAbsFileName($settings['content']);
        } else {
            $absolutePath = Environment::getPublicPath() . '/' . $settings['content'];
        }
        try {
            return (new Finder())->files()->in($absolutePath)->filter(function (SplFileInfo $info) {
                $filename = $info->getFilename();
                return ctype_lower(substr($filename, 0, 1)) || strpos($filename, '_') !== false;
            });
        } catch (DirectoryNotFoundException $e) {
            // do nothing
        }

        return null;
    }
}
