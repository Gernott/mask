<?php
declare(strict_types=1);

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

use Exception;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use MASK\Mask\Helper\FieldHelper;

/**
 * Generates all the tca needed for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class TcaCodeGenerator extends AbstractCodeGenerator
{
    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    public function __construct(StorageRepository $storageRepository = null, FieldHelper $fieldHelper = null)
    {
        parent::__construct($storageRepository);
        if ($fieldHelper) {
            $this->fieldHelper = $fieldHelper;
        } else {
            $this->fieldHelper = GeneralUtility::makeInstance(FieldHelper::class);
        }
    }

    /**
     * Generates and sets the correct tca for all the inline fields
     */
    public function setInlineTca(): void
    {
        $json = $this->storageRepository->load();
        foreach ($json as $table => $subJson) {
            if (!MaskUtility::isMaskIrreTable($table)) {
                continue;
            }
            // Generate Table TCA
            $processedTca = $this->processTableTca($table, $subJson['tca']);
            $parentTable = $this->fieldHelper->getFieldType($table);

            // Adjust TCA-Template
            $tableTca = self::getTcaTemplate();
            $appendLanguageTab = ',--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language';
            $appendAccessTab = ',--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access';

            $tableTca['ctrl']['title'] = $table;
            $tableTca['ctrl']['label'] = $processedTca['label'];
            $tableTca['ctrl']['iconfile'] = 'EXT:mask/Resources/Public/Icons/Extension.svg';

            // hide table in list view
            $tableTca['ctrl']['hideTable'] = true;
            $tableTca['types']['1']['showitem'] = $processedTca['showitem'] . $appendLanguageTab . $appendAccessTab;

            $tableTca['columns']['l10n_parent']['config']['foreign_table'] = $table;
            $tableTca['columns']['l10n_parent']['config']['foreign_table_where'] = "AND $table.pid=###CURRENT_PID### AND $table.sys_language_uid IN (-1, 0)";

            $tableTca['columns']['parentid']['config']['foreign_table'] = $parentTable;
            $tableTca['columns']['parentid']['config']['foreign_table_where'] = "AND $parentTable.pid=###CURRENT_PID### AND $parentTable.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)";

            // Add some stuff we need to make irre work like it should
            $GLOBALS['TCA'][$table] = $tableTca;

            // set label for inline if defined
            $inlineLabel = $json['tt_content']['tca'][$table]['inlineLabel'] ?? '';
            if ($inlineLabel && in_array($inlineLabel, array_keys($subJson['tca']))) {
                $GLOBALS['TCA'][$table]['ctrl']['label'] = $inlineLabel;
            }

            // set icon for inline
            $inlineIcon = $json['tt_content']['tca'][$table]['inlineIcon'] ?? '';
            if ($inlineIcon) {
                $GLOBALS['TCA'][$table]['ctrl']['iconfile'] = $inlineIcon;
            }

            // Generate Field TCA
            $fieldTCA = $this->generateFieldsTca($subJson['tca']);
            ExtensionManagementUtility::addTCAcolumns($table, $fieldTCA);
        }
    }

    /**
     * Generates and sets the tca for all the content-elements
     *
     * @param array $tca
     * @noinspection PhpUnused
     */
    public function setElementsTca($tca): void
    {

        $fieldHelper = GeneralUtility::makeInstance(FieldHelper::class);
        $defaultTabs = ',--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended';

        // add gridelements fields, to make mask work with gridelements out of the box
        $gridelements = '';
        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $gridelements = ', tx_gridelements_container, tx_gridelements_columns';
        }
        if ($tca) {

            // add new group in CType selectbox
            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
                'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
                '--div--'
            ];

            foreach ($tca as $elementvalue) {
                if (!$elementvalue['hidden']) {

                    $prependTabs = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,';
                    $fieldArray = [];
                    $label = $elementvalue['shortLabel'] ?: $elementvalue['label']; // Optional shortLabel

                    // add new entry in CType selectbox
                    ExtensionManagementUtility::addPlugin([
                        $label,
                        'mask_' . $elementvalue['key'],
                        'mask-ce-' . $elementvalue['key']
                    ], 'CType', 'mask');

                    // add all the fields that should be shown
                    if (is_array($elementvalue['columns'])) {
                        foreach ($elementvalue['columns'] as $index => $fieldKey) {

                            // check if this field is of type tab
                            $formType = $fieldHelper->getFormType($fieldKey, $elementvalue['key'], 'tt_content');
                            if ($formType === 'Tab') {
                                $label = $fieldHelper->getLabel($elementvalue['key'], $fieldKey, 'tt_content');
                                // if a tab is in the first position then change the name of the general tab
                                if ($index === 0) {
                                    $prependTabs = '--div--;' . $label . ',' . $prependTabs;
                                } else {
                                    // otherwise just add new tab
                                    $fieldArray[] = '--div--;' . $label;
                                }
                            } else {
                                $fieldArray[] = $fieldKey;
                            }
                        }
                    }
                    $fields = implode(',', $fieldArray);

                    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mask_' . $elementvalue['key']] = 'mask-ce-' . $elementvalue['key'];
                    $GLOBALS['TCA']['tt_content']['types']['mask_' . $elementvalue['key']]['columnsOverrides']['bodytext']['config']['enableRichtext'] = 1;
                    $GLOBALS['TCA']['tt_content']['types']['mask_' . $elementvalue['key']]['showitem'] = $prependTabs . $fields . $defaultTabs . $gridelements;
                }
            }
        }
    }

    /**
     * @param string $key
     * @return string
     */
    public function getPageTca(string $key)
    {
        $prependTabs = ',--div--;Content-Fields,';
        $tca = $this->storageRepository->load();
        $columns = $tca['pages']['elements'][$key]['columns'] ?? [];
        for ($i = 0; $i < count($columns); $i++) {
            $fieldKey = $columns[$i];

            // check if this field is of type tab
            $formType = $this->fieldHelper->getFormType($fieldKey, $key, 'pages');
            if ($formType === 'Tab') {
                $label = $this->fieldHelper->getLabel($key, $fieldKey, 'pages');
                // if a tab is in the first position then change the name of the general tab
                if ($i === 0) {
                    $prependTabs = ',--div--;' . $label . ',';
                } else {
                    // otherwise just add new tab
                    $fieldArray[] = '--div--;' . $label;
                }
            } else {
                $fieldArray[] = $fieldKey;
            }
        }

        if (!empty($fieldArray)) {
            $pageFieldString = $prependTabs . implode(',', $fieldArray);
        } else {
            $pageFieldString = $prependTabs;
        }
        return $pageFieldString;
    }

    /**
     * Generates the TCA for fields
     *
     * @param array $tca
     * @return array
     * @throws Exception
     */
    public function generateFieldsTca($tca): array
    {
        $generalUtility = GeneralUtility::makeInstance(MaskUtility::class);
        $columns = [];
        if ($tca) {
            foreach ($tca as $tcakey => $tcavalue) {
                $addToTca = true;
                if ($tcavalue) {
                    foreach ($tcavalue as $fieldkey => $fieldvalue) {
                        // Add File-Config for file-field
                        if ($fieldkey === 'options' && $fieldvalue === 'file') {
                            $fieldName = $tcakey;
                            $customSettingOverride = [
                                'overrideChildTca' => [
                                    'types' => [
                                        '0' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                        '1' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                        '2' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                        '3' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                        '4' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                        '5' => [
                                            'showitem' => '--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
                                        ],
                                    ],
                                ]
                            ];

                            $customSettingOverride['appearance'] = $tcavalue['config']['appearance'];
                            if ($customSettingOverride['appearance']['fileUploadAllowed'] === '') {
                                $customSettingOverride['appearance']['fileUploadAllowed'] = false;
                            }
                            if ($customSettingOverride['appearance']['useSortable'] === '') {
                                $customSettingOverride['appearance']['useSortable'] = 0;
                            } else {
                                $customSettingOverride['appearance']['useSortable'] = 1;
                            }

                            if (!empty($tcavalue['config']['filter']['0']['parameters']['allowedFileExtensions'])) {
                                $allowedFileExtensions = $tcavalue['config']['filter']['0']['parameters']['allowedFileExtensions'];
                            } else {
                                $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                            }
                            $columns[$tcakey]['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($fieldName,
                                $customSettingOverride, $allowedFileExtensions);
                        }

                        // check if field is actually a tab
                        if (isset($fieldvalue['type']) && $fieldvalue['type'] === 'tab') {
                            $addToTca = false;
                        }

                        // Fill missing tablename in TCA-Config for inline-fields
                        if ($fieldkey === 'config' && $tcavalue[$fieldkey]['foreign_table'] === '--inlinetable--') {
                            $tcavalue[$fieldkey]['foreign_table'] = $tcakey;
                        }

                        // set date ranges if date or datetime field
                        if ($fieldkey === 'config' && ($tcavalue[$fieldkey]['dbType'] === 'date' || $tcavalue[$fieldkey]['dbType'] === 'datetime')) {
                            if (!empty($tcavalue[$fieldkey]['range']['upper'])) {
                                $date = new \DateTime($tcavalue[$fieldkey]['range']['upper']);
                                $tcavalue[$fieldkey]['range']['upper'] = $date->getTimestamp() + 86400;
                            }
                            if (!empty($tcavalue[$fieldkey]['range']['lower'] !== '')) {
                                $date = new \DateTime($tcavalue[$fieldkey]['range']['lower']);
                                $tcavalue[$fieldkey]['range']['lower'] = $date->getTimestamp() + 86400;
                            }
                        }

                        // set correct rendertype if format (code highlighting) is set in text tca
                        if ($fieldkey === 'config' && !empty($tcavalue[$fieldkey]['format'])) {
                            $tcavalue[$fieldkey]['renderType'] = 't3editor';
                        }

                        // make some adjustmens to content fields
                        if (
                            $fieldkey === 'config' &&
                            $tcavalue[$fieldkey]['foreign_table'] === 'tt_content' &&
                            $tcavalue[$fieldkey]['type'] === 'inline'
                        ) {
                            $tcavalue[$fieldkey]['foreign_field'] = $tcakey . '_parent';
                            if ($tcavalue['cTypes']) {
                                $tcavalue[$fieldkey]['overrideChildTca']['columns']['CType']['config']['default'] = reset($tcavalue['cTypes']);
                            }
                        }

                        // merge user inputs with file array
                        if (!is_array($columns[$tcakey])) {
                            $columns[$tcakey] = [];
                        } else {
                            ArrayUtility::mergeRecursiveWithOverrule($columns[$tcakey], $tcavalue);
                        }

                        if (isset($columns[$tcakey]['rte'])) {
                            $columns[$tcakey]['config']['softref'] = 'rtehtmlarea_images,typolink_tag,images,email[subst],url';
                        }

                        // Unset some values that are not needed in TCA
                        unset(
                            $columns[$tcakey]['options'],
                            $columns[$tcakey]['key'],
                            $columns[$tcakey]['rte'],
                            $columns[$tcakey]['inlineParent'],
                            $columns[$tcakey]['inlineLabel'],
                            $columns[$tcakey]['inlineIcon'],
                            $columns[$tcakey]['cTypes']
                        );

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
     * Processes the TCA for Inline-Tables
     *
     * @param string $table
     * @param array $tca
     * @return array
     */
    public function processTableTca($table, $tca): array
    {
        $generalTab = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general';

        uasort($tca, static function ($columnA, $columnB) {
            $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
            $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
            return $a - $b;
        });

        $fields = [];
        $i = 0;
        foreach ($tca as $fieldKey => $configuration) {
            // check if this field is of type tab
            $formType = $this->fieldHelper->getFormType($fieldKey, '', $table);
            if ($formType === 'Tab') {
                $label = $configuration['label'];
                // if a tab is in the first position then change the name of the general tab
                if ($i === 0) {
                    $generalTab = '--div--;' . $label;
                } else {
                    // otherwise just add new tab
                    $fields[] = '--div--;' . $label;
                }
            } else {
                $fields[] = $fieldKey;
            }
            $i++;
        }

        // take first field for inline label
        $labelField = '';
        if ($fields) {
            $labelField = MaskUtility::getFirstNoneTabField($fields);
        }

        return [
            'label' => $labelField,
            'showitem' => $generalTab . ',' . implode(',', $fields),
        ];
    }

    /**
     * Return array with mask irre tables.
     */
    public function getMaskIrreTables(): array
    {
        $configuration = $this->storageRepository->load();
        $irreTables = array_filter(array_keys($configuration), function ($table) {
            return MaskUtility::isMaskIrreTable($table);
        });
        return array_values($irreTables);
    }

    public static function getTcaTemplate()
    {
        return [
            'ctrl' => [
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'versioningWS' => true,
                'origUid' => 't3_origuid',
                'languageField' => 'sys_language_uid',
                'transOrigPointerField' => 'l10n_parent',
                'translationSource' => 'l10n_source',
                'transOrigDiffSourceField' => 'l10n_diffsource',
                'delete' => 'deleted',
                'enablecolumns' => [
                    'disabled' => 'hidden',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime'
                ],
            ],
            'palettes' => [
                'language' => [
                    'showitem' => '
                        sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,l18n_parent
                    ',
                ],
                'hidden' => [
                    'showitem' => '
                        hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden
                    ',
                ],
                'access' => [
                    'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
                    'showitem' => '
                        starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                        endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel
                    ',
                ],
            ],
            'columns' => [
                'sys_language_uid' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'special' => 'languages',
                        'items' => [
                            [
                                'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                                -1,
                                'flags-multiple'
                            ],
                        ],
                        'default' => 0,
                    ]
                ],
                'l10n_parent' => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => [
                            [
                                '',
                                0
                            ]
                        ],
                        'default' => 0
                    ]
                ],
                'l10n_diffsource' => [
                    'config' => [
                        'type' => 'passthrough'
                    ],
                ],
                'hidden' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
                    'config' => [
                        'type' => 'check',
                        'renderType' => 'checkboxToggle',
                        'items' => [
                            [
                                0 => '',
                                1 => '',
                                'invertStateDisplay' => true
                            ]
                        ],
                    ]
                ],
                'starttime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime,int',
                        'default' => 0
                    ],
                    'l10n_mode' => 'exclude',
                    'l10n_display' => 'defaultAsReadonly'
                ],
                'endtime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime,int',
                        'default' => 0,
                        'range' => [
                            'upper' => mktime(0, 0, 0, 1, 1, 2038)
                        ]
                    ],
                    'l10n_mode' => 'exclude',
                    'l10n_display' => 'defaultAsReadonly'
                ],
                'parentid' => [
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => [
                            ['', 0],
                        ],
                        'default' => 0
                    ],
                ],
                'parenttable' => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
                'sorting' => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
            ],
        ];
    }
}
