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

use MASK\Mask\ConfigurationLoader\ConfigurationLoaderInterface;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use MASK\Mask\Utility\TcaConverter;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonLoader implements LoaderInterface
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(array $maskExtensionConfiguration, ConfigurationLoaderInterface $configurationLoader)
    {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->configurationLoader = $configurationLoader;
    }

    public function load(): TableDefinitionCollection
    {
        $maskJsonFilePath = $this->validateGetJsonFilePath();
        if ($this->tableDefinitionCollection === null) {
            $this->tableDefinitionCollection = new TableDefinitionCollection();
            // The file might not exist yet. Will be created as soon as write() is called.
            if (file_exists($maskJsonFilePath)) {
                $json = json_decode(file_get_contents($maskJsonFilePath), true, 512, 4194304); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
                $this->tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
            }
        }

        // Compatibility layer
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca as $tcaFieldDefinition) {
                if ($tcaFieldDefinition->isCoreField) {
                    continue;
                }
                // Add defaults, if missing.
                $tcaDefaults = $this->configurationLoader->loadDefaults()[(string)$tcaFieldDefinition->type];
                $tcaDefaults = TcaConverter::convertFlatTcaToArray($tcaDefaults['tca_out'] ?? []);
                ArrayUtility::mergeRecursiveWithOverrule($tcaFieldDefinition->realTca, $tcaDefaults);
            }
        }

        // Compatibility layer for old rte resolving
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->elements as $element) {
                if ($element->options === []) {
                    continue;
                }
                foreach ($element->options as $index => $option) {
                    if ($option === 'rte') {
                        trigger_error('Migration for options rte done in element "' . $element->key . '". Please update your json definition.', E_USER_DEPRECATED);
                        $fieldKey = $element->columns[$index];
                        $field = $this->tableDefinitionCollection->loadField($tableDefinition->table, $fieldKey);
                        if ($field instanceof TcaFieldDefinition) {
                            $field->type = new FieldType(FieldType::RICHTEXT);
                        }
                    }
                }
            }
        }

        return $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tableDefinitionCollection): void
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
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

        $result = GeneralUtility::writeFile($maskJsonFilePath, json_encode($tableDefinitionCollection->toArray(), 4194304 | JSON_PRETTY_PRINT)); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
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

        return MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['json']);
    }
}
