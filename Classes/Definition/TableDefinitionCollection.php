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

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;

final class TableDefinitionCollection implements \IteratorAggregate
{
    /**
     * @var array<TableDefinition>
     */
    protected $tableDefinitions = [];

    public function addTableDefinition(TableDefinition $tableDefinition): void
    {
        if (!$this->hasTableDefinition($tableDefinition->table)) {
            $this->tableDefinitions[$tableDefinition->table] = $tableDefinition;
        }
    }

    public function getTableDefiniton(string $table): TableDefinition
    {
        if ($this->hasTableDefinition($table)) {
            return $this->tableDefinitions[$table];
        }
        throw new \OutOfBoundsException(sprintf('The table "%s" does not exist.', $table), 1628925803);
    }

    public function hasTableDefinition(string $table): bool
    {
        if (isset($this->tableDefinitions[$table])) {
            return true;
        }
        return false;
    }

    public function toArray(): array
    {
        return array_merge([], ...$this->getTableDefinitionsAsArray());
    }

    public function getTableDefinitionsAsArray(): iterable
    {
        foreach ($this->tableDefinitions as $definition) {
            yield [$definition->table => $definition->toArray()];
        }
    }

    /**
     * @return iterable<TableDefinition>
     */
    public function getCustomTableDefinitions(): iterable
    {
        foreach ($this->tableDefinitions as $tableDefinition) {
            if (AffixUtility::hasMaskPrefix($tableDefinition->table)) {
                yield $tableDefinition;
            }
        }
    }

    public static function createFromInternalArray(array $tableDefinitionArray): TableDefinitionCollection
    {
        $tcaDefinition = new self();
        foreach ($tableDefinitionArray as $table => $value) {
            $elements = $value['elements'] ?? [];
            $sql = $value['sql'] ?? [];
            $tca = $value['tca'] ?? [];
            $palettes = $value['palettes'] ?? [];
            $tableDefinition = new TableDefinition($table, $tca, $sql, $elements, $palettes);
            $tcaDefinition->addTableDefinition($tableDefinition);
        }

        return $tcaDefinition;
    }

    /**
     * @return iterable<TableDefinition>
     */
    public function getIterator(): iterable
    {
        foreach ($this->tableDefinitions as $tableDefinition) {
            yield $tableDefinition;
        }
    }

    /**
     * Load Field
     */
    public function loadField(string $table, string $fieldName): array
    {
        if (!$this->hasTableDefinition($table)) {
            return [];
        }

        $tableDefinition = $this->getTableDefiniton($table);

        return $tableDefinition->tca[$fieldName] ?? [];
    }

    /**
     * Load Element with all the field configurations
     */
    public function loadElement(string $table, string $key): array
    {
        // Only tt_content and pages can have elements
        if (!in_array($table, ['tt_content', 'pages'])) {
            return [];
        }

        if (!$this->hasTableDefinition($table)) {
            return [];
        }

        $tableDefinition = $this->getTableDefiniton($table);

        $elements = $tableDefinition->elements;
        $columns = $elements[$key]['columns'] ?? [];

        if (!is_array($columns)) {
            return [];
        }

        $fields = [];
        foreach ($columns as $fieldName) {
            $fields[$fieldName] = $tableDefinition->tca[$fieldName] ?? [];
        }

        if (!empty($fields)) {
            $elements[$key]['tca'] = $fields;
        }

        return $elements[$key] ?? [];
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     */
    public function loadInlineFields(string $parentKey, string $elementKey = ''): array
    {
        $inlineFields = [];

        // Load inline fields of own table
        if ($this->hasTableDefinition($parentKey)) {
            $searchTable = $this->getTableDefiniton($parentKey);
            if (!$searchTable) {
                return [];
            }
            $searchTables = [$searchTable];
        } else {
            $searchTables = $this->tableDefinitions;
        }

        // Traverse tables and find palette
        foreach ($searchTables as $tableDefinition) {
            foreach ($tableDefinition->tca as $key => $tca) {
                // if inlineParent is an array, it's in a palette on default table
                if (is_array(($tca['inlineParent'] ?? ''))) {
                    $inlineParent = $tca['inlineParent'][$elementKey] ?? '';
                } else {
                    $inlineParent = $tca['inlineParent'] ?? '';
                }
                if ($inlineParent === $parentKey) {
                    $maskKey = AffixUtility::addMaskPrefix($tca['key']);
                    if ($this->getFormType($tca['key'], $elementKey, $tableDefinition->table) === FieldType::INLINE) {
                        $tca['inlineFields'] = $this->loadInlineFields($key, $elementKey);
                    }
                    if (($tca['config']['type'] ?? '') === FieldType::PALETTE) {
                        $tca['inlineFields'] = $this->loadInlineFields($maskKey, $elementKey);
                    }
                    $tca['maskKey'] = $maskKey;
                    $inlineFields[] = $tca;
                }
            }
        }

        $this->sortInlineFieldsByOrder($inlineFields, $elementKey);
        return $inlineFields;
    }

    /**
     * Returns the formType of a field in an element
     */
    public function getFormType(string $fieldKey, string $elementKey = '', string $table = 'tt_content'): string
    {
        // @todo Allow bodytext to be normal TEXT field.
        if ($fieldKey === 'bodytext' && $table === 'tt_content') {
            return FieldType::RICHTEXT;
        }

        $element = [];
        $maskKey = AffixUtility::addMaskPrefix($fieldKey);

        // Check if TCA for mask key exists, else assume it's a core field.
        $tca = $GLOBALS['TCA'][$table]['columns'][$maskKey] ?? [];
        if (!$tca) {
            $tca = $GLOBALS['TCA'][$table]['columns'][$fieldKey] ?? [];
        }

        if ($elementKey) {
            // Load element and TCA of field
            $element = $this->loadElement($table, $elementKey);
            if (array_key_exists('config', $tca) && !$tca['config']) {
                $tca = $element['tca'][$fieldKey] ?? [];
            }
        }

        // if field is in inline table or $GLOBALS["TCA"] is not yet filled, load tca from json
        if (!$tca || !in_array($table, ['tt_content', 'pages'])) {
            $tca = $this->loadField($table, $fieldKey);
            if (!($tca['config'] ?? false)) {
                $tca = $this->loadField($table, $maskKey);
            }
        }

        $tcaType = $tca['config']['type'] ?? '';
        $evals = [];
        if (isset($tca['config']['eval'])) {
            $evals = explode(',', $tca['config']['eval']);
        }

        if (($tca['options'] ?? '') === 'file') {
            return FieldType::FILE;
        }

        if (($tca['rte'] ?? '') === '1' || (int)($tca['config']['enableRichtext'] ?? '') === 1) {
            return FieldType::RICHTEXT;
        }

        // And decide via different tca settings which formType it is
        switch ($tcaType) {
            case 'input':
                if (($tca['config']['dbType'] ?? '') === 'date') {
                    $formType = FieldType::DATE;
                } elseif (($tca['config']['dbType'] ?? '') === 'datetime') {
                    $formType = FieldType::DATETIME;
                } elseif (($tca['config']['renderType'] ?? '') === 'inputDateTime') {
                    $formType = FieldType::TIMESTAMP;
                } elseif (in_array('int', $evals, true)) {
                    $formType = FieldType::INTEGER;
                } elseif (in_array('double2', $evals, true)) {
                    $formType = FieldType::FLOAT;
                } elseif (($tca['config']['renderType'] ?? '') === 'inputLink') {
                    $formType = FieldType::LINK;
                } else {
                    $formType = FieldType::STRING;
                }
                break;
            case 'text':
                $formType = FieldType::TEXT;
                if ($elementKey && in_array($table, ['tt_content', 'pages'])) {
                    foreach ($element['columns'] ?? [] as $numberKey => $column) {
                        if ($column === $fieldKey) {
                            $option = $element['options'][$numberKey] ?? '';
                            if ($option === 'rte') {
                                $formType = FieldType::RICHTEXT;
                            }
                        }
                    }
                }
                break;
            case 'inline':
                if (($tca['config']['foreign_table'] ?? '') === 'sys_file_reference') {
                    $formType = FieldType::FILE;
                } elseif (($tca['config']['foreign_table'] ?? '') === 'tt_content') {
                    $formType = FieldType::CONTENT;
                } else {
                    $formType = FieldType::INLINE;
                }
                break;
            default:
                try {
                    FieldType::cast($tcaType);
                    $formType = $tcaType;
                } catch (InvalidEnumerationValueException $e) {
                    return '';
                }
                break;
        }
        return $formType;
    }

    /**
     * Returns type of field (tt_content or pages)
     */
    public function getFieldType(string $fieldKey, string $elementKey = '', bool $excludeInlineFields = false): string
    {
        foreach ($this->tableDefinitions as $tableDefinition) {
            $table = $tableDefinition->table;
            if ($excludeInlineFields && !in_array($table, ['tt_content', 'pages'], true)) {
                continue;
            }
            if (isset($tableDefinition->tca[$fieldKey]) && AffixUtility::hasMaskPrefix($table)) {
                return $table;
            }
            foreach ($tableDefinition->elements as $element) {
                // If element key is set, ignore all other elements
                if ($elementKey !== '' && ($elementKey !== $element['key'])) {
                    continue;
                }
                if (isset($tableDefinition->tca[$fieldKey]) || in_array($fieldKey, ($element['columns'] ?? []), true)) {
                    return $table;
                }
            }
        }

        return '';
    }

    /**
     * Returns all elements that use this field
     */
    public function getElementsWhichUseField(string $key, string $table = 'tt_content'): array
    {
        if (!$this->hasTableDefinition($table)) {
            return [];
        }

        $definition = $this->getTableDefiniton($table);

        $elementsInUse = [];
        foreach ($definition->elements as $element) {
            foreach ($element['columns'] ?? [] as $column) {
                if ($column === $key) {
                    $elementsInUse[] = $element;
                    break;
                }
                if ($this->getFormType($column, $element['key'], $table) === FieldType::PALETTE) {
                    foreach ($definition->palettes[$column]['showitem'] ?? [] as $item) {
                        if ($item === $key) {
                            $elementsInUse[] = $element;
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
        if (!$this->hasTableDefinition($table)) {
            return '';
        }
        $tableDefinition = $this->getTableDefiniton($table);

        // If this field is in a repeating field or palette, the label is in the field configuration.
        $field = $tableDefinition->tca[$fieldKey] ?? [];
        if (isset($field['inlineParent'])) {
            if (is_array($field['label'])) {
                if (isset($field['label'][$elementKey])) {
                    return $field['label'][$elementKey];
                }
            } else {
                return $field['label'];
            }
        }

        // Root level fields have their labels defined in element labels array.
        $elements = $tableDefinition->elements;
        $columns = $elements[$elementKey]['columns'] ?? false;
        if (!empty($columns)) {
            $fieldIndex = array_search($fieldKey, $columns, true);
            if ($fieldIndex !== false) {
                return $elements[$elementKey]['labels'][$fieldIndex];
            }
        }

        return '';
    }

    /**
     * This method searches for an existing label of a multiuse field
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        if (!$this->hasTableDefinition($table)) {
            return '';
        }
        $definition = $this->getTableDefiniton($table);

        $label = '';
        foreach ($definition->elements as $element) {
            if (in_array($key, $element['columns'] ?? [], true)) {
                $label = $element['labels'][array_search($key, $element['columns'], true)];
            } else {
                $label = $definition->tca[$key]['label'][$element['key']] ?? '';
            }
            if ($label !== '') {
                break;
            }
        }
        return $label;
    }

    /**
     * Sort inline fields recursively.
     */
    protected function sortInlineFieldsByOrder(array &$inlineFields, string $elementKey = ''): void
    {
        uasort(
            $inlineFields,
            static function ($columnA, $columnB) use ($elementKey) {
                if (is_array($columnA['order'])) {
                    $a = isset($columnA['order'][$elementKey]) ? (int)$columnA['order'][$elementKey] : 0;
                    $b = isset($columnB['order'][$elementKey]) ? (int)$columnB['order'][$elementKey] : 0;
                } else {
                    $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
                    $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
                }
                return $a - $b;
            }
        );

        foreach ($inlineFields as $field) {
            if (isset($field['inlineFields']) && is_array($field['inlineFields']) && in_array(($field['config']['type'] ?? ''), ['inline', 'palette'])) {
                $this->sortInlineFieldsByOrder($field['inlineFields']);
            }
        }
    }
}
