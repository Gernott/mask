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
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * This class is responsible for the persistence of content elements.
 * It manages adding, removing and updating operations.
 * For read only use cases, TableDefinitionCollection should be used directly.
 */
class StorageRepository implements SingletonInterface
{
    protected string $currentKey = '';
    protected LoaderInterface $loader;
    protected TableDefinitionCollection $tableDefinitionCollection;
    protected ConfigurationLoaderInterface $configurationLoader;
    protected Features $features;

    /**
     * @var array<string, mixed>
     */
    protected array $defaults = [];

    public function __construct(
        LoaderInterface $loader,
        TableDefinitionCollection $tableDefinitionCollection,
        ConfigurationLoaderInterface $configurationLoader,
        Features $features
    ) {
        $this->loader = $loader;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->configurationLoader = $configurationLoader;
        $this->features = $features;
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

            // Add config to mask field
            $defaults = $this->configurationLoader->loadDefaults();
            $field['tca'] = $field['tca'] ?? [];
            ArrayUtility::mergeRecursiveWithOverrule($field['tca'], $defaults[$field['name']]['tca_out'] ?? []);
            $tcaConfig = TcaConverter::convertFlatTcaToArray($field['tca']);

            // Add key to mask field
            $isMaskField = AffixUtility::hasMaskPrefix($field['key']);
            $fieldAdd = [];
            if ($isMaskField) {
                $fieldAdd['key'] = AffixUtility::removeMaskPrefix($field['key']);
            } else {
                $fieldAdd = [
                    'key' => $field['key'],
                    'coreField' => 1,
                ];
            }

            // Add the full key in addition to the abbreviated key.
            $fieldAdd['fullKey'] = $field['key'];

            // Add field type name for easier resolving
            $fieldAdd['type'] = $field['name'];

            // Convert range values of timestamp to integers
            if (FieldType::cast($fieldAdd['type'])->equals(FieldType::TIMESTAMP)) {
                $default = $tcaConfig['config']['default'] ?? false;
                if ($default) {
                    $date = new \DateTime($default);
                    $tcaConfig['config']['default'] = $date->getTimestamp();
                }
                $rangeLower = $tcaConfig['config']['range']['lower'] ?? false;
                if ($rangeLower) {
                    $date = new \DateTime($rangeLower);
                    $tcaConfig['config']['range']['lower'] = $date->getTimestamp();
                }
                $rangeUpper = $tcaConfig['config']['range']['upper'] ?? false;
                if ($rangeUpper) {
                    $date = new \DateTime($rangeUpper);
                    $tcaConfig['config']['range']['upper'] = $date->getTimestamp();
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

            if ($fieldAdd['fullKey'] === 'bodytext') {
                $fieldAdd['bodytextTypeByElement'][$elementKey] = $fieldAdd['type'];
                unset($fieldAdd['type']);
            }

            // Add tca entry for field
            unset($jsonAdd[$table]['elements'][$elementKey]['columnsOverride'][$field['key']]);

            // Override shared fields when:
            // The feature overrideSharedFields is enabled OR it is a core field
            // AND the table is tt_content (does not work for pages).
            // AND we are on root level AND the field type is able to be shared.
            $combinedFieldAdd = array_merge($fieldAdd, $tcaConfig);
            $tcaFieldDefinition = TcaFieldDefinition::createFromFieldArray($combinedFieldAdd);
            $isCoreFieldOrOverrideSharedFieldsIsEnabled = !$isMaskField || $this->features->isFeatureEnabled('overrideSharedFields');
            $overrideSharedField =
                $isCoreFieldOrOverrideSharedFieldsIsEnabled
                && $table === 'tt_content'
                && $onRootLevel
                && $tcaFieldDefinition->getFieldType($elementKey)->canBeShared();

            if ($overrideSharedField && $isMaskField) {
                $jsonAdd[$table]['tca'][$field['key']] = $tcaFieldDefinition->getMinimalDefinition();
            } elseif (!$overrideSharedField && $isMaskField) {
                $jsonAdd[$table]['tca'][$field['key']] = $combinedFieldAdd;
            } else {
                $jsonAdd[$table]['tca'][$field['key']] = $fieldAdd;
            }
            if ($overrideSharedField) {
                $overrideDefinition = $tcaFieldDefinition->getOverridesDefinition();
                if ($overrideDefinition['config'] !== []) {
                    $jsonAdd[$table]['elements'][$elementKey]['columnsOverride'][$field['key']] = $overrideDefinition;
                }
            }

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
                $elementTcaDefinition = $this->tableDefinitionCollection->loadElement($table, $this->currentKey);
                $element = $elementTcaDefinition instanceof ElementTcaDefinition
                    ? $elementTcaDefinition->elementDefinition
                    : null;
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $this->currentKey, $element) as $inlineField) {
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
}
