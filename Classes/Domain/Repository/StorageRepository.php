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

use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @api
 */
class StorageRepository implements SingletonInterface
{
    /**
     * SettingsService
     *
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    /**
     * @var string
     */
    protected $currentKey = '';

    /**
     * json configuration
     * @var array
     */
    private static $json;

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
        $this->extSettings = $this->settingsService->get();
    }

    /**
     * Load Storage
     *
     * @return array
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function load(): array
    {
        if (self::$json === null) {
            self::$json = [];
            if (!empty($this->extSettings['json'])) {
                $file = MaskUtility::getFileAbsFileName($this->extSettings['json']);
                if (file_exists($file)) {
                    self::$json = json_decode(file_get_contents($file), true, 512, 4194304);
                }
            }
        }
        return self::$json;
    }

    /**
     * Write Storage
     *
     * @param $json
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function write($json): void
    {
        if (!empty($this->extSettings['json'])) {
            $file = MaskUtility::getFileAbsFileName($this->extSettings['json']);
            GeneralUtility::writeFile(
                $file,
                json_encode($json, 4194304 | JSON_PRETTY_PRINT, 512)
            );
        }
        self::$json = $json;
    }

    /**
     * Sort and write json
     *
     * @param $json
     */
    public function persist($json)
    {
        // sort content elements by key before saving
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Load Field
     * @param $type
     * @param $key
     * @return array
     */
    public function loadField($type, $key): array
    {
        $json = $this->load();
        return $json[$type]['tca'][$key] ?? [];
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     *
     * @param string $parentKey key of the inline-field
     * @param $elementKey
     * @return array
     */
    public function loadInlineFields($parentKey, $elementKey = ''): array
    {
        $json = $this->load();
        $inlineFields = [];

        // Load inline fields of own table
        if (array_key_exists($parentKey, $json)) {
            $searchTables = [$parentKey];
        } else {
            $searchTables = array_keys($json);
        }

        // Traverse tables and find palette
        foreach ($searchTables as $table) {
            foreach ($json[$table]['tca'] ?? [] as $key => $tca) {
                // if inlineParent is an array, it's in a palette on default table
                if (is_array(($tca['inlineParent'] ?? ''))) {
                    $inlineParent = $tca['inlineParent'][$elementKey] ?? '';
                } else {
                    $inlineParent = $tca['inlineParent'] ?? '';
                }
                if ($inlineParent === $parentKey) {
                    if (($tca['config']['type'] ?? '') === 'inline') {
                        $tca['inlineFields'] = $this->loadInlineFields($key, $elementKey);
                    }
                    if (($tca['config']['type'] ?? '') === 'palette') {
                        $tca['inlineFields'] = $this->loadInlineFields('tx_mask_' . $tca['key'], $elementKey);
                    }
                    $tca['maskKey'] = 'tx_mask_' . $tca['key'];
                    $inlineFields[] = $tca;
                }
            }
        }

        $this->sortInlineFieldsByOrder($inlineFields, $elementKey);
        return $inlineFields;
    }

    /**
     * Sort inline fields recursively.
     *
     * @param array $inlineFields
     * @param string $elementKey
     */
    protected function sortInlineFieldsByOrder(array &$inlineFields, $elementKey = '')
    {
        uasort(
            $inlineFields,
            function ($columnA, $columnB) use ($elementKey) {
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

        foreach ($inlineFields as $i => $field) {
            if (in_array(($field['config']['type'] ?? ''), ['inline', 'palette'])) {
                if (isset($inlineFields[$i]['inlineFields']) && is_array($inlineFields[$i]['inlineFields'])) {
                    $this->sortInlineFieldsByOrder($inlineFields[$i]['inlineFields']);
                }
            }
        }
    }

    /**
     * Load Element with all the field configurations
     *
     * @param $type
     * @param $key
     * @return array
     */
    public function loadElement($type, $key): array
    {
        // Only tt_content and pages can have elements
        if (!in_array($type, ['tt_content', 'pages'])) {
            return [];
        }
        $json = $this->load();
        $fields = [];
        $columns = $json[$type]['elements'][$key]['columns'];

        // Check if it is an array before trying to count it
        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $fieldName) {
                $fields[$fieldName] = $json[$type]['tca'][$fieldName] ?? [];
            }
        }
        if (count($fields) > 0) {
            $json[$type]['elements'][$key]['tca'] = $fields;
        }
        return $json[$type]['elements'][$key] ?? [];
    }

    /**
     * Adds new Content-Element
     *
     * @param array $content
     * @noinspection NotOptimalIfConditionsInspection
     * @return array
     */
    public function add($content): array
    {
        // Load
        $json = $this->load();
        $elementKey = $content['elements']['key'];

        // Create JSON elements Array:
        foreach ($content['elements'] as $key => $value) {
            // delete columns and labels of irre-fields from elements
            if ($key === 'columns' || $key === 'labels') {
                foreach ($value as $index => $column) {
                    if ($content['tca'][$index]['inlineParent'] ?? false) {
                        unset($value[$index]);
                    // If using a mask field with empty label, we have to set the "default" label
                    } elseif ($key === 'labels' && empty($column) && isset($json[$content['type']]['tca'][$content['elements']['columns'][$index]])) {
                        $label = '';
                        foreach ($json[$content['type']]['elements'] as $element) {
                            if (is_array($element['columns']) && in_array($content['elements']['columns'][$index], $element['columns'], true)) {
                                $i = array_search($content['elements']['columns'][$index], $element['columns'], true);
                                if (!empty($element['labels'][$i])) {
                                    $label = $element['labels'][$i];
                                    break;
                                }
                            }
                        }
                        $value[$index] = $label;
                    }
                }
            }
            $json[$content['type']]['elements'][$elementKey][$key] = $value;
        }

        $columns = [];
        $labels = [];

        // delete columns and labels of irre-fields from elements
        foreach ($content['elements']['columns'] ?? [] as $index => $column) {
            $columns[] = $column;
            $labels[] = $content['elements']['labels'][$index];

            if ($content['tca'][$index]['inlineParent'] ?? false) {
                unset(
                    $content['elements']['columns'][$index],
                    $content['elements']['labels'][$index]
                );
            }
        }

        // Create JSON sql Array:
        foreach ($content['sql'] ?? [] as $table => $sqlArray) {
            foreach ($sqlArray as $index => $type) {
                $fieldname = 'tx_mask_' . $columns[$index];
                $json[$table]['sql'][$fieldname][$table][$fieldname] = $type;
            }
        }

        // Create JSON tca Array:
        $defaultType = $content['type'];
        $paletteParents = [];
        foreach ($content['tca'] ?? [] as $key => $value) {
            $hasConfig = isset($value['config']);
            $existingMaskFieldWithNoConfig = !$hasConfig && strpos($columns[$key], 'tx_mask') === 0;
            $inPalette = (bool)($value['inPalette'] ?? false);
            $coreFieldInPalette = !$existingMaskFieldWithNoConfig && !$hasConfig && $inPalette;
            $isInline = (bool)($value['inlineParent'] ?? false);
            $isPalette = (bool)($value['config']['type'] ?? false) == 'palette';
            $inlineParent = $value['inlineParent'] ?? '';

            if (!$isInline) {
                $type = $defaultType;
            } elseif (!$inPalette) {
                $type = $inlineParent;
                if ($isPalette) {
                    $paletteParents['tx_mask_' . $columns[$key]] = $inlineParent;
                }
            } else {
                $type = $paletteParents[$inlineParent] ?? $defaultType;
            }

            // Revert mask key to key without prefix
            if ($existingMaskFieldWithNoConfig) {
                $columns[$key] = MaskUtility::removeMaskPrefix($columns[$key]);
            }

            $maskKey = 'tx_mask_' . $columns[$key];
            $tempKey = 'temp_' . $columns[$key];
            // Set temp entry to modify
            $json[$type]['tca'][$tempKey] = $value;

            if ($coreFieldInPalette) {
                $json[$type]['tca'][$tempKey]['coreField'] = '1';
            }

            // add rte flag if inline and rte
            if ($isInline && ($content['elements']['options'][$key] ?? '') === 'rte') {
                $json[$type]['tca'][$tempKey]['rte'] = '1';
            }

            // Convert range values of timestamp to integers
            if (($json[$type]['tca'][$tempKey]['config']['renderType'] ?? '') === 'inputDateTime' && !isset($json[$type]['tca'][$tempKey]['config']['dbType'])) {
                if (isset($json[$type]['tca'][$tempKey]['config']['range']['lower']) && $json[$type]['tca'][$tempKey]['config']['range']['lower']) {
                    $date = new \DateTime($json[$type]['tca'][$tempKey]['config']['range']['lower']);
                    $json[$type]['tca'][$tempKey]['config']['range']['lower'] = $date->getTimestamp();
                }
                if (isset($json[$type]['tca'][$tempKey]['config']['range']['upper']) && $json[$type]['tca'][$tempKey]['config']['range']['upper']) {
                    $date = new \DateTime($json[$type]['tca'][$tempKey]['config']['range']['upper']);
                    $json[$type]['tca'][$tempKey]['config']['range']['upper'] = $date->getTimestamp();
                }
            }

            // Only add columns to elements if it is no inlinefield
            if (!$isInline) {
                $json[$type]['elements'][$elementKey]['columns'][$key] = $maskKey;
            }

            // If palette on root (not inline) set inlineParent, label and order to array with element as index
            if ($type == $defaultType && $inPalette) {
                // InlineParent
                $palette = $json[$type]['tca'][$tempKey]['inlineParent'];
                $json[$type]['tca'][$tempKey]['inlineParent'] = [];
                $json[$type]['tca'][$tempKey]['inlineParent'][$elementKey] = $palette;

                // Label
                $paletteLabel = $json[$type]['tca'][$tempKey]['label'];
                $json[$type]['tca'][$tempKey]['label'] = [];
                $json[$type]['tca'][$tempKey]['label'][$elementKey] = $paletteLabel;

                // Order
                $json[$type]['tca'][$tempKey]['order'][$elementKey] = $key;
            }

            // If it's an existing field, override with new values but keep other untouched.
            $keyToUse = ($hasConfig || $existingMaskFieldWithNoConfig) ? $maskKey : $columns[$key];
            if (isset($json[$type]['tca'][$keyToUse])) {
                ArrayUtility::mergeRecursiveWithOverrule($json[$type]['tca'][$keyToUse], $json[$type]['tca'][$tempKey]);
            } else {
                $json[$type]['tca'][$keyToUse] = $json[$type]['tca'][$tempKey];
                $json[$type]['tca'][$keyToUse]['key'] = $columns[$key];
            }

            // Inline fields on custom tables need extra order property
            if ($type !== $defaultType && $isInline) {
                $json[$type]['tca'][$keyToUse]['order'] = $key;
            }

            // Fill palette showitem and set label
            if ($inPalette) {
                if (!in_array($keyToUse, $json[$type]['palettes'][$value['inlineParent']]['showitem'] ?? [])) {
                    $json[$type]['palettes'][$value['inlineParent']]['showitem'][] = $keyToUse;
                }
                $labelIndex = array_search(MaskUtility::removeMaskPrefix($value['inlineParent']), $columns);
                $json[$type]['palettes'][$value['inlineParent']]['label'] = $labels[$labelIndex];
            }

            unset($json[$type]['tca'][$tempKey]);
        }
        return $json;
    }

    /**
     * Removes Content-Element
     *
     * @param string $type
     * @param string $key
     * @return array
     */
    public function remove($type, $key): array
    {
        $this->currentKey = $key;
        // Load
        $json = $this->load();

        // Remove
        $columns = $json[$type]['elements'][$key]['columns'];
        unset($json[$type]['elements'][$key]);
        if (is_array($columns)) {
            foreach ($columns as $field) {
                $json = $this->removeField($type, $field, $json);
            }
        }
        $this->currentKey = '';
        return $json;
    }

    /**
     * Hides Content-Element
     *
     * @param string $type
     * @param string $key
     */
    public function hide($type, $key): void
    {
        // Load
        $json = $this->load();
        $json[$type]['elements'][$key]['hidden'] = 1;
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Activates Content-Element
     *
     * @param string $type
     * @param string $key
     */
    public function activate($type, $key): void
    {
        // Load
        $json = $this->load();
        unset($json[$type]['elements'][$key]['hidden']);
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Removes a field from the json, also recursively all inline-fields
     * @param string $table
     * @param string $field
     * @param array $json
     * @return array
     */
    private function removeField($table, $field, $json): array
    {
        $maskKey = $field;
        $field = str_replace('tx_mask_', '', $maskKey);
        $keyToUse = isset($json[$table]['tca'][$field]) ? $field : $maskKey;

        // check if this field is used in any other elements
        $elementsInUse = $this->getElementsWhichUseField($keyToUse, $table);
        $usedInAnotherElement = count($elementsInUse) > 1;

        // Remove inlineParent, label and order
        $inlineParent = $json[$table]['tca'][$keyToUse]['inlineParent'] ?? false;
        if (is_array($inlineParent)) {
            unset($json[$table]['tca'][$keyToUse]['inlineParent'][$this->currentKey]);
            unset($json[$table]['tca'][$keyToUse]['label'][$this->currentKey]);
            unset($json[$table]['tca'][$keyToUse]['order'][$this->currentKey]);
        }

        // if the field is a repeating field, make some exceptions
        if (in_array(($json[$table]['tca'][$maskKey]['config']['type'] ?? ''), ['inline', 'palette'])) {
            $inlineFields = $this->loadInlineFields($maskKey, $this->currentKey);
            // Recursively delete all inline field if possible
            foreach ($inlineFields  as $inlineField) {
                // Only remove if not in use in another element
                if (!$usedInAnotherElement) {
                    $parentTable = ($inlineField['inPalette'] ?? false) ? $table : $inlineField['inlineParent'];
                    $inlineKey = 'tx_mask_' . $inlineField['key'];
                    $json = $this->removeField($parentTable, $inlineKey, $json);
                }
            }
        }

        // then delete the field, if it is not in use in another element
        if (!$usedInAnotherElement) {
            unset($json[$table]['tca'][$maskKey]);
            // Unset typo3 core field
            unset($json[$table]['tca'][$field]);
            unset($json[$table]['sql'][$maskKey]);

            $type = $this->getFormType($maskKey, $this->currentKey, $table);

            // If field is of type inline, also delete table entry
            if ($type == FieldType::INLINE) {
                unset($json[$maskKey]);
            }

            if ($type == FieldType::PALETTE) {
                unset($json[$table]['palettes'][$maskKey]);
            }

            // If field is of type file, also delete entry in sys_file_reference
            if ($type == FieldType::FILE) {
                unset($json['sys_file_reference']['sql'][$maskKey]);
                $json = $this->cleanTable('sys_file_reference', $json);
            }
        }
        return $this->cleanTable($table, $json);
    }

    /**
     * Deletes all the empty settings of a table
     *
     * @param string $table
     * @param array $json
     * @return array
     */
    private function cleanTable($table, $json): array
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
     *
     * @param array $content
     */
    public function update($content): void
    {
        $json = $this->remove($content['type'], $content['orgkey']);
        $this->persist($json);
        $this->persist($this->add($content));
    }

    /**
     * Sorts the json entries
     * @param array $array
     */
    private function sortJson(array &$array): void
    {
        // check if array is not a hash table, because we only want to sort hash tables
        if (
            [] === $array
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
     *
     * @param string $fieldKey Key if Field
     * @param string $elementKey Key of Element
     * @param string $type elementtype
     * @return string formType
     */
    public function getFormType($fieldKey, $elementKey = '', $type = 'tt_content'): string
    {
        $element = [];

        // Check if TCA for mask key exists, else assume it's a core field.
        $tca = $GLOBALS['TCA'][$type]['columns']['tx_mask_' . $fieldKey] ?? [];
        if (!$tca) {
            $tca = $GLOBALS['TCA'][$type]['columns'][$fieldKey] ?? [];
        }

        if ($elementKey) {
            // Load element and TCA of field
            $element = $this->loadElement($type, $elementKey);
            if (array_key_exists('config', $tca) && !$tca['config']) {
                $tca = $element['tca'][$fieldKey] ?? [];
            }
        }

        // if field is in inline table or $GLOBALS["TCA"] is not yet filled, load tca from json
        if (!$tca || !in_array($type, ['tt_content', 'pages'])) {
            $tca = $this->loadField($type, $fieldKey);
            if (!($tca['config'] ?? false)) {
                $tca = $this->loadField($type, 'tx_mask_' . $fieldKey);
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

        if (($tca['rte'] ?? '') === '1' || ($tca['config']['enableRichtext'] ?? '') === '1') {
            return FieldType::RICHTEXT;
        }

        // And decide via different tca settings which formType it is
        switch ($tcaType) {
            case 'input':
                if (($tca['config']['dbType'] ?? '') == 'date') {
                    $formType = FieldType::DATE;
                } elseif (($tca['config']['dbType'] ?? '') == 'datetime') {
                    $formType = FieldType::DATETIME;
                } elseif (($tca['config']['renderType'] ?? '') == 'inputDateTime') {
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
                if ($elementKey && in_array($type, ['tt_content', 'pages'])) {
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
     *
     * @param string $key TCA Type
     * @param string $type elementtype
     * @return array elements in use
     */
    public function getElementsWhichUseField($key, $type = 'tt_content'): array
    {
        $storage = $this->load();

        $elementsInUse = [];
        foreach ($storage[$type]['elements'] ?? [] as $element) {
            foreach ($element['columns'] ?? [] as $column) {
                if ($this->getFormType($column, $element['key'], $type) == FieldType::PALETTE) {
                    foreach ($storage[$type]['palettes'][$column]['showitem'] ?? [] as $item) {
                        if ($item === $key) {
                            $elementsInUse[] = $element;
                        }
                    }
                } elseif ($column === $key) {
                    $elementsInUse[] = $element;
                }
            }
        }
        return $elementsInUse;
    }
}
