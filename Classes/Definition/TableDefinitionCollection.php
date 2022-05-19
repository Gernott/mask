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

namespace MASK\Mask\Definition;

use InvalidArgumentException;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\FieldTypeUtility;

final class TableDefinitionCollection implements \IteratorAggregate
{
    /**
     * @var array<TableDefinition>
     */
    private $definitions = [];

    /**
     * @var ArrayDefinitionSorter
     */
    private $arrayDefinitionSorter;

    /**
     * @var string
     */
    private $version = '7.2.0';

    /**
     * @var bool
     */
    private $migrationDone = false;

    public function __construct()
    {
        $this->arrayDefinitionSorter = new ArrayDefinitionSorter();
        $this->arrayDefinitionSorter->setExcludedKeys(
            [
                'itemGroups',
            ]
        );
    }

    public function __clone()
    {
        $this->definitions = array_map(function (TableDefinition $tableDefinition) {
            return clone $tableDefinition;
        }, $this->definitions);
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setToCurrentVersion(): void
    {
        $this->version = (new self())->getVersion();
    }

    public function getMigrationDone(): bool
    {
        return $this->migrationDone;
    }

    public function migrationDone(): void
    {
        $this->migrationDone = true;
    }

    public function addTable(TableDefinition $tableDefinition): void
    {
        if (!$this->hasTable($tableDefinition->table)) {
            $this->definitions[$tableDefinition->table] = $tableDefinition;
        }
    }

    public function getTable(string $table): TableDefinition
    {
        if ($this->hasTable($table)) {
            return $this->definitions[$table];
        }
        throw new \OutOfBoundsException(sprintf('The table "%s" does not exist.', $table), 1628925803);
    }

    public function hasTable(string $table): bool
    {
        return isset($this->definitions[$table]);
    }

    /**
     * @param bool $withVersion Compatibility flag. Can be set to false, in order to only get the tables array.
     * @return array
     */
    public function toArray(bool $withVersion = true): array
    {
        $tablesArray = array_merge([], ...$this->getTablesAsArray());
        $tablesArray = $this->arrayDefinitionSorter->sort($tablesArray);

        if (!$withVersion) {
            return $tablesArray;
        }

        return [
            'version' => $this->version,
            'tables' => $tablesArray,
        ];
    }

    public function getTablesAsArray(): iterable
    {
        foreach ($this->definitions as $definition) {
            yield [$definition->table => $definition->toArray()];
        }
    }

    /**
     * @return iterable<TableDefinition>
     */
    public function getCustomTables(): iterable
    {
        foreach ($this->definitions as $tableDefinition) {
            if (AffixUtility::hasMaskPrefix($tableDefinition->table)) {
                yield $tableDefinition;
            }
        }
    }

    public static function createFromArray(array $tableDefinitionArray): TableDefinitionCollection
    {
        $tableDefinitionCollection = new self();
        if (array_key_exists('version', $tableDefinitionArray)) {
            $tableDefinitionCollection->version = $tableDefinitionArray['version'];
            $tables = $tableDefinitionArray['tables'] ?? [];
        } else {
            // Fallback for definitions before the introduction of version.
            $tableDefinitionCollection->version = '0.1.0';
            $tables = $tableDefinitionArray;
        }
        foreach ($tables as $table => $definition) {
            $tableDefinition = TableDefinition::createFromTableArray($table, $definition);
            $tableDefinitionCollection->addTable($tableDefinition);
        }

        return $tableDefinitionCollection;
    }

    /**
     * @return \Traversable|TableDefinition[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->definitions);
    }

    /**
     * Load Field
     */
    public function loadField(string $table, string $fieldName): ?TcaFieldDefinition
    {
        if (!$this->hasTable($table)) {
            return null;
        }

        $tableDefinition = $this->getTable($table);

        if (!$tableDefinition->tca->hasField($fieldName)) {
            return null;
        }

        return $tableDefinition->tca->getField($fieldName);
    }

    /**
     * Load Element with all the field configurations
     */
    public function loadElement(string $table, string $key): ?ElementTcaDefinition
    {
        // Only tt_content and pages can have elements
        if (!in_array($table, ['tt_content', 'pages'])) {
            return null;
        }

        if (!$this->hasTable($table)) {
            return null;
        }

        $tableDefinition = $this->getTable($table);
        $elements = $tableDefinition->elements;
        if (!$elements->hasElement($key)) {
            return null;
        }

        $element = $elements->getElement($key);
        $tcaDefinition = new TcaDefinition();
        foreach ($element->columns as $fieldKey) {
            if ($tableDefinition->tca->hasField($fieldKey)) {
                $availableTcaField = $tableDefinition->tca->getField($fieldKey);
                $tcaDefinition->addField($availableTcaField);
                if ($availableTcaField->hasFieldType() && $availableTcaField->getFieldType()->equals(FieldType::PALETTE)) {
                    $paletteFields = $this->loadInlineFields($availableTcaField->fullKey, $element->key);
                    foreach ($paletteFields as $paletteField) {
                        $tcaDefinition->addField($paletteField);
                    }
                }
            }
        }

        return new ElementTcaDefinition($element, $tcaDefinition);
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     * Not specifying an element key means, the parent key has to be an inline table.
     */
    public function loadInlineFields(string $parentKey, string $elementKey): NestedTcaFieldDefinitions
    {
        $nestedTcaFields = new NestedTcaFieldDefinitions($elementKey);

        // Load inline fields of own table
        if ($this->hasTable($parentKey)) {
            $searchTable = $this->getTable($parentKey);
            $searchTables = [$searchTable];
        } else {
            $searchTables = $this->definitions;
        }

        // Traverse tables and find palette
        foreach ($searchTables as $tableDefinition) {
            foreach ($tableDefinition->tca as $field) {
                /** @todo Remove compatibility layer in Mask v8.0 */
                try {
                    if (!$field->hasInlineParent($elementKey) || $field->getInlineParent($elementKey) !== $parentKey) {
                        continue;
                    }
                } catch (InvalidArgumentException $e) {
                    trigger_error(
                        'Not specifying the element key in method TableDefinitionCollection->loadInlineFields, will not work in Mask v8.0 anymore.',
                        E_USER_DEPRECATED
                    );
                    continue;
                }

                // Check if FieldType is available
                if ($field->hasFieldType() && $field->getFieldType()->isParentField()) {
                    foreach ($this->loadInlineFields($field->fullKey, $elementKey) as $inlineField) {
                        $field->addInlineField($inlineField);
                    }
                }

                $nestedTcaFields->addField($field);
            }
        }

        return $nestedTcaFields;
    }

    public function getFieldType(string $fieldKey, string $table = 'tt_content', string $elementKey = ''): FieldType
    {
        return FieldType::cast($this->getFieldTypeString($fieldKey, $table, $elementKey));
    }

    /**
     * Returns the formType of a field in an element
     * @internal
     */
    public function getFieldTypeString(string $fieldKey, string $table = 'tt_content', string $elementKey = ''): string
    {
        $fieldDefinition = $this->loadField($table, $fieldKey);

        if ($fieldDefinition instanceof TcaFieldDefinition) {
            // If type is already known, return it.
            if ($fieldDefinition->hasFieldType($elementKey)) {
                return (string)$fieldDefinition->getFieldType($elementKey);
            }

            try {
                return FieldTypeUtility::getFieldType($fieldDefinition->toArray(), $fieldDefinition->fullKey);
            } catch (InvalidArgumentException $e) {
                // For core fields this exception might pop up, because in older
                // Mask versions no type was defined directly in the definition.
            }
        }

        // If field could not be found in field definition, check for global TCA.
        $tca = $GLOBALS['TCA'][$table]['columns'][$fieldKey] ?? [];
        return FieldTypeUtility::getFieldType($tca, $fieldKey);
    }

    /**
     * Returns type of field (tt_content or pages)
     */
    public function getTableByField(string $fieldKey, string $elementKey = '', bool $excludeInlineFields = false): string
    {
        foreach ($this->definitions as $table) {
            if ($excludeInlineFields && !in_array($table->table, ['tt_content', 'pages'], true)) {
                continue;
            }
            if ($table->tca->hasField($fieldKey) && AffixUtility::hasMaskPrefix($table->table)) {
                return $table->table;
            }
            foreach ($table->elements as $element) {
                // If element key is set, ignore all other elements
                if ($elementKey !== '' && ($elementKey !== $element->key)) {
                    continue;
                }
                if ($table->tca->hasField($fieldKey) || in_array($fieldKey, $element->columns, true)) {
                    return $table->table;
                }
            }
        }

        return '';
    }

    /**
     * Returns all elements that use this field
     */
    public function getElementsWhichUseField(string $key, string $table = 'tt_content'): ElementDefinitionCollection
    {
        $elementsInUse = new ElementDefinitionCollection($table);
        if (!$this->hasTable($table)) {
            return $elementsInUse;
        }

        $definition = $this->getTable($table);
        foreach ($definition->elements as $element) {
            foreach ($element->columns as $column) {
                if ($column === $key) {
                    $elementsInUse->addElement($element);
                    break;
                }
                $fieldDefinition = $this->loadField($table, $column);
                if ($fieldDefinition instanceof TcaFieldDefinition && $fieldDefinition->hasFieldType() && $fieldDefinition->getFieldType()->equals(FieldType::PALETTE)) {
                    foreach ($definition->palettes->getPalette($column)->showitem as $item) {
                        if ($item === $key) {
                            $elementsInUse->addElement($element);
                            break;
                        }
                    }
                }
            }
        }
        return $elementsInUse;
    }

    /**
     * Returns the label of a field in an element
     */
    public function getLabel(string $elementKey, string $fieldKey, string $table = 'tt_content'): string
    {
        return $this->getFieldPropertyByElement($elementKey, $fieldKey, 'label', $table);
    }

    /**
     * Returns the description of a field in an element
     */
    public function getDescription(string $elementKey, string $fieldKey, string $table = 'tt_content'): string
    {
        return $this->getFieldPropertyByElement($elementKey, $fieldKey, 'description', $table);
    }

    /**
     * This method can find properties, which are defined in the ElementDefinition.
     * These are "label" and "description" at the time being.
     */
    private function getFieldPropertyByElement(string $elementKey, string $fieldKey, string $property, string $table = 'tt_content'): string
    {
        $validProperties = ['label', 'description'];

        if (!in_array($property, $validProperties)) {
            throw new InvalidArgumentException('The property ' . $property . ' is not a valid. Valid properties are: ' . implode(' ', $validProperties) . '.', 1636825949);
        }

        if (!$this->hasTable($table)) {
            return '';
        }
        $tableDefinition = $this->getTable($table);

        if (!$tableDefinition->tca->hasField($fieldKey)) {
            return '';
        }

        // If this field is in a repeating field or palette, the description is in the field configuration.
        $field = $tableDefinition->tca->getField($fieldKey);
        if ($field->hasInlineParent()) {
            if (empty($field->{$property . 'ByElement'})) {
                return $field->{$property};
            }
            if (isset($field->{$property . 'ByElement'}[$elementKey])) {
                return $field->{$property . 'ByElement'}[$elementKey];
            }
        }

        // BC: If root field still has property defined directly, take it.
        if ($field->{$property} !== '') {
            return $field->{$property};
        }

        // Root level fields have their properties defined in according element array.
        $elements = $tableDefinition->elements;
        if (!$elements->hasElement($elementKey)) {
            return '';
        }
        $element = $elements->getElement($elementKey);
        if (!empty($element->columns)) {
            $fieldIndex = array_search($fieldKey, $element->columns, true);
            if ($fieldIndex !== false) {
                return $element->{$property . 's'}[$fieldIndex] ?? '';
            }
        }

        return '';
    }

    /**
     * This method searches for an existing label of a multiuse field
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        return $this->findFirstNonEmptyProperty($table, $key, 'label');
    }

    /**
     * This method searches for an existing description of a multiuse field
     */
    public function findFirstNonEmptyDescription(string $table, string $key): string
    {
        return $this->findFirstNonEmptyProperty($table, $key, 'description');
    }

    private function findFirstNonEmptyProperty(string $table, string $key, string $propertyName): string
    {
        if (!$this->hasTable($table)) {
            return '';
        }
        $definition = $this->getTable($table);

        $property = '';
        foreach ($definition->elements as $element) {
            if (in_array($key, $element->columns, true)) {
                $property = $element->{$propertyName . 's'}[array_search($key, $element->columns, true)];
            } else {
                $field = $definition->tca->getField($key);
                $property = $field->{$propertyName . 'ByElement'}[$element->key] ?? '';
            }
            if ($property !== '') {
                break;
            }
        }
        return $property;
    }
}
