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

use MASK\Mask\Definition\ElementDefinitionCollection;
use MASK\Mask\Definition\PaletteDefinitionCollection;
use MASK\Mask\Definition\SqlDefinition;
use MASK\Mask\Definition\TableDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaDefinition;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Migrations\MigrationManager;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonSplitLoader implements LoaderInterface
{
    /**
     * @var TableDefinitionCollection|null
     */
    protected $tableDefinitionCollection;

    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var MigrationManager
     */
    protected $migrationManager;

    private const FOLDER_KEYS = [
        'tt_content' => 'content_elements_folder',
        'pages' => 'backend_layouts_folder',
    ];

    public function __construct(
        array $maskExtensionConfiguration,
        MigrationManager $migrationManager
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->migrationManager = $migrationManager;
    }

    public function load(): TableDefinitionCollection
    {
        if ($this->tableDefinitionCollection instanceof TableDefinitionCollection) {
            return clone $this->tableDefinitionCollection;
        }

        $this->tableDefinitionCollection = new TableDefinitionCollection();

        // Early return, if the content elements folder does not exist.
        $contentElementsFolder = $this->validateFolderPath('tt_content');
        if (!file_exists($contentElementsFolder)) {
            return clone $this->tableDefinitionCollection;
        }

        $definitionArray = [];
        $definitionArray = $this->mergeElementDefinitions($definitionArray, $contentElementsFolder);

        // If optional backendLayoutsFolder is not empty, validate the path.
        $backendLayoutsFolder = $this->getAbsolutePath('pages');
        if ($backendLayoutsFolder !== '') {
            $backendLayoutsFolder = $this->validateFolderPath('pages');
            if (file_exists($backendLayoutsFolder)) {
                $definitionArray = $this->mergeElementDefinitions($definitionArray, $backendLayoutsFolder);
            }
        }

        // If the definition array is empty, skip the factory method of TableDefinitionCollection.
        if ($definitionArray !== []) {
            $this->tableDefinitionCollection = TableDefinitionCollection::createFromArray($definitionArray);
        }

        $this->tableDefinitionCollection = $this->migrationManager->migrate($this->tableDefinitionCollection);

        return clone $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tableDefinitionCollection): void
    {
        // Write content elements and backend layouts (if a folder is defined).
        $this->writeElementsForTable($tableDefinitionCollection, 'tt_content');
        if ($this->getAbsolutePath('pages') !== '') {
            $this->writeElementsForTable($tableDefinitionCollection, 'pages');
        }

        // Save new definition in memory.
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

    protected function validateFolderPath(string $table): string
    {
        $path = $this->getAbsolutePath($table);
        if ($path === '' && isset($this->maskExtensionConfiguration[self::FOLDER_KEYS[$table]]) && $this->maskExtensionConfiguration[self::FOLDER_KEYS[$table]] !== '') {
            throw new \InvalidArgumentException('Expected ' . self::FOLDER_KEYS[$table] . ' to be a correct file system path. The value "' . $path . '" was given.', 1639218892);
        }

        return $path;
    }

    protected function getPath(string $table): string
    {
        return $this->maskExtensionConfiguration[self::FOLDER_KEYS[$table]] ?? '';
    }

    protected function getAbsolutePath(string $table): string
    {
        return MaskUtility::getFileAbsFileName($this->getPath($table));
    }

    protected function mergeElementDefinitions(array &$definitionArray, string $folder): array
    {
        foreach ((new Finder())->files()->in($folder) as $file) {
            if ($file->getFileInfo()->getExtension() !== 'json') {
                continue;
            }
            // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
            $json = json_decode($file->getContents(), true, 512, 4194304);
            ArrayUtility::mergeRecursiveWithOverrule($definitionArray, $json);
        }
        return $definitionArray;
    }

    protected function writeElementsForTable(TableDefinitionCollection $tableDefinitionCollection, string $table): void
    {
        if (!$tableDefinitionCollection->hasTable($table)) {
            return;
        }

        $absolutePath = $this->validateFolderPath($table);

        if (!file_exists($absolutePath)) {
            GeneralUtility::mkdir_deep($absolutePath);
        }

        $elements = [];
        foreach ($tableDefinitionCollection->getTable($table)->elements as $element) {
            $elements[] = $element->key;
        }

        // Delete removed elements
        foreach ((new Finder())->files()->in($absolutePath) as $file) {
            if ($file->getFileInfo()->getExtension() !== 'json') {
                continue;
            }

            if (!in_array($file->getFilenameWithoutExtension(), $elements, true)) {
                unlink($file->getPathname());
            }
        }

        $tableDefinition = $tableDefinitionCollection->getTable($table);
        $sorting = 0;
        foreach ($tableDefinition->elements as $element) {
            $elementTableDefinitionCollection = new TableDefinitionCollection();
            $newElementsDefinitionCollection = new ElementDefinitionCollection();
            $newTableDefinition = new TableDefinition();
            $newTcaDefinition = new TcaDefinition();
            $newPaletteDefinitionCollection = new PaletteDefinitionCollection();
            $newSqlDefinition = new SqlDefinition();
            $newTableDefinition->table = $table;
            $newPaletteDefinitionCollection->table = $table;
            $newTcaDefinition->table = $table;
            $newSqlDefinition->table = $table;
            $newElementsDefinitionCollection->table = $table;

            // If element had no sorting before, add it here.
            if ($sorting > 0 && $element->sorting === 0) {
                $element->sorting = $sorting;
            }

            $newElementsDefinitionCollection->addElement($element);
            $sorting += 1;

            foreach ($element->columns as $column) {
                $field = $tableDefinition->tca->getField($column);
                $newTcaDefinition->addField($field);
                if ($tableDefinition->sql->hasColumn($field->fullKey)) {
                    $newSqlDefinition->addColumn($tableDefinition->sql->getColumn($field->fullKey));
                }
                $fieldType = $tableDefinitionCollection->getFieldType($field->fullKey, $table);
                if ($fieldType->equals(FieldType::INLINE)) {
                    $this->addInlineRecursive($field, $elementTableDefinitionCollection, $tableDefinitionCollection);
                }
                if ($fieldType->equals(FieldType::PALETTE)) {
                    $paletteDefinition = $tableDefinition->palettes->getPalette($field->fullKey);
                    $newPaletteDefinitionCollection->addPalette($paletteDefinition);
                    foreach ($paletteDefinition->showitem as $item) {
                        $paletteField = $tableDefinition->tca->getField($item);
                        $newTcaDefinition->addField($paletteField);
                        if ($tableDefinition->sql->hasColumn($paletteField->fullKey)) {
                            $newSqlDefinition->addColumn($tableDefinition->sql->getColumn($paletteField->fullKey));
                        }
                        if ($tableDefinitionCollection->getFieldType($paletteField->fullKey)->equals(FieldType::INLINE)) {
                            $elementTableDefinitionCollection->addTable($tableDefinitionCollection->getTable($paletteField->fullKey));
                        }
                    }
                }
            }
            $newTableDefinition->tca = $newTcaDefinition;
            $newTableDefinition->sql = $newSqlDefinition;
            $newTableDefinition->palettes = $newPaletteDefinitionCollection;
            $newTableDefinition->elements = $newElementsDefinitionCollection;
            $elementTableDefinitionCollection->addTable($newTableDefinition);

            // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
            $filePath = $absolutePath . '/' . $element->key . '.json';
            $result = GeneralUtility::writeFile($filePath, json_encode($elementTableDefinitionCollection->toArray(), 4194304 | JSON_PRETTY_PRINT) . "\n");

            if (!$result) {
                throw new \InvalidArgumentException('The file "' . $filePath . '" could not be written. Check your file permissions.', 1639169283);
            }
        }
    }

    protected function addInlineRecursive(TcaFieldDefinition $field, TableDefinitionCollection $elementTableDefinitionCollection, TableDefinitionCollection $tableDefinitionCollection): void
    {
        $elementTableDefinitionCollection->addTable($tableDefinitionCollection->getTable($field->fullKey));
        foreach ($tableDefinitionCollection->getTable($field->fullKey)->tca as $inlineChild) {
            $fieldType = $tableDefinitionCollection->getFieldType($inlineChild->fullKey, $field->fullKey);
            if ($fieldType->equals(FieldType::INLINE)) {
                $this->addInlineRecursive($inlineChild, $elementTableDefinitionCollection, $tableDefinitionCollection);
            }
        }
    }
}
