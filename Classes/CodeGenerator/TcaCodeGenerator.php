<?php

namespace MASK\Mask\CodeGenerator;

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
 *  the Free Software Foundation; either version 3 of the License, or
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
 * Generates all the tca needed for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class TcaCodeGenerator extends AbstractCodeGenerator
{

    /**
     * Generates and sets the correct tca for all the inline fields
     * @author Benjamin Butschell <bb@webprofil.at>
     * @param array $json
     */
    public function setInlineTca($json)
    {
        // Generate TCA for IRRE Fields and Tables
        $notIrreTables = array("pages", "tt_content", "sys_file_reference");
        if ($json) {
            foreach ($json as $table => $subJson) {
                $fieldTCA = array();
                if (array_search($table, $notIrreTables) === FALSE) {
                    // Generate Table TCA
                    $this->generateTableTca($table, $subJson["tca"]);
                    // Generate Field TCA
                    $fieldTCA = $this->generateFieldsTca($subJson["tca"]);
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $fieldTCA);

                    // set label for inline
                    if (!empty($json["tt_content"]["tca"][$table]["inlineLabel"])) {
                        $fields = array_keys($subJson["tca"]);
                        if (array_search($json["tt_content"]["tca"][$table]["inlineLabel"], $fields) !== FALSE) {
                            $GLOBALS["TCA"][$table]["ctrl"]['label'] = $json["tt_content"]["tca"][$table]["inlineLabel"];
                        }
                    }
                    // set icon for inline
                    if (!empty($json["tt_content"]["tca"][$table]["inlineIcon"])) {
                        $GLOBALS["TCA"][$table]["ctrl"]['iconfile'] = $json["tt_content"]["tca"][$table]["inlineIcon"];
                    } else {
                        $GLOBALS["TCA"][$table]["ctrl"]['iconfile'] = "EXT:mask/ext_icon.svg";
                    }

                    // hide table in list view
                    $GLOBALS["TCA"][$table]['ctrl']['hideTable'] = TRUE;
                }
            }
        }
    }

    /**
     * Generates and sets the tca for all the content-elements
     *
     * @param array $tca
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function setElementsTca($tca)
    {

        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $defaultTabs = ",--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended,--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,categories";

        // add gridelements fields, to make mask work with gridelements out of the box
        $gridelements = '';
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')) {
            $gridelements = ', tx_gridelements_container, tx_gridelements_columns';
        }
        if ($tca) {
            foreach ($tca as $elementvalue) {
                if (!$elementvalue["hidden"]) {
                $prependTabs = "--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,";
                $fieldArray = array();
                $label = $elementvalue["shortLabel"]; // Optional shortLabel
                if ($label == "") {
                    $label = $elementvalue["label"];
                }
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array($label, "mask_" . $elementvalue["key"]), "CType", "mask");

                // now add all the fields that should be shown
                if (is_array($elementvalue["columns"])) {
                    foreach ($elementvalue["columns"] as $index => $fieldKey) {

                        // check if this field is of type tab
                        $formType = $fieldHelper->getFormType($fieldKey, $elementvalue["key"], "tt_content");
                        if ($formType == "Tab") {
                            $label = $fieldHelper->getLabel($elementvalue["key"], $fieldKey, "tt_content");
                            // if a tab is in the first position then change the name of the general tab
                            if ($index === 0) {
                                $prependTabs = '--div--;' . $label . "," . $prependTabs;
                            } else {
                                // otherwise just add new tab
                                $fieldArray[] = '--div--;' . $label;
                            }
                        } else {
                            $fieldArray[] = $fieldKey;
                        }
                    }
                }
                $fields = implode(",", $fieldArray);

                $GLOBALS['TCA']["tt_content"]["types"]["mask_" . $elementvalue["key"]]["columnsOverrides"]["bodytext"]["defaultExtras"] = 'richtext:rte_transform[mode=ts_css]';
                $GLOBALS['TCA']["tt_content"]["types"]["mask_" . $elementvalue["key"]]["showitem"] = $prependTabs . $fields . $defaultTabs . $gridelements;
            }
        }
    }
    }

    /**
     * Generates and sets the tca for all the extended pages
     *
     * @param array $tca
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function setPageTca($tca)
    {
        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $prependTabs = "--div--;Content-Fields,";
        if ($tca) {
            $i = 0;
            foreach ($tca as $fieldKey => $config) {
                // no information about which element this field is for at this point
                $elements = $fieldHelper->getElementsWhichUseField($fieldKey, "pages");
                $element = $elements[0];

                // check if this field is of type tab
                $formType = $fieldHelper->getFormType($fieldKey, $element["key"], "pages");
                if ($formType == "Tab") {
                    $label = $fieldHelper->getLabel($element["key"], $fieldKey, "pages");
                    // if a tab is in the first position then change the name of the general tab
                    if ($i === 0) {
                        $prependTabs = '--div--;' . $label . ",";
                    } else {
                        // otherwise just add new tab
                        $fieldArray[] = '--div--;' . $label;
                    }
                } else {
                    $fieldArray[] = $fieldKey;
                }
                $i++;
            }
        }

        if (!empty($fieldArray)) {
            $pageFieldString = $prependTabs . implode(",", $fieldArray);
        } else {
            $pageFieldString = $prependTabs;
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', $pageFieldString);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages_language_overlay', $pageFieldString);
    }

    /**
     * Generates the TCA for fields
     *
     * @param array $tca
     * @return string
     */
    public function generateFieldsTca($tca)
    {
        $generalUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Utility\\GeneralUtility');
        $columns = array();
        if ($tca) {
            foreach ($tca as $tcakey => $tcavalue) {
                $addToTca = true;
                if ($tcavalue) {
                    foreach ($tcavalue as $fieldkey => $fieldvalue) {
                        // Add File-Config for file-field
                        if ($fieldkey == "options" && $fieldvalue == "file") {
                            $fieldName = $tcakey;
                            $customSettingOverride = array(
                                'foreign_types' => array(
                                    '0' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                    '1' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                    '2' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                    '3' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                    '4' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                    '5' => array(
                                        'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                    ),
                                ),
                            );

                            $customSettingOverride["appearance"] = $tcavalue["config"]["appearance"];
                            if ($customSettingOverride["appearance"]["fileUploadAllowed"] == "") {
                                $customSettingOverride["appearance"]["fileUploadAllowed"] = "false";
                            }
                            if ($customSettingOverride["appearance"]["useSortable"] == "") {
                                $customSettingOverride["appearance"]["useSortable"] = "0";
                            } else {
                                $customSettingOverride["appearance"]["useSortable"] = "1";
                            }

                            if ($tcavalue["config"]["filter"]["0"]["parameters"]["allowedFileExtensions"] != "") {
                                $allowedFileExtensions = $tcavalue["config"]["filter"]["0"]["parameters"]["allowedFileExtensions"];
                            } else {
                                $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                            }
                            $columns[$tcakey]["config"] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig($fieldName, $customSettingOverride, $allowedFileExtensions);
                        }

                        // check if field is actually a tab
                        if (isset($fieldvalue["type"]) && $fieldvalue["type"] == "tab") {
                            $addToTca = false;
                        }

                        // Fill missing tablename in TCA-Config for inline-fields
                        if ($fieldkey == "config" && $tcavalue[$fieldkey]["foreign_table"] == "--inlinetable--") {
                            $tcavalue[$fieldkey]["foreign_table"] = $tcakey;
                        }

                        // set date ranges if date or datetime field
                        if ($fieldkey == "config" && ($tcavalue[$fieldkey]["dbType"] == "date" || $tcavalue[$fieldkey]["dbType"] == "datetime")) {
                            if ($tcavalue[$fieldkey]["range"]["upper"] != "") {
                                $date = new \DateTime($tcavalue[$fieldkey]["range"]["upper"]);
                                $tcavalue[$fieldkey]["range"]["upper"] = $date->getTimestamp() + 86400;
                            }
                            if ($tcavalue[$fieldkey]["range"]["lower"] != "") {
                                $date = new \DateTime($tcavalue[$fieldkey]["range"]["lower"]);
                                $tcavalue[$fieldkey]["range"]["lower"] = $date->getTimestamp() + 86400;
                            }
                        }

                        // set correct rendertype if format (code highlighting) is set in text tca
                        if ($fieldkey == "config" && $tcavalue[$fieldkey]["format"] != "") {
                            $tcavalue[$fieldkey]["renderType"] = "t3editor";
                        }

                        // make some adjustmens to content fields
                        if ($fieldkey == "config" && $tcavalue[$fieldkey]["foreign_table"] == "tt_content") {
                            $tcavalue[$fieldkey]["foreign_field"] = $tcakey . "_parent";
                            if ($tcavalue["cTypes"]) {
                                $tcavalue[$fieldkey]["foreign_record_defaults"]["CType"] = reset($tcavalue["cTypes"]);
                            }
                        }

                        // merge user inputs with file array
                        if (!is_array($columns[$tcakey])) {
                            $columns[$tcakey] = array();
                        } else {
                            \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($columns[$tcakey], $tcavalue);
                        }

                        // Unset some values that are not needed in TCA
                        unset($columns[$tcakey]["options"]);
                        unset($columns[$tcakey]["key"]);
                        unset($columns[$tcakey]["rte"]);
                        unset($columns[$tcakey]["inlineParent"]);
                        unset($columns[$tcakey]["inlineLabel"]);
                        unset($columns[$tcakey]["inlineIcon"]);
                        unset($columns[$tcakey]["cTypes"]);

                        $columns[$tcakey] = $generalUtility->removeBlankOptions($columns[$tcakey]);
                        $columns[$tcakey] = $generalUtility->replaceKey($columns[$tcakey], $tcakey);
                    }
                }
                // if this field should not be added to the tca (e.g. tabs)
                if (!$addToTca) {
                    unset($columns[$tcakey]);
                }
            }
        }
        return $columns;
    }

    /**
     * Generates the TCA for Inline-Tables
     *
     * @param string $table
     * @param array $tca
     * @return string
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function generateTableTca($table, $tca)
    {

        $tcaTemplate = array(
            'ctrl' => array(
                'title' => 'IRRE-Table',
                'label' => 'uid',
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'dividers2tabs' => TRUE,
                'versioningWS' => 2,
                'versioning_followPages' => TRUE,
                'languageField' => 'sys_language_uid',
                'transOrigPointerField' => 'l10n_parent',
                'transOrigDiffSourceField' => 'l10n_diffsource',
                'delete' => 'deleted',
                'enablecolumns' => array(
                    'disabled' => 'hidden',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime',
                ),
                'searchFields' => '',
                'dynamicConfigFile' => '',
                'iconfile' => '',
                'requestUpdate' => 'CType'
            ),
            'interface' => array(
                'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, ',
            ),
            'types' => array(
                '1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
            ),
            'palettes' => array(
                '1' => array('showitem' => ''),
            ),
            'columns' => array(
                'sys_language_uid' => array(
                    'exclude' => 1,
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
                    'config' => array(
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'foreign_table' => 'sys_language',
                        'foreign_table_where' => 'ORDER BY sys_language.title',
                        'items' => array(
                            array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
                            array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
                        ),
                    ),
                ),
                'l10n_parent' => array(
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'exclude' => 1,
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
                    'config' => array(
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => array(
                            array('', 0),
                        ),
                        'foreign_table' => 'tx_test_domain_model_murph',
                        'foreign_table_where' => 'AND tx_test_domain_model_murph.pid=###CURRENT_PID### AND tx_test_domain_model_murph.sys_language_uid IN (-1,0)',
                    ),
                ),
                'l10n_diffsource' => array(
                    'config' => array(
                        'type' => 'passthrough',
                    ),
                ),
                't3ver_label' => array(
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
                    'config' => array(
                        'type' => 'input',
                        'size' => 30,
                        'max' => 255,
                    )
                ),
                'hidden' => array(
                    'exclude' => 1,
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
                    'config' => array(
                        'type' => 'check',
                    ),
                ),
                'starttime' => array(
                    'exclude' => 1,
                    'l10n_mode' => 'mergeIfNotBlank',
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
                    'config' => array(
                        'type' => 'input',
                        'size' => 13,
                        'max' => 20,
                        'eval' => 'datetime',
                        'checkbox' => 0,
                        'default' => 0,
                        'range' => array(
                            'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                        ),
                    ),
                ),
                'endtime' => array(
                    'exclude' => 1,
                    'l10n_mode' => 'mergeIfNotBlank',
                    'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
                    'config' => array(
                        'type' => 'input',
                        'size' => 13,
                        'max' => 20,
                        'eval' => 'datetime',
                        'checkbox' => 0,
                        'default' => 0,
                        'range' => array(
                            'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                        ),
                    ),
                ),
                'parentid' => array(
                    'config' => array(
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => array(
                            array('', 0),
                        ),
                        'foreign_table' => 'tt_content',
                        'foreign_table_where' =>
                        'AND tt_content.pid=###CURRENT_PID###
								AND tt_content.sys_language_uid IN (-1,###REC_FIELD_sys_language_uid###)',
                    ),
                ),
                'parenttable' => array(
                    'config' => array(
                        'type' => 'passthrough',
                    ),
                ),
                'sorting' => array(
                    'config' => array(
                        'type' => 'passthrough',
                    ),
                ),
            ),
        );

        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $generalUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Utility\\GeneralUtility');

        // now add all the fields that should be shown
        $prependTabs = "sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, ";
        if ($tca) {
            $i = 0;
            foreach ($tca as $fieldKey => $configuration) {
                // check if this field is of type tab
                $formType = $fieldHelper->getFormType($fieldKey, "", $table);
                if ($formType == "Tab") {
                    $label = $configuration["label"];
                    // if a tab is in the first position then change the name of the general tab
                    if ($i === 0) {
                        $prependTabs = '--div--;' . $label . "," . $prependTabs;
                    } else {
                        // otherwise just add new tab
                        $fields[] = '--div--;' . $label;
                    }
                } else {
                    $fields[] = $fieldKey;
                }
                $i++;
            }
        }

        // take first field for inline label
        if ($fields) {
            $labelField = $generalUtility->getFirstNoneTabField($fields);
        }

        // get parent table of this inline table
        $parentTable = $fieldHelper->getFieldType($table);

        // Adjust TCA-Template
        $tableTca = $tcaTemplate;

        $tableTca["ctrl"]["title"] = $table;
        $tableTca["ctrl"]["label"] = $labelField;
        $tableTca["ctrl"]["searchFields"] = implode(",", $fields);
        $tableTca["interface"]["showRecordFieldList"] = "sys_language_uid, l10n_parent, l10n_diffsource, hidden, " . implode(", ", $fields);
        $tableTca["types"]["1"]["showitem"] = $prependTabs . implode(", ", $fields) . ", --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime";

        $tableTca["columns"]["l10n_parent"]["config"]["foreign_table"] = $table;
        $tableTca["columns"]["l10n_parent"]["config"]["foreign_table_where"] = 'AND ' . $table . '.pid=###CURRENT_PID### AND ' . $table . '.sys_language_uid IN (-1,0)';

        $tableTca["columns"]["parentid"]["config"]["foreign_table"] = $parentTable;
        $tableTca["columns"]["parentid"]["config"]["foreign_table_where"] = 'AND ' . $parentTable . '.pid=###CURRENT_PID### AND ' . $parentTable . '.sys_language_uid IN (-1,###REC_FIELD_sys_language_uid###)';

        // Add some stuff we need to make irre work like it should
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($table);
        $GLOBALS["TCA"][$table] = $tableTca;
    }
}
