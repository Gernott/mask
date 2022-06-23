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
use MASK\Mask\Definition\ElementDefinitionCollection;
use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\TcaConverter;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * This class is responsible for the persistence of content elements.
 * It manages adding, removing and updating operations.
 * For read only use cases, TableDefinitionCollection should be used directly.
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
     * Load content elements as json representation.
     */
    public function load(): array
    {
        return $this->loader->load()->toArray(false);
    }

    /**
     * Persist content elements
     */
    public function write(array $json): TableDefinitionCollection
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
        $tableDefinitionCollection->setToCurrentVersion();
        $this->loader->write($tableDefinitionCollection);
        return $tableDefinitionCollection;
    }

    /**
     * Persist content elements.
     */
    public function persist(array $json): TableDefinitionCollection
    {
        return $this->write($json);
    }

    /**
     * Adds a new content element.
     *
     * @internal For internal usage only.
     */
    public function add(array $element, array $fields, string $table, bool $isNew): array
    {
        $elementKey = $element['key'];

        $jsonAdd = [];
        $jsonAdd[$table]['elements'][$elementKey] = $element;
        $jsonAdd = $this->setSql($jsonAdd, $fields, $table);
        $jsonAdd = $this->addFieldsToJson($jsonAdd, $fields, $elementKey, $table, $table);

        // Add sorting for new element
        $tableDefinitionCollection = $this->loader->load();
        if ($isNew && $tableDefinitionCollection->hasTable($table)) {
            $sorting = $this->getHighestSorting($tableDefinitionCollection->getTable($table)->elements);
            $sorting += 1;
            $jsonAdd[$table]['elements'][$elementKey]['sorting'] = (string)$sorting;
        }

        $json = $this->remove($table, $element['key'], $fields);
        ArrayUtility::mergeRecursiveWithOverrule($json, $jsonAdd);

        return $json;
    }

    /**
     * Removes a content element for the given table.
     *
     * @internal For internal usage only.
     */
    public function remove(string $table, string $elementKey, array $addedFields = []): array
    {
        $this->currentKey = $elementKey;
        $json = $this->load();
        $element = $this->tableDefinitionCollection->loadElement($table, $elementKey);

        if (!$element instanceof ElementTcaDefinition) {
            return $json;
        }

        unset($json[$table]['elements'][$elementKey]);
        foreach ($element->getRootTcaFields() as $field) {
            $json = $this->removeField($table, $field, $json, $addedFields);
        }
        $this->currentKey = '';
        return $json;
    }

    /**
     * Updates Content-Element in Storage-Repository
     *
     * @internal For internal usage only.
     */
    public function update(array $element, array $fields, string $table, bool $isNew): TableDefinitionCollection
    {
        return $this->persist($this->add($element, $fields, $table, $isNew));
    }

    /**
     * Hides a content element
     */
    public function hide(string $table, string $elementKey): TableDefinitionCollection
    {
        $json = $this->load();
        $json[$table]['elements'][$elementKey]['hidden'] = 1;
        return $this->write($json);
    }

    /**
     * Activates a content element
     */
    public function activate(string $table, string $elementKey): TableDefinitionCollection
    {
        $json = $this->load();
        unset($json[$table]['elements'][$elementKey]['hidden']);
        return $this->write($json);
    }

    protected function getHighestSorting(ElementDefinitionCollection $elements): int
    {
        $max = 0;
        foreach ($elements as $element) {
            if ($element->sorting > $max) {
                $max = $element->sorting;
            }
        }
        return $max;
    }

    protected function setSql(array $json, array $fields, string $table): array
    {
        $defaults = $this->configurationLoader->loadDefaults();
        foreach ($fields as $field) {
            $fieldType = FieldType::cast($field['name']);
            $fieldName = $field['key'];
            // If mask field which needs table column
            if (isset($defaults[$field['name']]['sql']) && AffixUtility::hasMaskPrefix($field['key'])) {
                // Keep existing value. For new fields use defaults.
                $json[$table]['sql'][$fieldName][$table][$fieldName] = $field['sql'] ?? $defaults[$field['name']]['sql'];
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
            $onRootLevel = $table === $defaultTable;

            // Add columns and labels to element if on root level
            if ($onRootLevel && !$parent) {
                $jsonAdd[$defaultTable]['elements'][$elementKey]['columns'][] = $field['key'];
                $jsonAdd[$defaultTable]['elements'][$elementKey]['labels'][] = $field['label'];
                $jsonAdd[$defaultTable]['elements'][$elementKey]['descriptions'][] = $field['description'] ?? '';
            }

            // Add key and config to mask field
            $fieldAdd = [];
            $isMaskField = AffixUtility::hasMaskPrefix($field['key']);
            if ($isMaskField) {
                $defaults = $this->configurationLoader->loadDefaults();
                $field['tca'] = $field['tca'] ?? [];
                ArrayUtility::mergeRecursiveWithOverrule($field['tca'], $defaults[$field['name']]['tca_out'] ?? []);
                $fieldAdd = TcaConverter::convertFlatTcaToArray($field['tca']);
                $fieldAdd['key'] = AffixUtility::removeMaskPrefix($field['key']);
            } else {
                $fieldAdd['key'] = $field['key'];
                $fieldAdd['coreField'] = 1;
            }

            // Add the full key in addition to the abbreviated key.
            $fieldAdd['fullKey'] = $field['key'];

            // Add field type name for easier resolving
            $fieldAdd['type'] = $field['name'];

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

            // Create palette
            if (FieldType::cast($fieldAdd['type'])->equals(FieldType::PALETTE)) {
                $jsonAdd[$table]['palettes'][$fieldAdd['fullKey']]['showitem'] = [];
                $jsonAdd[$table]['palettes'][$fieldAdd['fullKey']]['label'] = $field['label'];
                $jsonAdd[$table]['palettes'][$fieldAdd['fullKey']]['description'] = $field['description'] ?? '';
            }

            // Add label, order and flags to child fields
            if (isset($parent)) {
                if ($parent['name'] === FieldType::PALETTE) {
                    $fieldAdd['inPalette'] = 1;
                    if ($onRootLevel) {
                        $fieldAdd['inlineParent'][$elementKey] = $parent['key'];
                        $fieldAdd['label'][$elementKey] = $field['label'];
                        $fieldAdd['description'][$elementKey] = $field['description'];
                        $fieldAdd['order'][$elementKey] = $order;
                    } else {
                        $fieldAdd['inlineParent'] = $parent['key'];
                        $fieldAdd['label'] = $field['label'];
                        $fieldAdd['description'] = $field['description'];
                        $fieldAdd['order'] = $order;
                    }
                    // Add field to showitem array
                    $jsonAdd[$table]['palettes'][$parent['key']]['showitem'][] = $field['key'];
                }
                if ($parent['name'] === FieldType::INLINE) {
                    $fieldAdd['inlineParent'] = $parent['key'];
                    $fieldAdd['label'] = $field['label'];
                    $fieldAdd['description'] = $field['description'] ?? '';
                    $fieldAdd['order'] = $order;
                }
            }

            if ($fieldAdd['key'] === 'bodytext') {
                $fieldAdd['bodytextTypeByElement'][$elementKey] = $fieldAdd['type'];
                unset($fieldAdd['type']);
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
    protected function removeField(string $table, TcaFieldDefinition $field, array $json, array $addedFields): array
    {
        // check if this field is used in any other elements
        $usedInAnotherElement = $this->tableDefinitionCollection->getElementsWhichUseField($field->fullKey, $table)->count() > 1;

        // Remove inlineParent, label and order
        $inlineParent = $json[$table]['tca'][$field->fullKey]['inlineParent'] ?? false;
        if (is_array($inlineParent)) {
            unset(
                $json[$table]['tca'][$field->fullKey]['inlineParent'][$this->currentKey],
                $json[$table]['tca'][$field->fullKey]['label'][$this->currentKey],
                $json[$table]['tca'][$field->fullKey]['order'][$this->currentKey]
            );
        }

        // Remove TCA config, if the field is used in another element and will be added back again later.
        // This prevents incorrect merging of array-like configurations (e.g. items).
        if ($usedInAnotherElement && $this->fieldExistsInNestedFields($addedFields, $field->fullKey)) {
            unset($json[$table]['tca'][$field->fullKey]['config']);
        }

        // then delete the field, if it is not in use in another element
        if (!$usedInAnotherElement) {
            // if the field is a repeating field, make some exceptions
            $fieldType = $this->tableDefinitionCollection->getFieldType($field->fullKey, $table);
            if ($fieldType->isParentField()) {
                // Recursively delete all inline field if possible
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $this->currentKey) as $inlineField) {
                    $parentTable = $inlineField->inPalette ? $table : $inlineField->inlineParent;
                    $json = $this->removeField($parentTable, $inlineField, $json, $addedFields);
                }
            }

            unset(
                $json[$table]['tca'][$field->fullKey],
                $json[$table]['sql'][$field->fullKey]
            );

            $fieldType = $this->tableDefinitionCollection->getFieldType($field->fullKey, $table);

            // If field is of type inline, also delete table entry
            if ($fieldType->equals(FieldType::INLINE)) {
                unset($json[$field->fullKey]);
            }

            if ($fieldType->equals(FieldType::PALETTE)) {
                unset($json[$table]['palettes'][$field->fullKey]);
            }
        }
        return $this->cleanTable($table, $json);
    }

    protected function fieldExistsInNestedFields(array $fields, string $searchKey): bool
    {
        foreach ($fields as $field) {
            if ($field['key'] === $searchKey) {
                return true;
            }

            if (FieldType::cast($field['name'])->equals(FieldType::PALETTE)) {
                foreach ($field['fields'] ?? [] as $paletteField) {
                    if ($paletteField['key'] === $searchKey) {
                        return true;
                    }
                }
            }
        }

        return false;
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
     * Load Field
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadField(string $table, string $fieldName): array
    {
        trigger_error(
            'StorageRepository->loadField will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->loadField() instead.',
            E_USER_DEPRECATED
        );

        $fieldDefinition =  $this->loader->load()->loadField($table, $fieldName);

        if (!$fieldDefinition) {
            return [];
        }

        return $fieldDefinition->toArray();
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadInlineFields(string $parentKey, string $elementKey = ''): array
    {
        trigger_error(
            'StorageRepository->loadInlineFields will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->loadInlineFields() instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->loadInlineFields($parentKey, $elementKey)->toArray();
    }

    /**
     * Load Element with all the field configurations
     * @deprecated will be removed in Mask v8.0.
     */
    public function loadElement(string $type, string $key): array
    {
        trigger_error(
            'StorageRepository->loadInlineFields will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->loadElement() instead.',
            E_USER_DEPRECATED
        );

        $element = $this->loader->load()->loadElement($type, $key);

        if (!$element) {
            return [];
        }

        return $element->toArray();
    }

    /**
     * Returns the formType of a field in an element
     * @deprecated will be removed in Mask v8.0.
     */
    public function getFormType(string $fieldKey, string $elementKey = '', string $table = 'tt_content'): string
    {
        trigger_error(
            'StorageRepository->getFormType will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->getFieldType() instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getFieldTypeString($fieldKey, $table, $elementKey);
    }

    /**
     * Returns all elements that use this field
     * @deprecated will be removed in Mask v8.0.
     */
    public function getElementsWhichUseField(string $key, string $table = 'tt_content'): array
    {
        trigger_error(
            'StorageRepository->getElementsWhichUseField will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->getElementsWhichUseField() instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getElementsWhichUseField($key, $table)->toArray();
    }

    /**
     * This method searches for an existing label of a multiuse field
     * @deprecated will be removed in Mask v8.0.
     */
    public function findFirstNonEmptyLabel(string $table, string $key): string
    {
        trigger_error(
            'StorageRepository->findFirstNonEmptyLabel will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->findFirstNonEmptyLabel() instead.',
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
            'StorageRepository->getLabel will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->getLabel() instead.',
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
            'StorageRepository->getFieldType will be removed in Mask v8.0. Please use MASK\Mask\Definition\TableDefinitionCollection->getTableByField() instead.',
            E_USER_DEPRECATED
        );

        return $this->loader->load()->getTableByField($fieldKey, $elementKey, $excludeInlineFields);
    }
}
