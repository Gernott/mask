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

namespace MASK\Mask\Domain\Repository;

use MASK\Mask\ConfigurationLoader\ConfigurationLoaderInterface;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Loader\TableDefinitionCollection;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\TcaConverterUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * @api
 */
class StorageRepository implements SingletonInterface
{
    /**
     * @var string
     */
    protected $currentKey = '';

    /**
     * @var array
     */
    protected $defaults = [];

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(
        LoaderInterface $loader,
        ConfigurationLoaderInterface $configurationLoader
    ) {
        $this->loader = $loader;
        $this->configurationLoader = $configurationLoader;
    }

    /**
     * Load Storage
     *
     * @return array
     */
    public function load(): array
    {
        return $this->loader->load()->toArray();
    }

    /**
     * Write Storage
     */
    public function write(array $json): void
    {
        $this->loader->write(TableDefinitionCollection::createFromInternalArray($json));
    }

    /**
     * Sort and write json
     */
    public function persist(array $json): void
    {
        // sort content elements by key before saving
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Load Field
     */
    public function loadField(string $table, string $fieldName): array
    {
        $tableDefinition = $this->loader->load()->getTableDefinitonByTable($table);
        if (!$tableDefinition) {
            return [];
        }

        return $tableDefinition->getTca()[$fieldName] ?? [];
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     */
    public function loadInlineFields(string $parentKey, string $elementKey = ''): array
    {
        $tableDefinitionCollection = $this->loader->load();
        $inlineFields = [];

        // Load inline fields of own table
        if ($tableDefinitionCollection->getTableDefinitonByTable($parentKey)) {
            $searchTables = [$parentKey];
        } else {
            $searchTables = array_keys($tableDefinitionCollection->toArray());
        }

        // Traverse tables and find palette
        foreach ($searchTables as $table) {
            foreach ($tableDefinitionCollection->getTableDefinitonByTable($table)->getTca() as $key => $tca) {
                // if inlineParent is an array, it's in a palette on default table
                if (is_array(($tca['inlineParent'] ?? ''))) {
                    $inlineParent = $tca['inlineParent'][$elementKey] ?? '';
                } else {
                    $inlineParent = $tca['inlineParent'] ?? '';
                }
                if ($inlineParent === $parentKey) {
                    $maskKey = AffixUtility::addMaskPrefix($tca['key']);
                    if ($this->getFormType($tca['key'], $elementKey, $table) === FieldType::INLINE) {
                        $tca['inlineFields'] = $this->loadInlineFields($key, $elementKey);
                    }
                    if (($tca['config']['type'] ?? '') === 'palette') {
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

    /**
     * Load Element with all the field configurations
     */
    public function loadElement(string $type, string $key): array
    {
        // Only tt_content and pages can have elements
        if (!in_array($type, ['tt_content', 'pages'])) {
            return [];
        }

        $tableDefinitions = $this->loader->load();
        $table = $tableDefinitions->getTableDefinitonByTable($type);

        if (!$table) {
            return [];
        }

        $elements = $table->getElements();
        $columns = $elements[$key]['columns'] ?? [];

        if (!is_array($columns)) {
            return [];
        }

        $fields = [];
        foreach ($columns as $fieldName) {
            $fields[$fieldName] = $table->getTca()[$fieldName] ?? [];
        }

        if (!empty($fields)) {
            $elements[$key]['tca'] = $fields;
        }

        return $elements[$key] ?? [];
    }

    /**
     * Adds new Content-Element
     */
    public function add(array $element, array $fields, string $table): array
    {
        // Load
        $json = $this->load();
        $jsonAdd = [];
        $elementKey = $element['key'];

        // Set element
        $jsonAdd[$table]['elements'][$elementKey] = $element;

        $jsonAdd = $this->setSql($jsonAdd, $fields, $table);

        // Create JSON tca Array:
        $jsonAdd = $this->addFieldsToJson($jsonAdd, $fields, $elementKey, $table, $table);
        ArrayUtility::mergeRecursiveWithOverrule($json, $jsonAdd);

        return $json;
    }

    protected function setSql($json, $fields, $table)
    {
        $defaults = $this->configurationLoader->loadDefaults();
        foreach ($fields as $field) {
            $fieldType = FieldType::cast($field['name']);
            $fieldname = $field['key'];
            // If mask field which needs table column
            if (isset($defaults[$field['name']]['sql']) && AffixUtility::hasMaskPrefix($field['key'])) {
                // Keep existing value. For new fields use defaults.
                $json[$table]['sql'][$fieldname][$table][$fieldname] = $field['sql'] ?? $defaults[$field['name']]['sql'];

                // Set sys_file_reference entry for mask file fields.
                if ($fieldType->equals(FieldType::FILE)) {
                    $json['sys_file_reference']['sql'][$fieldname]['sys_file_reference'][$fieldname] = "int(11) unsigned DEFAULT '0' NOT NULL";
                }
            }
            if (isset($field['fields'])) {
                $inlineTable = $fieldType->equals(FieldType::INLINE) ? $field['key'] : $table;
                $json = $this->setSql($json, $field['fields'], $inlineTable);
            }
        }
        return $json;
    }

    /**
     * This method converts the nested structure of VueJs to the flat json structure.
     * Date values are converted back to the timestamp representation.
     */
    protected function addFieldsToJson(array $jsonAdd, array $fields, string $elementKey, string $table, string $defaultTable, ?array $parent = null): array
    {
        $order = 0;
        foreach ($fields as $field) {
            $order += 1;
            $fieldAdd = [];
            $onRootLevel = $table === $defaultTable;
            $isMaskField = AffixUtility::hasMaskPrefix($field['key']);

            // Add columns and labels to element if on root level
            if ($onRootLevel && !$parent) {
                $jsonAdd[$defaultTable]['elements'][$elementKey]['columns'][] = $field['key'];
                $jsonAdd[$defaultTable]['elements'][$elementKey]['labels'][] = $field['label'];
            }

            // Add key and config to mask field
            if ($isMaskField) {
                $defaults = $this->configurationLoader->loadDefaults();
                $field['tca'] = $field['tca'] ?? [];
                ArrayUtility::mergeRecursiveWithOverrule($field['tca'], $defaults[$field['name']]['tca_out'] ?? []);
                $fieldAdd = TcaConverterUtility::convertFlatTcaToArray($field['tca']);
                $fieldAdd['key'] = AffixUtility::removeMaskPrefix($field['key']);
                $fieldAdd['description'] = $field['description'] ?? '';
            } else {
                $fieldAdd['key'] = $field['key'];
                $fieldAdd['coreField'] = 1;
            }

            // Add field type name for easier resolving
            $fieldAdd['name'] = $field['name'];

            // Convert range values of timestamp to integers
            if ($isMaskField && $field['name'] === FieldType::TIMESTAMP) {
                $default = $fieldAdd['config']['default'] ?? false;
                if ($default) {
                    $date = new \DateTime($default);
                    $fieldAdd['config']['default'] = $date->getTimestamp();
                }
                $rangeLower = $fieldAdd['config']['range']['lower'] ?? false;
                if ($rangeLower) {
                    $date = new \DateTime($rangeLower);
                    $fieldAdd['config']['range']['lower'] = $date->getTimestamp();
                }
                $rangeUpper = $fieldAdd['config']['range']['upper'] ?? false;
                if ($rangeUpper) {
                    $date = new \DateTime($rangeUpper);
                    $fieldAdd['config']['range']['upper'] = $date->getTimestamp();
                }
            }

            // Add label, order and flags to child fields
            if (isset($parent)) {
                if ($parent['name'] === FieldType::PALETTE) {
                    $fieldAdd['inPalette'] = 1;
                    if ($onRootLevel) {
                        $fieldAdd['inlineParent'][$elementKey] = $parent['key'];
                        $fieldAdd['label'][$elementKey] = $field['label'];
                        $fieldAdd['order'][$elementKey] = $order;
                    } else {
                        $fieldAdd['inlineParent'] = $parent['key'];
                        $fieldAdd['label'] = $field['label'];
                        $fieldAdd['order'] = $order;
                    }
                    // Add palettes entry
                    $jsonAdd[$table]['palettes'][$parent['key']]['showitem'][] = $field['key'];
                    $jsonAdd[$table]['palettes'][$parent['key']]['label'] = $parent['label'];
                }
                if ($parent['name'] === FieldType::INLINE) {
                    $fieldAdd['inlineParent'] = $parent['key'];
                    $fieldAdd['label'] = $field['label'];
                    $fieldAdd['order'] = $order;
                }
            }

            // Add tca entry for field
            $jsonAdd[$table]['tca'][$field['key']] = $fieldAdd;

            // Resolve nested fields
            if (isset($field['fields'])) {
                $inlineTable = $field['name'] === FieldType::INLINE ? $field['key'] : $table;
                $jsonAdd = $this->addFieldsToJson($jsonAdd, $field['fields'], $elementKey, $inlineTable, $defaultTable, $field);
            }
        }
        return $jsonAdd;
    }

    /**
     * Removes Content-Element
     */
    public function remove(string $table, string $elementKey): array
    {
        $this->currentKey = $elementKey;
        // Load
        $json = $this->load();

        // Remove
        $columns = $json[$table]['elements'][$elementKey]['columns'];
        unset($json[$table]['elements'][$elementKey]);
        if (is_array($columns)) {
            foreach ($columns as $field) {
                $json = $this->removeField($table, $field, $json);
            }
        }
        $this->currentKey = '';
        return $json;
    }

    /**
     * Hides Content-Element
     */
    public function hide(string $table, string $elementKey): void
    {
        // Load
        $json = $this->load();
        $json[$table]['elements'][$elementKey]['hidden'] = 1;
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Activates Content-Element
     */
    public function activate(string $table, string $elementKey): void
    {
        // Load
        $json = $this->load();
        unset($json[$table]['elements'][$elementKey]['hidden']);
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Removes a field from the json, also recursively all inline-fields
     */
    protected function removeField(string $table, string $field, array $json): array
    {
        $maskKey = $field;
        $field = AffixUtility::removeMaskPrefix($maskKey);
        $keyToUse = isset($json[$table]['tca'][$field]) ? $field : $maskKey;

        // check if this field is used in any other elements
        $elementsInUse = $this->getElementsWhichUseField($keyToUse, $table);
        $usedInAnotherElement = count($elementsInUse) > 1;

        // Remove inlineParent, label and order
        $inlineParent = $json[$table]['tca'][$keyToUse]['inlineParent'] ?? false;
        if (is_array($inlineParent)) {
            unset(
                $json[$table]['tca'][$keyToUse]['inlineParent'][$this->currentKey],
                $json[$table]['tca'][$keyToUse]['label'][$this->currentKey],
                $json[$table]['tca'][$keyToUse]['order'][$this->currentKey]
            );
        }

        // if the field is a repeating field, make some exceptions
        if (in_array(($json[$table]['tca'][$maskKey]['config']['type'] ?? ''), ['inline', 'palette'])) {
            $inlineFields = $this->loadInlineFields($maskKey, $this->currentKey);
            // Recursively delete all inline field if possible
            foreach ($inlineFields  as $inlineField) {
                // Only remove if not in use in another element
                if (!$usedInAnotherElement) {
                    $parentTable = ($inlineField['inPalette'] ?? false) ? $table : $inlineField['inlineParent'];
                    $inlineKey = AffixUtility::addMaskPrefix($inlineField['key']);
                    $json = $this->removeField($parentTable, $inlineKey, $json);
                }
            }
        }

        // then delete the field, if it is not in use in another element
        if (!$usedInAnotherElement) {
            unset(
                $json[$table]['tca'][$maskKey],
                // Unset typo3 core field
                $json[$table]['tca'][$field],
                $json[$table]['sql'][$maskKey]
            );

            $type = $this->getFormType($maskKey, $this->currentKey, $table);

            // If field is of type inline, also delete table entry
            if ($type === FieldType::INLINE) {
                unset($json[$maskKey]);
            }

            if ($type === FieldType::PALETTE) {
                unset($json[$table]['palettes'][$maskKey]);
            }

            // If field is of type file, also delete entry in sys_file_reference
            if ($type === FieldType::FILE) {
                unset($json['sys_file_reference']['sql'][$maskKey]);
                $json = $this->cleanTable('sys_file_reference', $json);
            }
        }
        return $this->cleanTable($table, $json);
    }

    /**
     * Deletes all the empty settings of a table
     */
    protected function cleanTable(string $table, array $json): array
    {
        if (isset($json[$table]['tca']) && count($json[$table]['tca']) < 1) {
            unset($json[$table]['tca']);
        }
        if (isset($json[$table]['sql']) && count($json[$table]['sql']) < 1) {
            unset($json[$table]['sql']);
        }
        if (isset($json[$table]['palettes']) && count($json[$table]['palettes']) < 1) {
            unset($json[$table]['palettes']);
        }
        if (isset($json[$table]) && count($json[$table]) < 1) {
            unset($json[$table]);
        }
        return $json;
    }

    /**
     * Updates Content-Element in Storage-Repository
     */
    public function update(array $element, array $fields, string $table, bool $isNew): void
    {
        if (!$isNew) {
            $json = $this->remove($table, $element['key']);
            $this->persist($json);
        }
        $this->persist($this->add($element, $fields, $table));
    }

    /**
     * Sorts the json entries
     */
    protected function sortJson(array &$array): void
    {
        // check if array is not a hash table, because we only want to sort hash tables
        if (
            empty($array)
            || !(array_keys($array) !== range(0, count($array) - 1))
        ) {
            return;
        }

        ksort($array);
        foreach ($array as &$item) {
            if (is_array($item)) {
                $this->sortJson($item);
            }
        }
    }

    /**
     * Returns the formType of a field in an element
     */
    public function getFormType(string $fieldKey, string $elementKey = '', string $table = 'tt_content'): string
    {
        // TODO Allow bodytext to be normal TEXT field.
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
     * Returns all elements that use this field
     */
    public function getElementsWhichUseField(string $key, string $table = 'tt_content'): array
    {
        $definition = $this->loader->load()->getTableDefinitonByTable($table);

        if (!$definition) {
            return [];
        }

        $elementsInUse = [];
        foreach ($definition->getElements() as $element) {
            foreach ($element['columns'] ?? [] as $column) {
                if ($column === $key) {
                    $elementsInUse[] = $element;
                    break;
                }
                if ($this->getFormType($column, $element['key'], $table) === FieldType::PALETTE) {
                    foreach ($definition->getPalettes()[$column]['showitem'] ?? [] as $item) {
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
     * This method searches for an existing label of a multiuse field
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        $label = '';
        $definition = $this->loader->load()->getTableDefinitonByTable($table);

        if (!$definition) {
            return '';
        }

        foreach ($definition->getElements() as $element) {
            if (in_array($key, $element['columns'] ?? [], true)) {
                $label = $element['labels'][array_search($key, $element['columns'], true)];
            } else {
                $label = $definition->getTca()[$key]['label'][$element['key']] ?? '';
            }
            if ($label !== '') {
                break;
            }
        }
        return $label;
    }
}
