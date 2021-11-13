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

    public function toArray(): array
    {
        return array_merge([], ...$this->getTablesAsArray());
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
        foreach ($tableDefinitionArray as $table => $definition) {
            $tableDefinition = TableDefinition::createFromTableArray($table, $definition);
            $tableDefinitionCollection->addTable($tableDefinition);
        }

        return $tableDefinitionCollection;
    }

    /**
     * @return iterable<TableDefinition>
     */
    public function getIterator(): iterable
    {
        foreach ($this->definitions as $definition) {
            yield clone $definition;
        }
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
        return new ElementTcaDefinition($element, $tableDefinition->tca);
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     * Not specifying an element key means, the parent key has to be an inline table.
     *
     * @param string $parentKey
     * @param string $elementKey
     * @return NestedTcaFieldDefinitions
     */
    public function loadInlineFields(string $parentKey, string $elementKey): NestedTcaFieldDefinitions
    {
        $nestedTcaFields = new NestedTcaFieldDefinitions($elementKey);

        // Load inline fields of own table
        if ($this->hasTable($parentKey)) {
            $searchTable = $this->getTable($parentKey);
            if (!$searchTable) {
                return $nestedTcaFields;
            }
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

                // This can be called very early, so ignore core fields.
                if (!$field->isCoreField) {
                    $fieldType = $this->getFieldType($field->fullKey, $tableDefinition->table);
                    if ($fieldType->isParentField()) {
                        foreach ($this->loadInlineFields($field->fullKey, $elementKey) as $inlineField) {
                            $field->addInlineField($inlineField);
                        }
                    }
                }

                $nestedTcaFields->addField($field);
            }
        }

        return $nestedTcaFields;
    }

    public function getFieldType(string $fieldKey, string $table = 'tt_content'): FieldType
    {
        return FieldType::cast($this->getFieldTypeString($fieldKey, $table));
    }

    /**
     * Returns the formType of a field in an element
     * @internal
     */
    public function getFieldTypeString(string $fieldKey, string $table = 'tt_content'): string
    {
        // @todo Allow bodytext to be normal TEXT field.
        if ($fieldKey === 'bodytext' && $table === 'tt_content') {
            return FieldType::RICHTEXT;
        }

        $fieldDefinition = $this->loadField($table, $fieldKey);

        if ($fieldDefinition !== null && !$fieldDefinition->isCoreField) {
            // If type is already known, return it.
            if ($fieldDefinition->type) {
                return (string)$fieldDefinition->type;
            }

            return FieldTypeUtility::getFieldType($fieldDefinition->toArray(), $fieldDefinition->fullKey);
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
                if ($this->getFieldType($column, $table)->equals(FieldType::PALETTE)) {
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
        if (!$this->hasTable($table)) {
            return '';
        }
        $tableDefinition = $this->getTable($table);

        if (!$tableDefinition->tca->hasField($fieldKey)) {
            return '';
        }

        // If this field is in a repeating field or palette, the label is in the field configuration.
        $field = $tableDefinition->tca->getField($fieldKey);
        if ($field->hasInlineParent()) {
            if (empty($field->labelByElement)) {
                return $field->label;
            }
            if (isset($field->labelByElement[$elementKey])) {
                return $field->labelByElement[$elementKey];
            }
        }

        // Root level fields have their labels defined in element labels array.
        $elements = $tableDefinition->elements;
        if (!$elements->hasElement($elementKey)) {
            return '';
        }
        $element = $elements->getElement($elementKey);
        if (!empty($element->columns)) {
            $fieldIndex = array_search($fieldKey, $element->columns, true);
            if ($fieldIndex !== false) {
                return $element->labels[$fieldIndex];
            }
        }

        return '';
    }

    /**
     * Returns the description of a field in an element
     */
    public function getDescription(string $elementKey, string $fieldKey, string $table = 'tt_content'): string
    {
        if (!$this->hasTable($table)) {
            return '';
        }
        $tableDefinition = $this->getTable($table);

        if (!$tableDefinition->tca->hasField($fieldKey)) {
            return '';
        }

        // If this field is in a repeating field or palette, the label is in the field configuration.
        $field = $tableDefinition->tca->getField($fieldKey);
        if ($field->hasInlineParent()) {
            if (empty($field->descriptionByElement)) {
                return $field->description;
            }
            if (isset($field->descriptionByElement[$elementKey])) {
                return $field->descriptionByElement[$elementKey];
            }
        }

        // Root level fields have their labels defined in element labels array.
        $elements = $tableDefinition->elements;
        if (!$elements->hasElement($elementKey)) {
            return '';
        }
        $element = $elements->getElement($elementKey);
        if (!empty($element->columns)) {
            $fieldIndex = array_search($fieldKey, $element->columns, true);
            if ($fieldIndex !== false) {
                return $element->descriptions[$fieldIndex];
            }
        }

        return '';
    }

    /**
     * This method searches for an existing label of a multiuse field
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        if (!$this->hasTable($table)) {
            return '';
        }
        $definition = $this->getTable($table);

        $label = '';
        foreach ($definition->elements as $element) {
            if (in_array($key, $element->columns, true)) {
                $label = $element->labels[array_search($key, $element->columns, true)];
            } else {
                $field = $definition->tca->getField($key);
                if ($field) {
                    $label = $field->labelByElement[$element->key] ?? '';
                }
            }
            if ($label !== '') {
                break;
            }
        }
        return $label;
    }
}
