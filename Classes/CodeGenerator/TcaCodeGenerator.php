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

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Definition\TableDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\DateUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Generates all the TCA needed for mask content elements and backend layout fields.
 *
 * @internal
 */
class TcaCodeGenerator
{
    /**
     * StorageRepository
     *
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

    /**
     * Generates and sets the correct tca for all the inline fields
     */
    public function setInlineTca(): void
    {
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            $table = $tableDefinition->table;
            if (!AffixUtility::hasMaskPrefix($table)) {
                continue;
            }
            // Ignore table with missing tca
            if (empty($tableDefinition->tca)) {
                continue;
            }
            // Enhance boilerplate table tca with user settings
            $GLOBALS['TCA'][$table] = $this->generateTableTca($tableDefinition);
            ExtensionManagementUtility::addTCAcolumns($table, $this->generateFieldsTca($table));
        }
    }

    /**
     * Generates the TCA for a new custom table.
     */
    public function generateTableTca(TableDefinition $tableDefinition): array
    {
        $table = $tableDefinition->table;
        // Generate Table TCA
        $processedTca = $this->processTableTca($tableDefinition);
        $parentTable = $this->tableDefinitionCollection->getFieldType($table);

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
        foreach ($tableDefinition->palettes as $key => $palette) {
            $tableTca['palettes'][$key] = $this->generatePalettesTca($palette, $table);
        }

        $tt_content = $this->tableDefinitionCollection->getTableDefiniton('tt_content');
        // Set label for inline if defined
        $inlineLabel = $tt_content->tca[$table]['ctrl']['label'] ?? $tt_content->tca[$table]['inlineLabel'] ?? '';
        if ($inlineLabel && array_key_exists($inlineLabel, $tableDefinition->tca)) {
            $tableTca['ctrl']['label'] = $inlineLabel;
        }

        // Set icon for inline
        $inlineIcon = $tt_content->tca[$table]['ctrl']['iconfile'] ?? $tt_content->tca[$table]['inlineIcon'] ?? '';
        if ($inlineIcon !== '') {
            $tableTca['ctrl']['iconfile'] = $inlineIcon;
        }
        return $tableTca;
    }

    /**
     * Generates and sets the tca for all content elements.
     */
    public function setElementsTca(): void
    {
        $defaultTabs = ',--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended';
        $prependTabs = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,';
        $defaultPalette = '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,';

        // Add gridelements fields, to make mask work with gridelements out of the box
        $gridelements = '';
        if (ExtensionManagementUtility::isLoaded('gridelements')) {
            $gridelements = ', tx_gridelements_container, tx_gridelements_columns';
        }

        // Add new group in CType selectbox
        ExtensionManagementUtility::addTcaSelectItemGroup(
            'tt_content',
            'CType',
            'mask',
            'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
            'after:default'
        );

        $tt_content = $this->tableDefinitionCollection->getTableDefiniton('tt_content');

        foreach ($tt_content->palettes as $key => $palette) {
            $GLOBALS['TCA']['tt_content']['palettes'][$key] = $this->generatePalettesTca($palette, 'tt_content');
        }

        foreach ($tt_content->elements as $key => $elementValue) {
            if ($elementValue['hidden'] ?? false) {
                continue;
            }

            $cTypeKey = AffixUtility::addMaskCTypePrefix($elementValue['key']);

            // Optional shortLabel
            $label = $elementValue['shortLabel'] ?: $elementValue['label'];

            // Add new entry in CType selectbox
            ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    $label,
                    $cTypeKey,
                    'mask-ce-' . $elementValue['key'],
                    'mask'
                ]
            );

            // Add all the fields that should be shown
            [$prependTabs, $fields] = $this->generateShowItem($prependTabs, $key, 'tt_content');

            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cTypeKey] = 'mask-ce-' . $elementValue['key'];
            $GLOBALS['TCA']['tt_content']['types'][$cTypeKey]['columnsOverrides']['bodytext']['config']['enableRichtext'] = 1;
            $GLOBALS['TCA']['tt_content']['types'][$cTypeKey]['showitem'] = $prependTabs . $defaultPalette . $fields . $defaultTabs . $gridelements;
        }
    }

    /**
     * Returns the palettes of a page layout for the given key.
     */
    public function getPagePalettes(string $key): array
    {
        $palettes = [];
        $pages = $this->tableDefinitionCollection->getTableDefiniton('pages');
        foreach ($pages->elements['columns'] ?? [] as $column) {
            if ($this->tableDefinitionCollection->getFormType($column, $key, 'pages') === FieldType::PALETTE) {
                $palettes[$column] = $this->generatePalettesTca($pages->palettes[$column], 'pages');
            }
        }
        return $palettes;
    }

    /**
     * Generates the showitem string for pages
     */
    public function getPageShowItem(string $layoutKey): string
    {
        $prependTabs = '--div--;Content-Fields,';
        [$prependTabs, $fields] = $this->generateShowItem($prependTabs, $layoutKey, 'pages');

        return ',' . $prependTabs . $fields;
    }

    /**
     * Generates the showitem string for the given
     */
    protected function generateShowItem(string $prependTabs, string $elementKey, string $table): array
    {
        $element = $this->tableDefinitionCollection->getTableDefiniton($table)->elements[$elementKey] ?? [];
        $fieldArray = [];
        foreach ($element['columns'] ?? [] as $index => $fieldKey) {
            $formType = $this->tableDefinitionCollection->getFormType($fieldKey, $elementKey, $table);
            // Check if this field is of type tab
            if ($formType === FieldType::TAB) {
                $label = $this->tableDefinitionCollection->getLabel($elementKey, $fieldKey, $table);
                // If a tab is in the first position then change the name of the general tab
                if ($index === 0) {
                    $prependTabs = '--div--;' . $label . ',';
                } else {
                    // Otherwise just add new tab
                    $fieldArray[] = '--div--;' . $label;
                }
            } elseif ($formType === FieldType::PALETTE) {
                $fieldArray[] = '--palette--;;' . $fieldKey;
            } else {
                $fieldArray[] = $fieldKey;
            }
        }
        $fields = implode(',', $fieldArray);

        return [$prependTabs, $fields];
    }

    /**
     * Generates the TCA needed for palettes.
     */
    protected function generatePalettesTca(array $palette, string $table): array
    {
        $showitem = [];
        foreach ($palette['showitem'] as $item) {
            if ($this->tableDefinitionCollection->getFormType($item, '', $table) === FieldType::LINEBREAK) {
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
     */
    public function generateFieldsTca(string $table): array
    {
        $tableDefinition = $this->tableDefinitionCollection->getTableDefiniton($table);
        $columns = [];
        foreach ($tableDefinition->tca as $tcaKey => $tcaValue) {
            if (!isset($tcaValue['config'])) {
                continue;
            }

            // Inline: Ignore empty inline fields
            $formType = $this->tableDefinitionCollection->getFormType($tcaKey, '', $table);
            if ($formType !== '' && !$this->tableDefinitionCollection->hasTableDefinition($tcaKey) && FieldType::cast($formType)->isParentField()) {
                continue;
            }

            // Ignore grouping elements
            if (in_array(($tcaValue['config']['type'] ?? ''), FieldType::getConstants(), true) && FieldType::cast(($tcaValue['config']['type']))->isGroupingField()) {
                continue;
            }

            $columns[$tcaKey] = [];

            // File: Add file config.
            if (($tcaValue['options'] ?? '') === 'file') {
                // If imageoverlayPalette is not set (because of updates to newer version) fallback to default behaviour.
                if ($tcaValue['imageoverlayPalette'] ?? true) {
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

                $customSettingOverride['appearance'] = $tcaValue['config']['appearance'] ?? [];
                $customSettingOverride['appearance']['fileUploadAllowed'] = (bool)($customSettingOverride['appearance']['fileUploadAllowed'] ?? true);
                $customSettingOverride['appearance']['useSortable'] = (bool)($customSettingOverride['appearance']['useSortable'] ?? false);
                // Since mask v7.0.0 the path for allowedFileExtensions has changed to root level. Keep this as fallback.
                $allowedFileExtensions = $tcaValue['allowedFileExtensions'] ?? $tcaValue['config']['filter']['0']['parameters']['allowedFileExtensions'] ?? '';
                if ($allowedFileExtensions === '') {
                    $allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                }
                $columns[$tcaKey]['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($tcaKey, $customSettingOverride, $allowedFileExtensions);
                unset($customSettingOverride);
            }

            // Inline (Repeating): Fill missing foreign_table in tca config.
            if (($tcaValue['config']['foreign_table'] ?? '') === '--inlinetable--') {
                $tcaValue['config']['foreign_table'] = $tcaKey;
            }

            // Convert Date and Datetime default and ranges to timestamp
            $dbType = $tcaValue['config']['dbType'] ?? '';
            if (in_array($dbType, ['date', 'datetime'])) {
                $default = $tcaValue['config']['default'] ?? false;
                if ($default) {
                    $tcaValue['config']['default'] = DateUtility::convertStringToTimestampByDbType($dbType, $default);
                }
                $upper = $tcaValue['config']['range']['upper'] ?? false;
                if ($upper) {
                    $tcaValue['config']['range']['upper'] = DateUtility::convertStringToTimestampByDbType($dbType, $upper);
                }
                $lower = $tcaValue['config']['range']['lower'] ?? false;
                if ($lower) {
                    $tcaValue['config']['range']['lower'] = DateUtility::convertStringToTimestampByDbType($dbType, $lower);
                }
            }

            // Text: Set correct rendertype if format (code highlighting) is set.
            if ($tcaValue['config']['format'] ?? false) {
                $tcaValue['config']['renderType'] = 't3editor';
            }

            // RTE: Add softref
            if (FieldType::cast($formType)->equals(FieldType::RICHTEXT)) {
                $tcaValue['config']['softref'] = 'typolink_tag,email[subst],url';
            }

            // Content: Set foreign_field and default CType in select if restricted.
            if (($tcaValue['config']['foreign_table'] ?? '') === 'tt_content' && ($tcaValue['config']['type'] ?? '') === 'inline') {
                $parentField = AffixUtility::addMaskParentSuffix($tcaKey);
                $tcaValue['config']['foreign_field'] = $parentField;
                if ($table === 'tt_content') {
                    $columns[$parentField] = [
                        'config' => [
                            'type' => 'passthrough'
                        ]
                    ];
                }
                if ($tcaValue['cTypes'] ?? false) {
                    $tcaValue['config']['overrideChildTca']['columns']['CType']['config']['default'] = reset($tcaValue['cTypes']);
                }
            }

            // Add backwards compatibility for allowed extensions.
            if (FieldType::cast($formType)->equals(FieldType::LINK)) {
                if (isset($tcavalue['config']['wizards']['link']['params']['allowedExtensions'])) {
                    $tcavalue['config']['fieldControl']['linkPopup']['options']['allowedExtensions'] = $tcavalue['config']['wizards']['link']['params']['allowedExtensions'];
                    unset($tcavalue['config']['wizards']['link']['params']['allowedExtensions']);
                }
            }

            // Merge user inputs with file array (for file type overrides)
            ArrayUtility::mergeRecursiveWithOverrule($columns[$tcaKey], $tcaValue);

            // Unset some values that are not needed in TCA
            unset(
                $columns[$tcaKey]['options'],
                $columns[$tcaKey]['key'],
                $columns[$tcaKey]['rte'],
                $columns[$tcaKey]['inlineParent'],
                $columns[$tcaKey]['inlineLabel'],
                $columns[$tcaKey]['inPalette'],
                $columns[$tcaKey]['order'],
                $columns[$tcaKey]['inlineIcon'],
                $columns[$tcaKey]['imageoverlayPalette'],
                $columns[$tcaKey]['cTypes'],
                $columns[$tcaKey]['allowedFileExtensions'],
                $columns[$tcaKey]['ctrl']
            );

            // Unset label if it is from palette fields
            if (is_array($columns[$tcaKey]['label'] ?? false)) {
                unset($columns[$tcaKey]['label']);
            }

            $columns[$tcaKey] = MaskUtility::removeBlankOptions($columns[$tcaKey]);
            // Exlcude all fields for editors by default
            $columns[$tcaKey]['exclude'] = 1;
        }
        return $columns;
    }

    /**
     * Processes the TCA for Inline-Tables
     */
    public function processTableTca(TableDefinition $tableDefinition): array
    {
        $generalTab = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general';

        $tca = $tableDefinition->tca;
        uasort($tca, static function ($columnA, $columnB) {
            $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
            $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
            return $a - $b;
        });

        $fields = [];
        $firstField = true;
        foreach ($tca as $fieldKey => $configuration) {
            // check if this field is of type tab
            $formType = $this->tableDefinitionCollection->getFormType($fieldKey, '', $tableDefinition->table);
            if ($formType === FieldType::TAB) {
                $label = $configuration['label'];
                // if a tab is in the first position then change the name of the general tab
                if ($firstField) {
                    $generalTab = '--div--;' . $label;
                } else {
                    // otherwise just add new tab
                    $fields[] = '--div--;' . $label;
                }
            } elseif ($formType === FieldType::PALETTE) {
                if ($firstField && empty($tableDefinition->palettes[$fieldKey]['showitem'])) {
                    $firstField = false;
                    continue;
                }
                $fields[] = '--palette--;;' . $fieldKey;
            } elseif (!($configuration['inPalette'] ?? false)) {
                $fields[] = $fieldKey;
            }
            $firstField = false;
        }

        // take first field for inline label
        $labelField = '';
        if (!empty($fields)) {
            $labelField = MaskUtility::getFirstNoneTabField($fields);
            // If first field is palette, get label of first field in this palette.
            if (strpos($labelField, '--palette--;;') === 0) {
                $palette = str_replace('--palette--;;', '', $labelField);
                $labelField = $tableDefinition->palettes[$palette]['showitem'][0];
            }
        }

        return [
            'label' => $labelField,
            'showitem' => $generalTab . ',' . implode(',', $fields),
        ];
    }

    /**
     * Return array with mask irre tables.
     * @deprecated will be removed in Mask v8.0.
     */
    public function getMaskIrreTables(): array
    {
        $configuration = $this->tableDefinitionCollection->toArray();
        $irreTables = array_filter(array_keys($configuration), static function ($table) {
            return AffixUtility::hasMaskPrefix($table);
        });
        return array_values($irreTables);
    }

    /**
     * Add search fields to find mask elements or pages
     */
    public function addSearchFields(string $table): void
    {
        $tca = $this->tableDefinitionCollection->getTableDefiniton($table)->tca;
        $searchFields = [];

        foreach ($tca as $tcakey => $tcavalue) {
            $formType = $this->tableDefinitionCollection->getFormType($tcakey, '', $table);
            if (in_array($formType, [FieldType::STRING, FieldType::TEXT], true)) {
                $searchFields[] = $tcakey;
            }
        }

        if ($searchFields) {
            $GLOBALS['TCA'][$table]['ctrl']['searchFields'] .= ',' . implode(',', $searchFields);
        }
    }

    public static function getTcaTemplate(): array
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
