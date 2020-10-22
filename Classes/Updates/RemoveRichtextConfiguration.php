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

use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class RemoveRichtextConfiguration implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'removeRichtextConfiguration';
    }

    public function getTitle(): string
    {
        return 'Remove "richtextConfiguration" in mask.json';
    }

    public function getDescription(): string
    {
        return 'Since TYPO3 v10 TCA overrides PageTS CKEditor presets. This update wizard removes all presets defined in mask.json.';
    }

    public function executeUpdate(): bool
    {
        $storage = GeneralUtility::makeInstance(StorageRepository::class);
        $json = $storage->load();
        foreach ($json as $tableKey => $table) {
            foreach ($table['tca'] ?? [] as $fieldKey => $field) {
                if (isset($field['config']['richtextConfiguration'])) {
                    unset($json[$tableKey]['tca'][$fieldKey]['config']['richtextConfiguration']);
                }
            }
        }
        $storage->write($json);
        return true;
    }

    public function updateNecessary(): bool
    {
        $storage = GeneralUtility::makeInstance(StorageRepository::class);
        $json = $storage->load();
        foreach ($json as $table) {
            foreach ($table['tca'] ?? [] as $field) {
                if (isset($field['config']['richtextConfiguration'])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
