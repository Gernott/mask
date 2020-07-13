<?php
declare(strict_types=1);

namespace MASK\Mask\Domain\Repository;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
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
     * @return void
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
     * Load Field
     * @param $type
     * @param $key
     * @return array
     */
    public function loadField($type, $key): ?array
    {
        $json = $this->load();
        return $json[$type]['tca'][$key];
    }

    /**
     * Loads all the inline fields of an inline-field, recursively!
     *
     * @param string $parentKey key of the inline-field
     * @return array
     */
    public function loadInlineFields($parentKey): array
    {
        $json = $this->load();
        $inlineFields = [];
        foreach ($json as $table) {
            if ($table['tca']) {
                foreach ($table['tca'] as $key => $tca) {
                    if ($tca['inlineParent'] === $parentKey) {
                        if ($tca['config']['type'] === 'inline') {
                            $tca['inlineFields'] = $this->loadInlineFields($key);
                        }
                        $tca['maskKey'] = 'tx_mask_' . $tca['key'];
                        $inlineFields[] = $tca;
                    }
                }
            }
        }
        return $inlineFields;
    }

    /**
     * Load Element with all the field configurations
     *
     * @param $type
     * @param $key
     * @return array
     */
    public function loadElement($type, $key): ?array
    {
        $json = $this->load();
        $fields = [];
        $columns = $json[$type]['elements'][$key]['columns'];

        //Check if it is an array before trying to count it
        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $fieldName) {
                $fields[$fieldName] = $json[$type]['tca'][$fieldName];
            }
        }
        if (count($fields) > 0) {
            $json[$type]['elements'][$key]['tca'] = $fields;
        }
        return $json[$type]['elements'][$key];
    }

    /**
     * Adds new Content-Element
     *
     * @param array $content
     * @noinspection NotOptimalIfConditionsInspection
     */
    public function add($content): void
    {
        // Load
        $json = $this->load();

        // Create JSON elements Array:
        foreach ($content['elements'] as $key => $value) {
            // delete columns and labels of irre-fields from elements
            if ($key === 'columns' || $key === 'labels') {
                foreach ($value as $index => $column) {
                    if ($content['tca'][$index]['inlineParent']) {
                        unset($value[$index]);
                    }
                    if (($key === 'labels') && empty($column)
                        && isset($json[$content['type']]['tca'][$content['elements']['columns'][$index]])
                    ) {
                        // If using a mask field with empty label, we have to set the "default" label
                        $label = '';
                        foreach ($json[$content['type']]['elements'] as $element) {
                            if (is_array($element['columns']) && in_array($content['elements']['columns'][$index],
                                    $element['columns'], true)) {
                                $i = array_search($content['elements']['columns'][$index], $element['columns'],
                                    true);
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
            $json[$content['type']]['elements'][$content['elements']['key']][$key] = $value;
        }

        $columns = [];

        // delete columns and labels of irre-fields from elements
        if ($content['elements']['columns']) {
            foreach ($content['elements']['columns'] as $index => $column) {
                if ($content['tca'][$index]['inlineParent']) {
                    unset(
                        $content['elements']['columns'][$index],
                        $content['elements']['labels'][$index]
                    );
                }
                $columns[] = $column;
            }
        }

        // Create JSON sql Array:
        if (is_array($content['sql'])) {
            foreach ($content['sql'] as $table => $sqlArray) {
                foreach ($sqlArray as $index => $type) {
                    $fieldname = 'tx_mask_' . $columns[$index];
                    $json[$table]['sql'][$fieldname][$table][$fieldname] = $type;
                }
            }
        }

        // Create JSON tca Array:
        if (is_array($content['tca'])) {

            foreach ($content['tca'] as $key => $value) {
                $inlineField = false;

                // if this field is inline-field
                if ($value['inlineParent']) {
                    $type = $value['inlineParent'];
                    $inlineField = true;
                } else {
                    $type = $content['type'];
                }

                $json[$type]['tca'][$columns[$key]] = $value;

                // add rte flag if inline and rte
                if ($inlineField) {
                    if ($content['elements']['options'][$key] === 'rte') {
                        $json[$type]['tca'][$columns[$key]]['rte'] = '1';
                    }
                }

                // Only add columns to elements if it is no inlinefield
                if (!$inlineField) {
                    $json[$type]['elements'][$content['elements']['key']]['columns'][$key] = 'tx_mask_' . $columns[$key];
                }
                $json[$type]['tca']['tx_mask_' . $columns[$key]] = $json[$type]['tca'][$columns[$key]];
                $json[$type]['tca']['tx_mask_' . $columns[$key]]['key'] = $columns[$key];

                if ($inlineField) {
                    $json[$type]['tca']['tx_mask_' . $columns[$key]]['order'] = $key;
                }

                unset($json[$type]['tca'][$columns[$key]]);
            }
        }

        // sort content elements by key before saving
        $this->sortJson($json);
        $this->write($json);
    }

    /**
     * Removes Content-Element
     *
     * @param string $type
     * @param string $key
     * @param array $remainingFields
     */
    public function remove($type, $key, $remainingFields = []): void
    {
        $this->currentKey = $key;
        // Load
        $json = $this->load();

        // Remove
        $columns = $json[$type]['elements'][$key]['columns'];
        unset($json[$type]['elements'][$key]);
        if (is_array($columns)) {
            foreach ($columns as $field) {
                $json = $this->removeField($type, $field, $json, $remainingFields);
            }
        }
        $this->currentKey = '';
        $this->sortJson($json);
        $this->write($json);
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
     * @param array $remainingFields
     * @return array
     *
     */
    private function removeField($table, $field, $json, $remainingFields = []): array
    {
        // check if this field is used in any other elements
        $elementsInUse = [];
        if ($json[$table]['elements']) {
            foreach ($json[$table]['elements'] as $element) {
                if ($element['columns']) {
                    foreach ($element['columns'] as $column) {
                        if ($column === $field) {
                            $elementsInUse[] = $element;
                        }
                    }
                }
            }
        }


        // check if father gets deleted
        $fatherFound = false;
        if ($remainingFields) {
            foreach ($remainingFields as $remainingField) {
                if ($field === 'tx_mask_' . $remainingField) {
                    $fatherFound = true;
                }
            }
        }
        $fatherGetsDeleted = !$fatherFound;

        // if the field is a repeating field, make some exceptions
        if ($json[$table]['tca'][$field]['config']['type'] === 'inline') {
            $inlineFields = $this->loadInlineFields($field);
            if ($inlineFields) {
                // Recursively delete all inline-fields if necessary
                foreach ($inlineFields as $inlineField) {
                    $found = false;
                    // check if the fields are really deleted, or if they are just deleted temporarly for update action
                    if ($remainingFields) {
                        foreach ($remainingFields as $remainingField) {
                            if ($inlineField['key'] === $remainingField) {
                                $found = true;
                            }
                        }
                    }
                    if ($found) {
                        // was not really deleted => can be deleted temporarly because it will be readded
                        $json = $this->removeField($inlineField['inlineParent'], 'tx_mask_' . $inlineField['key'],
                            $json);
                    } else {
                        // was really deleted and can only be deleted if father is not in use in another element
                        if (($fatherGetsDeleted && count($elementsInUse) == 0) || !$fatherGetsDeleted) {
                            $json = $this->removeField($inlineField['inlineParent'], 'tx_mask_' . $inlineField['key'],
                                $json);
                        }
                    }
                }
            }
        }

        // then delete the field, if it is not in use in another element
        if (count($elementsInUse) < 1) {
            unset($json[$table]['tca'][$field]);
            unset($json[$table]['sql'][$field]);

            $type = $this->getFormType($field, $this->currentKey, $table);

            // If field is of type inline, also delete table entry
            if ($type === 'Inline') {
                unset($json[$field]);
            }

            // If field is of type file, also delete entry in sys_file_reference
            if ($type === 'File') {
                unset($json['sys_file_reference']['sql'][$field]);
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
        if ($json[$table]['tca'] && count($json[$table]['tca']) < 1) {
            unset($json[$table]['tca']);
        }
        if ($json[$table]['sql'] && count($json[$table]['sql']) < 1) {
            unset($json[$table]['sql']);
        }
        if ($json[$table] && count($json[$table]) < 1) {
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
        $this->remove($content['type'], $content['orgkey'], $content['elements']['columns']);
        $this->add($content);
    }

    /**
     * Sorts the json entries
     * @param array $array
     * @return void
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
     * @param FieldHelper $instance
     * @return string formType
     */
    public function getFormType($fieldKey, $elementKey = '', $type = 'tt_content'): string
    {
        $formType = 'String';
        $element = [];

        // Load element and TCA of field
        if ($elementKey) {
            $element = $this->loadElement($type, $elementKey);
        }

        // load tca for field from $GLOBALS
        $tca = $GLOBALS['TCA'][$type]['columns'][$fieldKey] ?? [];
        if (array_key_exists('config', $tca) && !$tca['config']) {
            $tca = $GLOBALS['TCA'][$type]['columns']['tx_mask_' . $fieldKey] ?? [];
        }
        if (array_key_exists('config', $tca) && !$tca['config']) {
            $tca = $element['tca'][$fieldKey] ?? [];
        }

        // if field is in inline table or $GLOBALS["TCA"] is not yet filled, load tca from json
        if (!$tca || !in_array($type, ['tt_content', 'pages'])) {
            $tca = $this->loadField($type, $fieldKey);
            if (!$tca['config']) {
                $tca = $this->loadField($type, 'tx_mask_' . $fieldKey);
            }
        }

        $tcaType = $tca['config']['type'];
        $evals = [];
        if (isset($tca['config']['eval'])) {
            $evals = explode(',', $tca['config']['eval']);
        }


        if (($tca['options'] ?? '') === 'file') {
            $formType = 'File';
        }

        // And decide via different tca settings which formType it is
        switch ($tcaType) {
            case 'input':
                if (in_array(strtolower('int'), $evals, true)) {
                    $formType = 'Integer';
                } else {
                    if (in_array(strtolower('double2'), $evals, true)) {
                        $formType = 'Float';
                    } else {
                        if (in_array(strtolower('date'), $evals, true)) {
                            $formType = 'Date';
                        } else {
                            if (in_array(strtolower('datetime'), $evals, true)) {
                                $formType = 'Datetime';
                            } else {
                                if (isset($tca['config']['renderType']) && $tca['config']['renderType'] === 'inputLink') {
                                    $formType = 'Link';
                                } else {
                                    $formType = 'String';
                                }
                            }
                        }
                    }
                }
                break;
            case 'text':
                $formType = 'Text';
                if (in_array($type, ['tt_content', 'pages'])) {
                    if ($elementKey) {
                        $fieldNumberKey = -1;
                        if (is_array($element['columns'])) {
                            foreach ($element['columns'] as $numberKey => $column) {
                                if ($column === $fieldKey) {
                                    $fieldNumberKey = $numberKey;
                                }
                            }
                        }

                        if ($fieldNumberKey >= 0) {
                            $option = $element['options'][$fieldNumberKey];
                            if ($option === 'rte') {
                                $formType = 'Richtext';
                            } else {
                                $formType = 'Text';
                            }
                        }
                    } else {
                        $formType = 'Text';
                    }
                } else {
                    if ($tca['rte']) {
                        $formType = 'Richtext';
                    } else {
                        $formType = 'Text';
                    }
                }
                break;
            case 'check':
                $formType = 'Check';
                break;
            case 'radio':
                $formType = 'Radio';
                break;
            case 'select':
                $formType = 'Select';
                break;
            case 'inline':
                if ($tca['config']['foreign_table'] === 'sys_file_reference') {
                    $formType = 'File';
                } else {
                    if ($tca['config']['foreign_table'] === 'tt_content') {
                        $formType = 'Content';
                    } else {
                        $formType = 'Inline';
                    }
                }
                break;
            case 'tab':
                $formType = 'Tab';
                break;
            case 'group':
            case 'none':
            case 'passthrough':
            case 'user':
            case 'flex':
            default:
                break;
        }
        return $formType;
    }
}
