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

namespace MASK\Mask\CodeGenerator;

use Exception;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Generates all the tca needed for mask content elements
 */
class TcaCodeGenerator
{
    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    public function __construct(StorageRepository $storageRepository, FieldHelper $fieldHelper)
    {
        $this->storageRepository = $storageRepository;
        $this->fieldHelper = $fieldHelper;
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
            $fieldTCA = $this->generateFieldsTca($table);
            ExtensionManagementUtility::addTCAcolumns($table, $fieldTCA);
        }
    }

    /**
     * Generates and sets the tca for all the content-elements
     */
    public function setElementsTca(): void
    {
        $json = $this->storageRepository->load();
        $tca = $json['tt_content']['elements'] ?? [];
        $defaultTabs = ',--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended';

        // Add gridelements fields, to make mask work with gridelements out of the box
        $gridelements = '';
        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $gridelements = ', tx_gridelements_container, tx_gridelements_columns';
        }

        // Add new group in CType selectbox
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
            '--div--'
        ];

        foreach ($tca as $elementvalue) {
            if ($elementvalue['hidden'] ?? false) {
                continue;
            }

            $prependTabs = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,';
            $fieldArray = [];
            // Optional shortLabel
            $label = $elementvalue['shortLabel'] ?: $elementvalue['label'];

            // Add new entry in CType selectbox
            ExtensionManagementUtility::addPlugin(
                [
                    $label,
                    'mask_' . $elementvalue['key'],
                    'mask-ce-' . $elementvalue['key']
                ],
                'CType',
                'mask'
            );

            // Add all the fields that should be shown
            foreach ($elementvalue['columns'] ?? [] as $index => $fieldKey) {
                $formType = $this->storageRepository->getFormType($fieldKey, $elementvalue['key'], 'tt_content');
                // Check if this field is of type tab
                if ($formType === 'Tab') {
                    $label = $this->fieldHelper->getLabel($elementvalue['key'], $fieldKey, 'tt_content');
                    // If a tab is in the first position then change the name of the general tab
                    if ($index === 0) {
                        $prependTabs = '--div--;' . $label . ',' . $prependTabs;
                    } else {
                        // Otherwise just add new tab
                        $fieldArray[] = '--div--;' . $label;
                    }
                } else {
                    $fieldArray[] = $fieldKey;
                }
            }
            $fields = implode(',', $fieldArray);

            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mask_' . $elementvalue['key']] = 'mask-ce-' . $elementvalue['key'];
            $GLOBALS['TCA']['tt_content']['types']['mask_' . $elementvalue['key']]['columnsOverrides']['bodytext']['config']['enableRichtext'] = 1;
            $GLOBALS['TCA']['tt_content']['types']['mask_' . $elementvalue['key']]['showitem'] = $prependTabs . $fields . $defaultTabs . $gridelements;
        }
    }

    /**
     * @param string $key
     * @return string
     */
    public function getPageTca(string $key)
    {
        $fieldArray = [];
        $prependTabs = ',--div--;Content-Fields,';
        $tca = $this->storageRepository->load();
        $columns = $tca['pages']['elements'][$key]['columns'] ?? [];
        for ($i = 0; $i < count($columns); $i++) {
            $fieldKey = $columns[$i];

            // check if this field is of type tab
            $formType = $this->storageRepository->getFormType($fieldKey, $key, 'pages');
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
     * @param $table
     * @return array
     * @throws Exception
     */
    public function generateFieldsTca($table): array
    {
        $json = $this->storageRepository->load();
        $tca = $json[$table]['tca'] ?? [];
        $columns = [];
        foreach ($tca as $tcakey => $tcavalue) {
            // Tabs: Ignore.
            if (($tcavalue['config']['type'] ?? '') === 'tab') {
                continue;
            }

            $columns[$tcakey] = [];

            // File: Add file config.
            if (($tcavalue['options'] ?? '') === 'file') {
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

                $customSettingOverride['appearance'] = $tcavalue['config']['appearance'] ?? [];
                $customSettingOverride['appearance']['fileUploadAllowed'] = (bool)($customSettingOverride['appearance']['fileUploadAllowed'] ?? false);
                $customSettingOverride['appearance']['useSortable'] = (bool)($customSettingOverride['appearance']['useSortable'] ?? false);
                $allowedFileExtensions = $tcavalue['config']['filter']['0']['parameters']['allowedFileExtensions'] ?? '';
                if ($allowedFileExtensions === '') {
                    $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                }
                $columns[$tcakey]['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($tcakey, $customSettingOverride, $allowedFileExtensions);
            }

            // Inline (Repeating): Fill missing foreign_table in tca config.
            if (($tcavalue['config']['foreign_table'] ?? '') === '--inlinetable--') {
                $tcavalue['config']['foreign_table'] = $tcakey;
            }

            // Date / DateTime: Set date ranges
            $dbType = $tcavalue['config']['dbType'] ?? '';
            if (($dbType === 'date' || $dbType === 'datetime')) {
                $tcavalue['config']['eval'] .= ',null';
                $format = ($dbType == 'date') ? 'd-m-Y' : 'H:i d-m-Y';
                if ($tcavalue['config']['range']['upper'] ?? false) {
                    $date = \DateTime::createFromFormat($format, $tcavalue['config']['range']['upper']);
                    if ($dbType == 'date') {
                        $date->setTime(0, 0);
                    }
                    $tcavalue['config']['range']['upper'] = $date->getTimestamp();
                }
                if ($tcavalue['config']['range']['lower'] ?? false) {
                    $date = \DateTime::createFromFormat($format, $tcavalue['config']['range']['lower']);
                    if ($dbType == 'date') {
                        $date->setTime(0, 0);
                    }
                    $tcavalue['config']['range']['lower'] = $date->getTimestamp();
                }
            }

            // Text: Set correct rendertype if format (code highlighting) is set.
            if ($tcavalue['config']['format'] ?? false) {
                $tcavalue['config']['renderType'] = 't3editor';
            }

            // Content: Set foreign_field and default CType in select if restricted.
            if (($tcavalue['config']['foreign_table'] ?? '') === 'tt_content' && ($tcavalue['config']['type'] ?? '') === 'inline') {
                $tcavalue['config']['foreign_field'] = $tcakey . '_parent';
                if ($tcavalue['cTypes'] ?? false) {
                    $tcavalue['config']['overrideChildTca']['columns']['CType']['config']['default'] = reset($tcavalue['cTypes']);
                }
            }

            // Merge user inputs with file array (for file type overrides)
            ArrayUtility::mergeRecursiveWithOverrule($columns[$tcakey], $tcavalue);

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

            $columns[$tcakey] = MaskUtility::removeBlankOptions($columns[$tcakey]);
            $columns[$tcakey] = MaskUtility::replaceKey($columns[$tcakey], $tcakey);
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
            $formType = $this->storageRepository->getFormType($fieldKey, '', $table);
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

    /**
     * Add search fields to find mask elements or pages
     */
    public function addSearchFields($table): void
    {
        $json = $this->storageRepository->load();
        $tca = $json[$table]['tca'] ?? [];
        $searchFields = [];

        foreach ($tca as $tcakey => $tcavalue) {
            $formType = $this->storageRepository->getFormType($tcakey, '', $table);
            if (in_array($formType, ['String', 'Text'])) {
                $searchFields[] = $tcakey;
            }
        }

        if ($searchFields) {
            $GLOBALS['TCA'][$table]['ctrl']['searchFields'] .= ',' . implode(',', $searchFields);
        }
    }

    public static function getTcaTemplate()
    {
        return [
            'ctrl' => [
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'editlock' => 'editlock',
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
                    'endtime' => 'endtime',
                    'fe_group' => 'fe_group'
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
                        endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
                        --linebreak--,
                        fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,
                        --linebreak--,editlock
                    ',
                ],
            ],
            'columns' => [
                'editlock' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:editlock',
                    'config' => [
                        'type' => 'check',
                        'renderType' => 'checkboxToggle',
                        'items' => [
                            [
                                0 => '',
                                1 => '',
                            ]
                        ],
                    ]
                ],
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
                'fe_group' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectMultipleSideBySide',
                        'size' => 5,
                        'maxitems' => 20,
                        'items' => [
                            [
                                'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                                -1
                            ],
                            [
                                'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                                -2
                            ],
                            [
                                'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                                '--div--'
                            ]
                        ],
                        'exclusiveKeys' => '-1,-2',
                        'foreign_table' => 'fe_groups',
                    ]
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
