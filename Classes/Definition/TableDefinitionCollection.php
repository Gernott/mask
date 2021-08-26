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
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;

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
        $tcaDefinition = new self();
        foreach ($tableDefinitionArray as $table => $definition) {
            $tableDefinition = TableDefinition::createFromTableArray($table, $definition);
            $tcaDefinition->addTable($tableDefinition);
        }

        return $tcaDefinition;
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
                    $fieldType = $this->getFieldType($field->fullKey, $tableDefinition->table, $elementKey);
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

    /**
     * Returns the formType of a field in an element
     * @internal
     */
    public function getFormType(string $fieldKey, string $elementKey = '', string $table = 'tt_content'): string
    {
        // @todo Allow bodytext to be normal TEXT field.
        if ($fieldKey === 'bodytext' && $table === 'tt_content') {
            return FieldType::RICHTEXT;
        }

        $tca = [];

        $fieldDefinition = $this->loadField($table, $fieldKey);
        if ($fieldDefinition) {
            // If type is already known, return it. No check here, as this can be trusted.
            if ($fieldDefinition->type) {
                return (string)$fieldDefinition->type;
            }
            // Load the fields TCA, if not core field.
            if (!$fieldDefinition->isCoreField) {
                $tca = $fieldDefinition->toArray();
            }
        }

        // If TCA could not be resolved by Mask config, check for global TCA.
        if (empty($tca)) {
            $tca = $GLOBALS['TCA'][$table]['columns'][$fieldKey] ?? [];
        }

        // If TCA is still empty, error out.
        if (empty($tca)) {
            throw new InvalidArgumentException(sprintf('No TCA could be found for the field "%s" in the table "%s".', $fieldKey, $table), 1629484158);
        }

        // The tca "type" attribute has to be set. Can also be a fake one like "palette" or "linebreak".
        $tcaType = $tca['config']['type'] ?? '';
        if ($tcaType === '') {
            throw new InvalidArgumentException(sprintf('The TCA type attribute of the field "%s" in the table "%s" must not be empty.', $fieldKey, $table), 1629485122);
        }

        // And decide via different tca settings which formType it is
        switch ($tcaType) {
            case 'input':
                $evals = [];
                if (isset($tca['config']['eval'])) {
                    $evals = explode(',', $tca['config']['eval']);
                }
                if (($tca['config']['dbType'] ?? '') === 'date') {
                    return FieldType::DATE;
                }
                if (($tca['config']['dbType'] ?? '') === 'datetime') {
                    return FieldType::DATETIME;
                }
                if (($tca['config']['renderType'] ?? '') === 'inputDateTime') {
                    return FieldType::TIMESTAMP;
                }
                if (in_array('int', $evals, true)) {
                    return FieldType::INTEGER;
                }
                if (in_array('double2', $evals, true)) {
                    return FieldType::FLOAT;
                }
                if (($tca['config']['renderType'] ?? '') === 'inputLink') {
                    return FieldType::LINK;
                }
                return FieldType::STRING;
            case 'text':
                if (isset($tca['config']['enableRichtext'])) {
                    return FieldType::RICHTEXT;
                }
                // Compatibility for mask prior to v3
                // Load the element, if element key is provided and search for "rte" option.
                if ($elementKey !== '') {
                    $element = $this->loadElement($table, $elementKey);
                    if (!$element) {
                        throw new \InvalidArgumentException(sprintf('The element "%s" does not exist in the table "%s".', $elementKey, $table), 1629482680);
                    }
                    foreach ($element->elementDefinition->columns as $numberKey => $column) {
                        if ($column === $fieldKey) {
                            $option = $element->elementDefinition->options[$numberKey] ?? '';
                            if ($option === 'rte') {
                                return FieldType::RICHTEXT;
                            }
                        }
                    }
                }
                return FieldType::TEXT;
            case 'inline':
                if (($tca['config']['foreign_table'] ?? '') === 'sys_file_reference') {
                    return FieldType::FILE;
                }
                if (($tca['config']['foreign_table'] ?? '') === 'tt_content') {
                    return FieldType::CONTENT;
                }
                return FieldType::INLINE;
            default:
                // Check if fake tca type is valid.
                try {
                    return (string)FieldType::cast($tcaType);
                } catch (InvalidEnumerationValueException $e) {
                    throw new \InvalidArgumentException(sprintf('Could not resolve the form type of the field "%s" in the table "%s". Please check, if your TCA is correct.', $fieldKey, $table), 1629484452);
                }
        }
    }

    public function getFieldType(string $fieldKey, string $table = 'tt_content', string $elementKey = ''): FieldType
    {
        return FieldType::cast($this->getFormType($fieldKey, $elementKey, $table));
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
                if ($this->getFieldType($column, $table, $element->key)->equals(FieldType::PALETTE)) {
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
