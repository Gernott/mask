<?php

namespace MASK\Mask\Helper;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Methods for types of fields in mask (string, rte, repeating, ...)
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class FieldHelper
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * @param \MASK\Mask\Domain\Repository\StorageRepository $storageRepository
     */
    public function __construct(\MASK\Mask\Domain\Repository\StorageRepository $storageRepository = NULL)
    {
        if (!$storageRepository) {
            $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        } else {
            $this->storageRepository = $storageRepository;
        }
    }

    /**
     * Returns all elements that use this field
     *
     * @param string $key TCA Type
     * @param string $type elementtype
     * @return array elements in use
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getElementsWhichUseField($key, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();

        $elementsInUse = array();
        if ($storage[$type]["elements"]) {
            foreach ($storage[$type]["elements"] as $element) {
                if ($element["columns"]) {
                    foreach ($element["columns"] as $column) {
                        if ($column == $key) {
                            $elementsInUse[] = $element;
                        }
                    }
                }
            }
        }
        return $elementsInUse;
    }

    /**
     * Returns the label of a field in an element
     *
     * @param string $elementKey Key of Element
     * @param string $fieldKey Key if Field
     * @param string $type elementtype
     * @return string Label
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getLabel($elementKey, $fieldKey, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();
        $fieldIndex = -1;
        if (count($storage[$type]["elements"][$elementKey]["columns"]) > 0) {
            foreach ($storage[$type]["elements"][$elementKey]["columns"] as $index => $column) {
                if ($column == $fieldKey) {
                    $fieldIndex = $index;
                }
            }
        }
        if ($fieldIndex >= 0) {
            $label = $storage[$type]["elements"][$elementKey]["labels"][$fieldIndex];
        } else {
            $label = "";
        }
        return $label;
    }

    /**
     * Returns the formType of a field in an element
     *
     * @param string $fieldKey Key if Field
     * @param string $elementKey Key of Element
     * @param string $type elementtype
     * @return string formType
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getFormType($fieldKey, $elementKey = "", $type = "tt_content")
    {
        $formType = "String";

        // Load element and TCA of field
        if ($elementKey) {
            $element = $this->storageRepository->loadElement($type, $elementKey);
        }

        // load tca for field from $GLOBALS
        $tca = $GLOBALS["TCA"][$type]["columns"][$fieldKey];
        if (!$tca["config"]) {
            $tca = $GLOBALS["TCA"][$type]["columns"]["tx_mask_" . $fieldKey];
        }
        if (!$tca["config"]) {
            $tca = $element["tca"][$fieldKey];
        }

        // if field is in inline table or $GLOBALS["TCA"] is not yet filled, load tca from json
        if (!in_array($type, array("tt_content", "pages")) || $tca == null) {
            $tca = $this->storageRepository->loadField($type, $fieldKey);
            if (!$tca["config"]) {
                $tca = $this->storageRepository->loadField($type, "tx_mask_" . $fieldKey);
            }
        }

        $tcaType = $tca["config"]["type"];
        $evals = explode(",", $tca["config"]["eval"]);

        if ($tca["options"] == "file") {
            $formType = "File";
        }

        // And decide via different tca settings which formType it is
        switch ($tcaType) {
            case "input":
                $formType = "String";
                if (array_search(strtolower("int"), $evals) !== FALSE) {
                    $formType = "Integer";
                } else if (array_search(strtolower("double2"), $evals) !== FALSE) {
                    $formType = "Float";
                } else if (array_search(strtolower("date"), $evals) !== FALSE) {
                    $formType = "Date";
                } else if (array_search(strtolower("datetime"), $evals) !== FALSE) {
                    $formType = "Datetime";
                } else {
                    if (isset($tca["config"]["renderType"]) && $tca["config"]["renderType"] === "inputLink") {
                        $formType = "Link";
                    } else {
                        $formType = "String";
                    }
                }
                break;
            case "text":
                $formType = "Text";
                if (in_array($type, array("tt_content", "pages"))) {
                    if ($elementKey) {
                        $fieldNumberKey = -1;
                        if (is_array($element["columns"])) {
                            foreach ($element["columns"] as $numberKey => $column) {
                                if ($column == $fieldKey) {
                                    $fieldNumberKey = $numberKey;
                                }
                            }
                        }

                        if ($fieldNumberKey >= 0) {
                            $option = $element["options"][$fieldNumberKey];
                            if ($option == "rte") {
                                $formType = "Richtext";
                            } else {
                                $formType = "Text";
                            }
                        }
                    } else {
                        $formType = "Text";
                    }
                } else {
                    if ($tca["rte"]) {
                        $formType = "Richtext";
                    } else {
                        $formType = "Text";
                    }
                }
                break;
            case "check":
                $formType = "Check";
                break;
            case "radio":
                $formType = "Radio";
                break;
            case "select":
                $formType = "Select";
                break;
            case "group":
                break;
            case "none":
                break;
            case "passthrough":
                break;
            case "user":
                break;
            case "flex":
                break;
            case "inline":
                $formType = "Inline";
                if ($tca["config"]["foreign_table"] == "sys_file_reference") {
                    $formType = "File";
                } else if($tca["config"]["foreign_table"] == "tt_content") {
                    $formType = "Content";
                } else {
                    $formType = "Inline";
                }
                break;
            case "tab":
                $formType = "Tab";
                break;
            default:
                break;
        }
        return $formType;
    }

    /**
     * Returns type of field (tt_content or pages)
     *
     * @param string $fieldKey key of field
     * @param string $elementKey key of element
     * @return string $fieldType returns fieldType or null if not found
     * @return string $excludeInlineFields only search in tt_content and pages
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getFieldType($fieldKey, $elementKey = "", $excludeInlineFields = false)
    {
        $storage = $this->storageRepository->load();

        // get all possible types (tables)
        if ($storage && !$excludeInlineFields) {
            $types = array_keys($storage);
        } else {
            $types = array();
        }
        $types[] = "pages";
        $types[] = "tt_content";
        $types = array_unique($types);

        $fieldType = "";
        $found = FALSE;
        foreach ($types as $type) {
            if ($storage[$type]["elements"] && !$found) {
                foreach ($storage[$type]["elements"] as $element) {

                    // if this is the element we search for, or no special element was given,
                    // and the element has columns and the fieldType wasn't found yet
                    if (($element["key"] == $elementKey || $elementKey == "") && $element["columns"] && !$found) {

                        foreach ($element["columns"] as $column) {
                            if ($column == $fieldKey && !$found) {
                                $fieldType = $type;
                                $found = TRUE;
                            }
                        }
                    }
                }
            } else if (is_array($storage[$type]["tca"][$fieldKey])) {
                $fieldType = $type;
                $found = TRUE;
            }
        }
        return $fieldType;
    }

    /**
     * Returns all fields of a type from a table
     *
     * @param string $key TCA Type
     * @param string $type elementtype
     * @return array fields
     */
    public function getFieldsByType($key, $type)
    {
        $storage = $this->storageRepository->load();
        if (empty($storage[$type]) || empty($storage[$type]['tca'])) {
            return [];
        }

        $fields = [];
        foreach ($storage[$type]['tca'] as $field => $config) {
            if ($config['config']['type'] !== strtolower($key)) {
                continue;
            }

            $elements = $this->getElementsWhichUseField($field, $type);
            if (empty($elements)) {
                continue;
            }

            $fields[] = [
                'field' => $field,
                'label' => $this->getLabel($elements[0]['key'], $field, $type),
            ];
        }

        return $fields;
    }
}
