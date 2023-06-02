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

namespace MASK\Mask\Loader;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Migrations\MigrationManager;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonLoader implements LoaderInterface
{
    protected ?TableDefinitionCollection $tableDefinitionCollection = null;
    protected array $maskExtensionConfiguration;
    protected MigrationManager $migrationManager;
    protected Features $features;

    public function __construct(
        array $maskExtensionConfiguration,
        MigrationManager $migrationManager,
        Features $features
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->migrationManager = $migrationManager;
        $this->features = $features;
    }

    public function load(): TableDefinitionCollection
    {
        if ($this->tableDefinitionCollection instanceof TableDefinitionCollection) {
            return clone $this->tableDefinitionCollection;
        }

        $this->tableDefinitionCollection = new TableDefinitionCollection();

        // Return an empty definition, if file doesn't exist yet.
        $maskJsonFilePath = $this->validateGetJsonFilePath();
        if (!file_exists($maskJsonFilePath)) {
            return clone $this->tableDefinitionCollection;
        }

        $json = json_decode(file_get_contents($maskJsonFilePath), true, 512, JSON_THROW_ON_ERROR);
        $this->tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);

        $this->tableDefinitionCollection = $this->migrationManager->migrate($this->tableDefinitionCollection);

        return clone $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tableDefinitionCollection): void
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;

        if ($this->features->isFeatureEnabled('overrideSharedFields')) {
            $this->tableDefinitionCollection->setRestructuringDone();
        }

        $maskJsonFilePath = $this->validateGetJsonFilePath();

        // Create folder for json file, if it doesn't exist.
        if (!file_exists($maskJsonFilePath)) {
            $maskJsonFolderPath = explode('/', $maskJsonFilePath);
            array_pop($maskJsonFolderPath);
            $maskJsonFolderPath = implode('/', $maskJsonFolderPath);
            if (!file_exists($maskJsonFolderPath)) {
                GeneralUtility::mkdir_deep($maskJsonFolderPath);
            }
        }

        $result = GeneralUtility::writeFile($maskJsonFilePath, json_encode($tableDefinitionCollection->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        if (!$result) {
            throw new \InvalidArgumentException('The Mask JSON file "' . $this->maskExtensionConfiguration['json'] . '" could not be written. Check your file permissions.', 1639169283);
        }
    }

    protected function validateGetJsonFilePath(): string
    {
        $maskJsonPath = $this->getJsonFilePath();
        if ($maskJsonPath === '' && isset($this->maskExtensionConfiguration['json']) && $this->maskExtensionConfiguration['json'] !== '') {
            throw new \InvalidArgumentException('Expected "json" to be a valid file path. The value "' . $this->maskExtensionConfiguration['json'] . '" was given.', 1639220370);
        }

        return $maskJsonPath;
    }

    protected function getJsonFilePath(): string
    {
        if (($this->maskExtensionConfiguration['json'] ?? '') === '') {
            return '';
        }

        return GeneralUtility::getFileAbsFileName($this->maskExtensionConfiguration['json']);
    }
}
