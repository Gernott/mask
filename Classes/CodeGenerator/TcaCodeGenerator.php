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
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\DateUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Resource\File;
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
            if (!AffixUtility::hasMaskPrefix($table)) {
                continue;
            }
            // Enhance boilerplate table tca with user settings
            $GLOBALS['TCA'][$table] = $this->generateTableTca($table, $subJson);
            ExtensionManagementUtility::addTCAcolumns($table, $this->generateFieldsTca($table));
        }
    }

    /**
     * @param string $table
     * @param array $subJson
     * @return array
     */
    public function generateTableTca(string $table, array $subJson): array
    {
        $json = $this->storageRepository->load();

        // Generate Table TCA
        $processedTca = $this->processTableTca($table, $subJson);
        $parentTable = $this->fieldHelper->getFieldType($table);

        // Adjust TCA-Template
        $tableTca = self::getTcaTemplate();
        $appendLanguageTab = ',--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language';
        $appendAccessTab = ',--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access';

        $tableTca['ctrl']['title'] = $table;
        $tableTca['ctrl']['label'] = $processedTca['label'];
        $tableTca['ctrl']['iconfile'] = 'EXT:mask/Resources/Public/Icons/Extension.svg';

        // Hide table in list view
        $tableTca['ctrl']['hideTable'] = true;
        $tableTca['types']['1']['showitem'] = $processedTca['showitem'] . $appendLanguageTab . $appendAccessTab;

        $tableTca['columns']['l10n_parent']['config']['foreign_table'] = $table;
        $tableTca['columns']['l10n_parent']['config']['foreign_table_where'] = "AND $table.pid=###CURRENT_PID### AND $table.sys_language_uid IN (-1, 0)";

        $tableTca['columns']['parentid']['config']['foreign_table'] = $parentTable;
        $tableTca['columns']['parentid']['config']['foreign_table_where'] = "AND $parentTable.pid=###CURRENT_PID### AND $parentTable.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)";

        // Add palettes
        foreach ($subJson['palettes'] ?? [] as $key => $palette) {
            $tableTca['palettes'][$key] = $this->generatePalettesTca($palette, $table);
        }

        // Set label for inline if defined
        $inlineLabel = $json['tt_content']['tca'][$table]['ctrl']['label'] ?? $json['tt_content']['tca'][$table]['inlineLabel'] ?? '';
        if ($inlineLabel && in_array($inlineLabel, array_keys($subJson['tca']))) {
            $tableTca['ctrl']['label'] = $inlineLabel;
        }

        // Set icon for inline
        $inlineIcon = $json['tt_content']['tca'][$table]['ctrl']['iconfile'] ?? $json['tt_content']['tca'][$table]['inlineIcon'] ?? '';
        if ($inlineIcon) {
            $tableTca['ctrl']['iconfile'] = $inlineIcon;
        }
        return $tableTca;
    }

    /**
     * Generates and sets the tca for all the content-elements
     */
    public function setElementsTca(): void
    {
        $json = $this->storageRepository->load();
        $tca = $json['tt_content']['elements'] ?? [];
        $defaultTabs = ',--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended';
        $prependTabs = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,';
        $defaultPalette = '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,';

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

        foreach ($json['tt_content']['palettes'] ?? [] as $key => $palette) {
            $GLOBALS['TCA']['tt_content']['palettes'][$key] = $this->generatePalettesTca($palette, 'tt_content');
        }

        foreach ($tca as $key => $elementvalue) {
            if ($elementvalue['hidden'] ?? false) {
                continue;
            }

            $cTypeKey = AffixUtility::addMaskCTypePrefix($elementvalue['key']);

            // Optional shortLabel
            $label = $elementvalue['shortLabel'] ?: $elementvalue['label'];

            // Add new entry in CType selectbox
            ExtensionManagementUtility::addPlugin(
                [
                    $label,
                    $cTypeKey,
                    'mask-ce-' . $elementvalue['key']
                ],
                'CType',
                'mask'
            );

            // Add all the fields that should be shown
            [$prependTabs, $fields] = $this->generateShowItem($prependTabs, $key, 'tt_content');

            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cTypeKey] = 'mask-ce-' . $elementvalue['key'];
            $GLOBALS['TCA']['tt_content']['types'][$cTypeKey]['columnsOverrides']['bodytext']['config']['enableRichtext'] = 1;
            $GLOBALS['TCA']['tt_content']['types'][$cTypeKey]['showitem'] = $prependTabs . $defaultPalette . $fields . $defaultTabs . $gridelements;
        }
    }

    public function getPagePalettes($key)
    {
        $palettes = [];
        $tca = $this->storageRepository->load();
        $element = $tca['pages']['elements'][$key];
        foreach ($element['columns'] ?? [] as $column) {
            if ($this->storageRepository->getFormType($column, $key, 'pages') == FieldType::PALETTE) {
                $palettes[$column] = $this->generatePalettesTca($tca['pages']['palettes'][$column], 'pages');
            }
        }
        return $palettes;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getPageTca(string $key)
    {
        $prependTabs = '--div--;Content-Fields,';
        [$prependTabs, $fields] = $this->generateShowItem($prependTabs, $key, 'pages');
        return ',' . $prependTabs . $fields;
    }

    protected function generateShowItem($prependTabs, $key, $table)
    {
        $tca = $this->storageRepository->load();
        $element = $tca[$table]['elements'][$key] ?? [];
        $fieldArray = [];
        foreach ($element['columns'] ?? [] as $index => $fieldKey) {
            $formType = $this->storageRepository->getFormType($fieldKey, $element['key'], $table);
            // Check if this field is of type tab
            if ($formType == FieldType::TAB) {
                $label = $this->fieldHelper->getLabel($element['key'], $fieldKey, $table);
                // If a tab is in the first position then change the name of the general tab
                if ($index === 0) {
                    $prependTabs = '--div--;' . $label . ',';
                } else {
                    // Otherwise just add new tab
                    $fieldArray[] = '--div--;' . $label;
                }
            } elseif ($formType == FieldType::PALETTE) {
                $fieldArray[] = '--palette--;;' . $fieldKey;
            } else {
                $fieldArray[] = $fieldKey;
            }
        }
        $fields = implode(',', $fieldArray);
        return [$prependTabs, $fields];
    }

    /**
     * @param $palette
     * @param $table
     * @return array
     */
    protected function generatePalettesTca($palette, $table)
    {
        $showitem = [];
        foreach ($palette['showitem'] as $item) {
            if ($this->storageRepository->getFormType($item, '', $table) == FieldType::LINEBREAK) {
                $showitem[] = '--linebreak--';
            } else {
                $showitem[] = $item;
            }
        }
        return [
            'label' => $palette['label'],
            'showitem' => implode(',', $showitem)
        ];
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
            if (!isset($tcavalue['config'])) {
                continue;
            }

            // Inline: Ignore empty inline fields
            $formType = $this->storageRepository->getFormType($tcakey, '', $table);
            if ($formType != '' && FieldType::cast($formType)->isParentField() && !array_key_exists($tcakey, $json)) {
                continue;
            }

            // Ignore grouping elements
            if (in_array(($tcavalue['config']['type'] ?? ''), FieldType::getConstants()) && FieldType::cast(($tcavalue['config']['type']))->isGroupingField()) {
                continue;
            }

            $columns[$tcakey] = [];

            // File: Add file config.
            if (($tcavalue['options'] ?? '') === 'file') {
                // If imageoverlayPalette is not set (because of updates to newer version) fallback to default behaviour.
                if ($tcavalue['imageoverlayPalette'] ?? true) {
                    $customSettingOverride = [
                        'overrideChildTca' => [
                            'types' => [
                                '0' => [
                                    'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                                ],
                                File::FILETYPE_TEXT => [
                                    'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                                ],
                                File::FILETYPE_IMAGE => [
                                    'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                                ],
                                File::FILETYPE_AUDIO => [
                                    'showitem' => '
                                --palette--;;audioOverlayPalette,
                                --palette--;;filePalette'
                                ],
                                File::FILETYPE_VIDEO => [
                                    'showitem' => '
                                --palette--;;videoOverlayPalette,
                                --palette--;;filePalette'
                                ],
                                File::FILETYPE_APPLICATION => [
                                    'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                                ]
                            ],
                        ],
                    ];
                }

                $customSettingOverride['appearance'] = $tcavalue['config']['appearance'] ?? [];
                $customSettingOverride['appearance']['fileUploadAllowed'] = (bool)($customSettingOverride['appearance']['fileUploadAllowed'] ?? true);
                $customSettingOverride['appearance']['useSortable'] = (bool)($customSettingOverride['appearance']['useSortable'] ?? false);
                // Since mask v7.0.0 the path for allowedFileExtensions has changed to root level. Keep this as fallback.
                $allowedFileExtensions = $tcavalue['allowedFileExtensions'] ?? $tcavalue['config']['filter']['0']['parameters']['allowedFileExtensions'] ?? '';
                if ($allowedFileExtensions === '') {
                    $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                }
                $columns[$tcakey]['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($tcakey, $customSettingOverride, $allowedFileExtensions);
                unset($customSettingOverride);
            }

            // Inline (Repeating): Fill missing foreign_table in tca config.
            if (($tcavalue['config']['foreign_table'] ?? '') === '--inlinetable--') {
                $tcavalue['config']['foreign_table'] = $tcakey;
            }

            // Convert Date and Datetime default and ranges to timestamp
            $dbType = $tcavalue['config']['dbType'] ?? '';
            if (in_array($dbType, ['date', 'datetime'])) {
                $default = $tcavalue['config']['default'] ?? false;
                if ($default) {
                    $tcavalue['config']['default'] = DateUtility::convertStringToTimestampByDbType($dbType, $default);
                }
                $upper = $tcavalue['config']['range']['upper'] ?? false;
                if ($upper) {
                    $tcavalue['config']['range']['upper'] = DateUtility::convertStringToTimestampByDbType($dbType, $upper);
                }
                $lower = $tcavalue['config']['range']['lower'] ?? false;
                if ($lower) {
                    $tcavalue['config']['range']['lower'] = DateUtility::convertStringToTimestampByDbType($dbType, $lower);
                }
            }

            // Text: Set correct rendertype if format (code highlighting) is set.
            if ($tcavalue['config']['format'] ?? false) {
                $tcavalue['config']['renderType'] = 't3editor';
            }

            // RTE: Add softref
            if (FieldType::cast($formType)->equals(FieldType::RICHTEXT)) {
                $tcavalue['config']['softref'] = 'typolink_tag,images,email[subst],url';
            }

            // Content: Set foreign_field and default CType in select if restricted.
            if (($tcavalue['config']['foreign_table'] ?? '') === 'tt_content' && ($tcavalue['config']['type'] ?? '') === 'inline') {
                $parentField = AffixUtility::addMaskParentSuffix($tcakey);
                $tcavalue['config']['foreign_field'] = $parentField;
                if ($table === 'tt_content') {
                    $columns[$parentField] = [
                        'config' => [
                            'type' => 'passthrough'
                        ]
                    ];
                }
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
                $columns[$tcakey]['inPalette'],
                $columns[$tcakey]['order'],
                $columns[$tcakey]['inlineIcon'],
                $columns[$tcakey]['imageoverlayPalette'],
                $columns[$tcakey]['cTypes'],
                $columns[$tcakey]['allowedFileExtensions'],
                $columns[$tcakey]['ctrl']
            );

            // Unset label if it is from palette fields
            if (is_array($columns[$tcakey]['label'] ?? false)) {
                unset($columns[$tcakey]['label']);
            }

            $columns[$tcakey] = MaskUtility::removeBlankOptions($columns[$tcakey]);
            // Exlcude all fields for editors by default
            $columns[$tcakey]['exclude'] = 1;
        }
        return $columns;
    }

    /**
     * Processes the TCA for Inline-Tables
     *
     * @param string $table
     * @param array $json
     * @return array
     */
    public function processTableTca($table, $json): array
    {
        $generalTab = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general';

        uasort($json['tca'], static function ($columnA, $columnB) {
            $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
            $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
            return $a - $b;
        });

        $fields = [];
        $i = 0;
        $fieldsInPaletteToIgnore = [];
        foreach ($json['tca'] as $fieldKey => $configuration) {
            if (in_array($fieldKey, $fieldsInPaletteToIgnore)) {
                continue;
            }
            // check if this field is of type tab
            $formType = $this->storageRepository->getFormType($fieldKey, '', $table);
            if ($formType == FieldType::TAB) {
                $label = $configuration['label'];
                // if a tab is in the first position then change the name of the general tab
                if ($i === 0) {
                    $generalTab = '--div--;' . $label;
                } else {
                    // otherwise just add new tab
                    $fields[] = '--div--;' . $label;
                }
            } elseif ($formType == FieldType::PALETTE) {
                $fields[] = '--palette--;;' . $fieldKey;
                $fieldsInPaletteToIgnore = array_merge($fieldsInPaletteToIgnore, $json['palettes'][$fieldKey]['showitem'] ?? []);
            } elseif (!($configuration['inPalette'] ?? false)) {
                $fields[] = $fieldKey;
            }
            $i++;
        }

        // take first field for inline label
        $labelField = '';
        if ($fields) {
            $labelField = MaskUtility::getFirstNoneTabField($fields);
            // If first field is palette, get label of first field in this palette.
            if (strpos($labelField, '--palette--;;') === 0) {
                $palette = str_replace('--palette--;;', '', $labelField);
                $paletteFields = $json['palettes'][$palette]['showitem'];
                $labelField = $paletteFields[0];
            }
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
            return AffixUtility::hasMaskPrefix($table);
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
            if (in_array($formType, [FieldType::STRING, FieldType::TEXT])) {
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
                'sortby' => 'sorting',
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
