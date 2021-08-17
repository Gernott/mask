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
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\TcaConverterUtility;
use TYPO3\CMS\Core\SingletonInterface;
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
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(
        LoaderInterface $loader,
        TableDefinitionCollection $tableDefinitionCollection,
        ConfigurationLoaderInterface $configurationLoader
    ) {
        $this->loader = $loader;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
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
     * Removes a field from the json, also recursively all inline-fields
     */
    protected function removeField(string $table, string $field, array $json): array
    {
        $maskKey = $field;
        $field = AffixUtility::removeMaskPrefix($maskKey);
        $keyToUse = isset($json[$table]['tca'][$field]) ? $field : $maskKey;

        // check if this field is used in any other elements
        $elementsInUse = $this->tableDefinitionCollection->getElementsWhichUseField($keyToUse, $table);
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
            $inlineFields = $this->tableDefinitionCollection->loadInlineFields($maskKey, $this->currentKey);
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

            $type = $this->tableDefinitionCollection->getFormType($maskKey, $this->currentKey, $table);

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
     * Load Field
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadField(string $table, string $fieldName): array
    {
        trigger_error(
            'StorageRepository->loadField will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->loadField($table, $fieldName);
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadInlineFields(string $parentKey, string $elementKey = ''): array
    {
        trigger_error(
            'StorageRepository->loadInlineFields will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->loadInlineFields($parentKey, $elementKey);
    }

    /**
     * Load Element with all the field configurations
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadElement(string $type, string $key): array
    {
        trigger_error(
            'StorageRepository->loadInlineFields will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->loadElement($type, $key);
    }

    /**
     * Returns the formType of a field in an element
     * @deprecated will be removed in Mask v8.0.
     */
    public function getFormType(string $fieldKey, string $elementKey = '', string $table = 'tt_content'): string
    {
        trigger_error(
            'StorageRepository->getFormType will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getFormType($fieldKey, $elementKey, $table);
    }

    /**
     * Returns all elements that use this field
     * @deprecated will be removed in Mask v8.0.
     */
    public function getElementsWhichUseField(string $key, string $table = 'tt_content'): array
    {
        trigger_error(
            'StorageRepository->getElementsWhichUseField will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getElementsWhichUseField($key, $table);
    }

    /**
     * This method searches for an existing label of a multiuse field
     * @deprecated will be removed in Mask v8.0.
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        trigger_error(
            'StorageRepository->findFirstNonEmptyLabel will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->findFirstNonEmptyLabel($table, $key);
    }

    /**
     * Returns the label of a field in an element
     * @deprecated will be removed in Mask v8.0.
     */
    public function getLabel(string $elementKey, string $fieldKey, string $type = 'tt_content'): string
    {
        trigger_error(
            'StorageRepository->getLabel will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getLabel($elementKey, $fieldKey, $type);
    }

    /**
     * Returns type of field (tt_content or pages)
     * @deprecated will be removed in Mask v8.0.
     */
    public function getFieldType(string $fieldKey, string $elementKey = '', bool $excludeInlineFields = false): string
    {
        trigger_error(
            'StorageRepository->getFieldType will be removed in Mask v8.0. Please use MASK\Mask\Loader\LoaderInterface instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getFieldType($fieldKey, $elementKey, $excludeInlineFields);
    }
}
