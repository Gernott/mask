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

namespace MASK\Mask\Tests\Unit\CodeGenerator;

use MASK\Mask\CodeGenerator\TcaCodeGenerator;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TcaCodeGeneratorTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;

    public function getPageShowItemProvider(): array
    {
        return [
            'Layout 1 is rendered in correct order' => [
                [
                    'pages' => [
                        'elements' => [
                            '1' => [
                                'columns' => [
                                    'tx_mask_c_in_default_tab',
                                    'tx_mask_b_tab',
                                    'tx_mask_b_in_b_tab',
                                    'tx_mask_a_tab',
                                    'tx_mask_a_in_a_tab',
                                ],
                                'label' => 'Backend Layout 1',
                                'description' => 'Test backend layout',
                                'shortLabel' => 'BL 1',
                                'key' => '1',
                                'labels' => [
                                    'In Standard Tab',
                                    'B Tab',
                                    'B Feld',
                                    'A Tab',
                                    'A Feld',
                                ],
                            ],
                            '2' => [
                                'columns' => [
                                    'tx_mask_d_in_default_tab',
                                    'tx_mask_c_in_default_tab',
                                ],
                                'label' => 'Backend Layout 2',
                                'description' => 'Test backend layout 2',
                                'shortLabel' => 'BL 2',
                                'key' => '2',
                                'labels' => [
                                    'In Standard Tab',
                                    'In Stamdard Tab 2',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab',
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab',
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab',
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab',
                            ],
                            'tx_mask_c_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'c_in_default_tab',
                            ],
                            'tx_mask_d_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'd_in_default_tab',
                            ],
                        ],
                    ],
                ],
                '1',
                ',--div--;Content-Fields,tx_mask_c_in_default_tab,--div--;B Tab,tx_mask_b_in_b_tab,--div--;A Tab,tx_mask_a_in_a_tab',
            ],
            'Layout 2 is rendered in correct order' => [
                [
                    'pages' => [
                        'elements' => [
                            '1' => [
                                'columns' => [
                                    'tx_mask_c_in_default_tab',
                                    'tx_mask_b_tab',
                                    'tx_mask_b_in_b_tab',
                                    'tx_mask_a_tab',
                                    'tx_mask_a_in_a_tab',
                                ],
                                'label' => 'Backend Layout 1',
                                'description' => 'Test backend layout',
                                'shortLabel' => 'BL 1',
                                'key' => '1',
                                'labels' => [
                                    'In Standard Tab',
                                    'B Tab',
                                    'B Feld',
                                    'A Tab',
                                    'A Feld',
                                ],
                            ],
                            '2' => [
                                'columns' => [
                                    'tx_mask_d_in_default_tab',
                                    'tx_mask_c_in_default_tab',
                                ],
                                'label' => 'Backend Layout 2',
                                'description' => 'Test backend layout 2',
                                'shortLabel' => 'BL 2',
                                'key' => '2',
                                'labels' => [
                                    'In Standard Tab',
                                    'In Standard Tab 2',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab',
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab',
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab',
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab',
                            ],
                            'tx_mask_c_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'c_in_default_tab',
                            ],
                            'tx_mask_d_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'd_in_default_tab',
                            ],
                        ],
                    ],
                ],
                '2',
                ',--div--;Content-Fields,tx_mask_d_in_default_tab,tx_mask_c_in_default_tab',
            ],
            'If tab is at first place override default tab' => [
                [
                    'pages' => [
                        'elements' => [
                            '1' => [
                                'columns' => [
                                    'tx_mask_b_tab',
                                    'tx_mask_b_in_b_tab',
                                    'tx_mask_a_tab',
                                    'tx_mask_a_in_a_tab',
                                ],
                                'label' => 'Backend Layout 1',
                                'description' => 'Test backend layout',
                                'shortLabel' => 'BL 1',
                                'key' => '1',
                                'labels' => [
                                    'B Tab',
                                    'B Feld',
                                    'A Tab',
                                    'A Feld',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab',
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab',
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab',
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab',
                            ],
                        ],
                    ],
                ],
                '1',
                ',--div--;B Tab,tx_mask_b_in_b_tab,--div--;A Tab,tx_mask_a_in_a_tab',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getPageShowItemProvider
     */
    public function getPageShowItem(array $json, string $key, string $expected): void
    {
        $tcaCodeGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());

        self::assertSame($expected, $tcaCodeGenerator->getPageShowItem($key));
    }

    public function processTableTcaDataProvider(): array
    {
        return [
            'Order is correct and tab is put correctly' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'tx_mask_field_2' => [
                                'key' => 'field_2',
                                'label' => 'Field 2',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_1' => [
                                'key' => 'field_1',
                                'label' => 'Field 1',
                                'order' => '1',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_3' => [
                                'key' => 'field_3',
                                'label' => 'Field 3',
                                'order' => '4',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_tab_field' => [
                                'key' => 'tab_field',
                                'label' => 'New Tab',
                                'order' => '3',
                                'config' => [
                                    'type' => 'tab',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'tx_mask_field_1',
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,tx_mask_field_1,tx_mask_field_2,--div--;New Tab,tx_mask_field_3',
                ],
            ],
            'Tab at first position overrides general tab' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'tx_mask_field_2' => [
                                'key' => 'field_2',
                                'label' => 'Field 2',
                                'order' => '3',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_1' => [
                                'key' => 'field_1',
                                'label' => 'Field 1',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_3' => [
                                'key' => 'field_3',
                                'label' => 'Field 3',
                                'order' => '4',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_tab_field' => [
                                'key' => 'tab_field',
                                'label' => 'New Tab',
                                'order' => '1',
                                'config' => [
                                    'type' => 'tab',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'tx_mask_field_1',
                    'showitem' => '--div--;New Tab,tx_mask_field_1,tx_mask_field_2,tx_mask_field_3',
                ],
            ],
            'Palettes are added' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'tx_mask_field_2' => [
                                'key' => 'field_2',
                                'label' => 'Field 2',
                                'order' => '3',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_1' => [
                                'key' => 'field_1',
                                'label' => 'Field 1',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_palette_field' => [
                                'key' => 'palette_field',
                                'label' => 'A Palette',
                                'order' => '1',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_field_3' => [
                                'key' => 'field_3',
                                'label' => 'Field 3',
                                'order' => '1',
                                'inPalette' => true,
                                'inlineParent' => 'palette_field',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette_field' => [
                                'showitem' => [
                                    'tx_mask_field_3',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'tx_mask_field_3',
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;;tx_mask_palette_field,tx_mask_field_1,tx_mask_field_2',
                ],
            ],
            'Empty palette first field is ignored' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'tx_mask_field_2' => [
                                'key' => 'field_2',
                                'label' => 'Field 2',
                                'order' => '3',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_field_1' => [
                                'key' => 'field_1',
                                'label' => 'Field 1',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                            'tx_mask_palette_field' => [
                                'key' => 'palette_field',
                                'label' => 'A Palette',
                                'order' => '1',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_field_3' => [
                                'key' => 'field_3',
                                'label' => 'Field 3',
                                'order' => '4',
                                'config' => [
                                    'type' => 'input',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette_field' => [
                                'showitem' => [],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'tx_mask_field_1',
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,tx_mask_field_1,tx_mask_field_2,tx_mask_field_3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider processTableTcaDataProvider
     * @test
     */
    public function processTableTca(string $table, array $json, array $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
        $tcaGenerator = new TcaCodeGenerator($tableDefinitionCollection, new OnlineMediaHelperRegistry());

        self::assertSame($expected, $tcaGenerator->processTableTca($tableDefinitionCollection->getTable($table)));
    }

    public function generateFieldsTcaDataProvider(): iterable
    {
        yield 'Input fields are processd correctly' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => '',
                            ],
                            'key' => 'field_1',
                        ],
                        'tx_mask_field_2' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => 'trim',
                            ],
                            'key' => 'field_2',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'input',
                    ],
                    'exclude' => 1,
                ],
                'tx_mask_field_2' => [
                    'config' => [
                        'type' => 'input',
                        'eval' => 'trim',
                    ],
                    'exclude' => 1,
                ],
            ],
        ];

        yield 'Text fields are processed correctly' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'text',
                                'eval' => '',
                                'format' => 'typoscript',
                            ],
                            'key' => 'field_1',
                        ],
                        'tx_mask_field_2' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => 'trim',
                            ],
                            'key' => 'field_2',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'text',
                        'format' => 'typoscript',
                        'renderType' => 't3editor',
                    ],
                    'exclude' => 1,
                ],
                'tx_mask_field_2' => [
                    'config' => [
                        'type' => 'input',
                        'eval' => 'trim',
                    ],
                    'exclude' => 1,
                ],
            ],
        ];

        yield 'Tabs are ignored' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => '',
                            ],
                            'key' => 'field_1',
                        ],
                        'tx_mask_tab' => [
                            'key' => 'tab',
                            'config' => [
                                'type' => 'tab',
                            ],
                        ],
                        'tx_mask_field_2' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => 'trim',
                            ],
                            'key' => 'field_2',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'input',
                    ],
                    'exclude' => 1,
                ],
                'tx_mask_field_2' => [
                    'config' => [
                        'type' => 'input',
                        'eval' => 'trim',
                    ],
                    'exclude' => 1,
                ],
            ],
        ];

        yield 'Foreign table of inline fields is replaced' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => '--inlinetable--',
                            ],
                            'key' => 'field_1',
                        ],
                    ],
                ],
                'tx_mask_field_1' => [
                    'tca' => [

                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'inline',
                        'foreign_table' => 'tx_mask_field_1',
                    ],
                    'exclude' => 1,
                ],
            ],
        ];

        yield 'Date fields ranges are applied' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'input',
                                'dbType' => 'date',
                                'eval' => 'date',
                                'range' => [
                                    'lower' => '01-01-2021',
                                    'upper' => '30-12-2021',
                                ],
                            ],
                            'key' => 'field_1',
                        ],
                        'tx_mask_field_2' => [
                            'config' => [
                                'type' => 'input',
                                'dbType' => 'datetime',
                                'eval' => 'datetime',
                                'range' => [
                                    'upper' => '20:30 30-12-2021',
                                ],
                            ],
                            'key' => 'field_2',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'input',
                        'dbType' => 'date',
                        'eval' => 'date',
                        'range' => [
                            'lower' => 1609459200,
                            'upper' => 1640822400,
                        ],
                    ],
                    'exclude' => 1,
                ],
                'tx_mask_field_2' => [
                    'config' => [
                        'type' => 'input',
                        'dbType' => 'datetime',
                        'eval' => 'datetime',
                        'range' => [
                            'upper' => 1640896200,
                        ],
                    ],
                    'exclude' => 1,
                ],
            ],
        ];

        yield 'Content inline fields are processed correctly' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'cTypes' => [
                                'text',
                                'textmedia',
                            ],
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => 'tt_content',
                            ],
                            'key' => 'field_1',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field_1' => [
                    'config' => [
                        'type' => 'inline',
                        'foreign_table' => 'tt_content',
                        'foreign_field' => 'tx_mask_field_1_parent',
                        'overrideChildTca' => [
                            'columns' => [
                                'CType' => [
                                    'config' => [
                                        'default' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'exclude' => 1,
                ],
                'tx_mask_field_1_parent' => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
            ],
        ];

        yield 'children of palettes are processed' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                        ],
                        'tx_mask_field' => [
                            'config' => [
                                'type' => 'input',
                                'eval' => 'trim',
                            ],
                            'key' => 'field',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette',
                                'element2' => 'tx_mask_palette2',
                            ],
                            'label' => [
                                'element1' => 'Field 1',
                                'element2' => 'Field 2',
                            ],
                            'order' => [
                                'element1' => 0,
                                'element2' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tx_mask_field' => [
                    'config' => [
                        'type' => 'input',
                        'eval' => 'trim',
                    ],
                    'exclude' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider generateFieldsTcaDataProvider
     * @test
     */
    public function generateFieldsTca(array $json, string $table, array $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());

        self::assertSame($expected, $tcaGenerator->generateFieldsTca($table));
    }

    public function generateFileTcaDataProvider(): iterable
    {
        yield 'Files are processed correctly' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'appearance' => [
                                    'useSortable' => false,
                                    'fileUploadAllowed' => true,
                                    'expandSingle' => true,
                                ],
                                'minitems' => '5',
                                'maxitems' => '10',
                            ],
                            'key' => 'field_1',
                            'type' => 'file',
                            'allowedFileExtensions' => 'jpeg',
                            'imageoverlayPalette' => 1,
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'field' => 'tx_mask_field_1',
            'expected' => [
                'type' => 'inline',
                'foreign_match_fields' => 'tx_mask_field_1',
                'elementBrowserAllowed' => 'jpeg',
                'imageoverlayPalette' => true,
                'minitems' => '5',
                'maxitems' => '10',
                'appearance' => [
                    'useSortable' => false,
                    'fileUploadAllowed' => true,
                    'expandSingle' => true,
                ],
            ],
        ];

        yield 'ImageOverlayPalette deactivated' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'appearance' => [
                                    'useSortable' => false,
                                    'fileUploadAllowed' => true,
                                    'expandSingle' => true,
                                ],
                                'minitems' => '5',
                                'maxitems' => '10',
                            ],
                            'key' => 'field_1',
                            'type' => 'file',
                            'allowedFileExtensions' => 'jpeg',
                            'imageoverlayPalette' => 0,
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'field' => 'tx_mask_field_1',
            'expected' => [
                'type' => 'inline',
                'foreign_match_fields' => 'tx_mask_field_1',
                'elementBrowserAllowed' => 'jpeg',
                'imageoverlayPalette' => false,
                'minitems' => '5',
                'maxitems' => '10',
                'appearance' => [
                    'useSortable' => false,
                    'fileUploadAllowed' => true,
                    'expandSingle' => true,
                ],
            ],
        ];

        yield 'Media is processed correctly' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'appearance' => [
                                    'useSortable' => false,
                                    'fileUploadAllowed' => true,
                                    'expandSingle' => true,
                                ],
                                'minitems' => '5',
                                'maxitems' => '10',
                            ],
                            'key' => 'field_1',
                            'type' => 'media',
                            'allowedFileExtensions' => 'flac,mp4,vimeo,youtube',
                            'onlineMedia' => 'youtube',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'field' => 'tx_mask_field_1',
            'expected' => [
                'type' => 'inline',
                'foreign_match_fields' => 'tx_mask_field_1',
                'elementBrowserAllowed' => 'flac,mp4,youtube',
                'imageoverlayPalette' => true,
                'minitems' => '5',
                'maxitems' => '10',
                'appearance' => [
                    'useSortable' => false,
                    'fileUploadAllowed' => true,
                    'expandSingle' => true,
                ],
            ],
        ];

        yield 'Media with empty onlineMedia' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_field_1' => [
                            'config' => [
                                'appearance' => [
                                    'useSortable' => false,
                                    'fileUploadAllowed' => true,
                                    'expandSingle' => true,
                                ],
                                'minitems' => '5',
                                'maxitems' => '10',
                            ],
                            'key' => 'field_1',
                            'type' => 'media',
                            'allowedFileExtensions' => 'flac,mp4,vimeo,youtube',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'field' => 'tx_mask_field_1',
            'expected' => [
                'type' => 'inline',
                'foreign_match_fields' => 'tx_mask_field_1',
                'elementBrowserAllowed' => 'flac,mp4',
                'imageoverlayPalette' => true,
                'minitems' => '5',
                'maxitems' => '10',
                'appearance' => [
                    'useSortable' => false,
                    'fileUploadAllowed' => true,
                    'expandSingle' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider generateFileTcaDataProvider
     * @test
     */
    public function generateFileTca(array $json, string $table, string $field, array $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());

        $result = $tcaGenerator->generateFieldsTca($table);
        self::assertSame($expected['type'], $result[$field]['config']['type']);
        self::assertSame($expected['minitems'], $result[$field]['config']['minitems']);
        self::assertSame($expected['maxitems'], $result[$field]['config']['maxitems']);
        self::assertSame($expected['elementBrowserAllowed'], $result[$field]['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed']);
        self::assertEquals($expected['elementBrowserAllowed'], $result[$field]['config']['filter'][0]['parameters']['allowedFileExtensions']);
        self::assertSame($expected['foreign_match_fields'], $result[$field]['config']['foreign_match_fields']['fieldname']);
        self::assertEquals($expected['appearance']['useSortable'], $result[$field]['config']['appearance']['useSortable']);
        self::assertEquals($expected['appearance']['fileUploadAllowed'], $result[$field]['config']['appearance']['fileUploadAllowed']);
        self::assertEquals($expected['appearance']['expandSingle'], $result[$field]['config']['appearance']['expandSingle']);
        self::assertEquals($expected['imageoverlayPalette'], isset($result[$field]['config']['overrideChildTca']['types'][File::FILETYPE_IMAGE]));
    }

    public function setElementsTcaDataProvider(): array
    {
        return [
            'showitem is set for each field' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'header',
                                    'bodytext',
                                ],
                                'labels' => [
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,bodytext,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                true,
            ],
            'Hidden elements are ignored' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'hidden' => '1',
                                'columns' => [
                                    'header',
                                    'bodytext',
                                ],
                                'labels' => [
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '',
                [],
                false,
            ],
            'Empty inline fields are ignored' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'header',
                                    'tx_mask_inline',
                                ],
                                'labels' => [
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
                            ],
                            'tx_mask_inline' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'type' => 'inline',
                                'key' => 'inline',
                                'fullKey' => 'tx_mask_inline',
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                false,
            ],
            'General tab can be overriden' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'tx_mask_my_tab',
                                    'header',
                                    'bodytext',
                                ],
                                'labels' => [
                                    'My Tab',
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_my_tab' => [
                                'key' => 'my_tab',
                                'config' => [
                                    'type' => 'tab',
                                ],
                            ],
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;My Tab,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,bodytext,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                true,
            ],
            'Tabs can be added after elements' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'header',
                                    'tx_mask_my_tab',
                                    'bodytext',
                                ],
                                'labels' => [
                                    '',
                                    'My Tab',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_my_tab' => [
                                'key' => 'my_tab',
                                'config' => [
                                    'type' => 'tab',
                                ],
                            ],
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,--div--;My Tab,bodytext,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                true,
            ],
            'Palettes can be added' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'tx_mask_my_palette',
                                    'tx_mask_my_palette2',
                                ],
                                'labels' => [
                                    'My Palette',
                                    'My Palette 2 (without description defined in palettes array)',
                                ],
                                'descriptions' => [
                                    'description for palette with label My Palette',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_my_palette' => [
                                'key' => 'my_palette',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_my_palette2' => [
                                'key' => 'my_palette2',
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'description' => 'Palette Description old position works',
                            ],
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_my_palette',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_my_palette' => [
                                'label' => 'My Palette',
                                'description' => 'description for palette with label My Palette',
                                'showitem' => ['header', 'bodytext'],
                            ],
                            'tx_mask_my_palette2' => [
                                'label' => 'My Palette 2 (without description defined in palettes array)',
                                'showitem' => [],
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,--palette--;;tx_mask_my_palette,--palette--;;tx_mask_my_palette2,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [
                    'tx_mask_my_palette' => [
                        'label' => 'My Palette',
                        'description' => 'description for palette with label My Palette',
                        'showitem' => 'header,bodytext',
                    ],
                    'tx_mask_my_palette2' => [
                        'label' => 'My Palette 2 (without description defined in palettes array)',
                        'description' => 'Palette Description old position works',
                        'showitem' => '',
                    ],
                ],
                true,
            ],
            'Linebreaks converted to --linebreak--' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'tx_mask_my_palette',
                                ],
                                'labels' => [
                                    'My Palette',
                                ],
                                'descriptions' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_my_palette' => [
                                'key' => 'my_palette',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_linebreak-1' => [
                                'key' => 'linebreak-1',
                                'config' => [
                                    'type' => 'linebreak',
                                ],
                            ],
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                                'type' => 'richtext',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_my_palette',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_my_palette' => [
                                'label' => 'My Palette',
                                'description' => '',
                                'showitem' => ['header', 'tx_mask_linebreak-1', 'bodytext'],
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,--palette--;;tx_mask_my_palette,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [
                    'tx_mask_my_palette' => [
                        'label' => 'My Palette',
                        'description' => '',
                        'showitem' => 'header,--linebreak--,bodytext',
                    ],
                ],
                true,
            ],
            'richtext not added for bodytext, if it is of type text' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'header',
                                    'bodytext',
                                ],
                                'labels' => [
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                                'bodytextTypeByElement' => [
                                    'element1' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,bodytext,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                false,
            ],
            'richtext added for bodytext, if it is of type richtext' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'shortLabel' => 'Ele 1',
                                'columns' => [
                                    'header',
                                    'bodytext',
                                ],
                                'labels' => [
                                    '',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
                            ],
                            'bodytext' => [
                                'key' => 'bodytext',
                                'bodytextTypeByElement' => [
                                    'element1' => 'richtext',
                                ],
                            ],
                        ],
                    ],
                ],
                'mask_element1',
                '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,header,bodytext,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames;frames,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,--palette--;;hidden,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,rowDescription,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended',
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider setElementsTcaDataProvider
     * @test
     */
    public function setElementsTca(array $json, string $key, string $showItemExpected, array $paletteExpected, bool $bodytextIsRichtext): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['type'] = 'input';

        $packageManager = $this->prophesize(PackageManager::class);
        $packageManager->isPackageActive('gridelements')->willReturn(false);
        ExtensionManagementUtility::setPackageManager($packageManager->reveal());

        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());
        $tcaGenerator->setElementsTca();

        self::assertSame($showItemExpected, $GLOBALS['TCA']['tt_content']['types'][$key]['showitem'] ?? '');
        self::assertSame($paletteExpected, $GLOBALS['TCA']['tt_content']['palettes'] ?? []);
        self::assertSame($bodytextIsRichtext, ($GLOBALS['TCA']['tt_content']['types'][$key]['columnsOverrides']['bodytext']['config']['enableRichtext'] ?? 0) === 1);
    }

    public function generateTableTcaDataProvider(): array
    {
        return [
            'Label and Icon generated when ctrl provided' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                                'labels' => [
                                    'Inline Field',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'key' => 'inline',
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'ctrl' => [
                                    'label' => 'tx_mask_field1',
                                    'iconfile' => '/some/path/to/a/file',
                                ],
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'order' => 1,
                            ],
                        ],
                    ],
                ],
                'tx_mask_inline',
                'tx_mask_field1',
                '/some/path/to/a/file',
            ],
            'Label and Icon generated when inlineLabel and inlineIcon provided' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                                'labels' => [
                                    'Inline Field',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'key' => 'inline',
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'inlineLabel' => 'tx_mask_field1',
                                'inlineIcon' => '/some/path/to/a/file',
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'order' => 1,
                                'inlineParent' => 'tx_mask_inline',
                            ],
                        ],
                    ],
                ],
                'tx_mask_inline',
                'tx_mask_field1',
                '/some/path/to/a/file',
            ],
            'Non exsiting key for label results in first field' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element1',
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                                'labels' => [
                                    'Inline Field',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'key' => 'inline',
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'ctrl' => [
                                    'label' => 'tx_mask_field3',
                                    'iconfile' => '/some/path/to/a/file',
                                ],
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'order' => 1,
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'order' => 2,
                            ],
                        ],
                    ],
                ],
                'tx_mask_inline',
                'tx_mask_field1',
                '/some/path/to/a/file',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider generateTableTcaDataProvider
     */
    public function generateTableTca(array $json, string $table, string $expectedLabel, string $expectedIcon): void
    {
        $loader = $this->createLoader($json);
        $tableDefinition = $loader->load()->getTable($table);
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());

        self::assertSame($expectedLabel, $tcaGenerator->generateTableTca($tableDefinition)['ctrl']['label']);
        self::assertSame($expectedIcon, $tcaGenerator->generateTableTca($tableDefinition)['ctrl']['iconfile']);
    }

    public function getPagePalettesTestDataProvider(): array
    {
        return [
            'Palettes of pages returned' => [
                [
                    'pages' => [
                        'elements' => [
                            '1' => [
                                'key' => '1',
                                'label' => 'Layout 1',
                                'columns' => [
                                    'tx_mask_palette1',
                                ],
                                'labels' => [
                                    'Palette 1',
                                ],
                            ],
                            '2' => [
                                'key' => '2',
                                'label' => 'Layout 2',
                                'columns' => [
                                    'tx_mask_palette2',
                                ],
                                'labels' => [
                                    'Palette 2',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette1' => [
                                'key' => 'palette1',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_palette2' => [
                                'key' => 'palette2',
                                'config' => [
                                    'type' => 'palette',
                                ],
                            ],
                            'tx_mask_field1' => [
                                'key' => 'field1',
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inPalette' => 1,
                                'inlineParent' => [
                                    '1' => 'tx_mask_palette',
                                    '2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    '1' => 'Palette 1',
                                    '2' => 'Palette 2',
                                ],
                            ],
                            'tx_mask_field2' => [
                                'key' => 'field2',
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inPalette' => 1,
                                'inlineParent' => [
                                    '2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    '2' => 'Palette 2',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette1' => [
                                'label' => 'Palette 1',
                                'showitem' => [
                                    'tx_mask_field1',
                                ],
                            ],
                            'tx_mask_palette2' => [
                                'label' => 'Palette 2',
                                'showitem' => [
                                    'tx_mask_field1',
                                ],
                            ],
                        ],
                    ],
                ],
                '2',
                [
                    'tx_mask_palette2',
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getPagePalettesTestDataProvider
     */
    public function getPagePalettesTest(array $json, string $elementKey, array $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());
        self::assertEquals($expected, array_keys($tcaGenerator->getPagePalettes($elementKey)));
    }

    public function getFirstNoneTabFieldDataProvider(): iterable
    {
        return [
            'Tab is first element' => [
                ['--div--;My Tab', 'tx_mask_the_field', 'tx_mask_another_field'],
                'tx_mask_the_field',
            ],
            'Tab is not first element' => [
                ['tx_mask_the_field', '--div--;My Tab', 'tx_mask_another_field'],
                'tx_mask_the_field',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFirstNoneTabFieldDataProvider
     */
    public function getFirstNoneTabField(array $data, string $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray([]), new OnlineMediaHelperRegistry());
        self::assertSame($expected, $tcaGenerator->getFirstNoneTabField($data));
    }

    public function generateTCAColumnsOverridesDataProvider(): iterable
    {
        yield 'normal root fields TCA override generated' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_field1',
                                'tx_mask_field2',
                            ],
                            'descriptions' => [
                                'Field 1',
                                'Field 2',
                            ],
                        ],
                        'element2' => [
                            'key' => 'element2',
                            'label' => 'Element 2',
                            'columns' => [
                                'tx_mask_field3',
                            ],
                            'descriptions' => [
                                'Field 3',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                        'tx_mask_field2' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                        'tx_mask_field3' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'types' => [
                        'mask_element1' => [
                            'columnsOverrides' => [
                                'tx_mask_field1' => [
                                    'description' => 'Field 1',
                                ],
                                'tx_mask_field2' => [
                                    'description' => 'Field 2',
                                ],
                            ],
                        ],
                        'mask_element2' => [
                            'columnsOverrides' => [
                                'tx_mask_field3' => [
                                    'description' => 'Field 3',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'no overrides for empty inline fields generated' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_inline',
                            ],
                            'descriptions' => [
                                'Inline 1',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_inline' => [
                            'config' => [
                                'type' => 'inline',
                            ],
                            'key' => 'inline',
                            'fullKey' => 'tx_mask_inline',
                        ],
                    ],
                ],
                'tx_mask_inline' => [
                    'tca' => [],
                ],
            ],
            'expected' => [],
        ];

        yield 'nothing to generate' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_tab',
                                'tx_mask_field1',
                                'tx_mask_field2',
                            ],
                            'descriptions' => [
                                'Tab 1',
                                '',
                                '',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                        'tx_mask_field2' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                        'tx_mask_tab' => [
                            'config' => [
                                'type' => 'tab',
                            ],
                            'key' => 'tab',
                            'fullKey' => 'tx_mask_tab',
                        ],
                    ],
                ],
            ],
            'expected' => [],
        ];

        yield 'fields in palettes generate overrides and palette description is ignored.' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_palette',
                                'tx_mask_field2',
                            ],
                            'descriptions' => [
                                'Palette Description',
                                'Field 2 Description',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette',
                            ],
                            'inPalette' => '1',
                            'description' => [
                                'element1' => 'Field 1 Description',
                            ],
                        ],
                        'tx_mask_field2' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                        ],
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                            'fullKey' => 'tx_mask_palette',
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette' => [
                            'showitem' => ['tx_mask_field1'],
                            'label' => 'Palette',
                            'description' => 'Palette Description',
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'types' => [
                        'mask_element1' => [
                            'columnsOverrides' => [
                                'tx_mask_field1' => [
                                    'description' => 'Field 1 Description',
                                ],
                                'tx_mask_field2' => [
                                    'description' => 'Field 2 Description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider generateTCAColumnsOverridesDataProvider
     * @test
     */
    public function generateTCAColumnsOverrides(array $json, array $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());
        self::assertEquals($expected, $tcaGenerator->generateTCAColumnsOverrides('tt_content'));
    }

    /**
     * @return mixed[]
     */
    public function addSearchFieldsReturnsCorrectFieldStringDataProvider(): iterable
    {
        yield 'No fields return current search string as is' => [
            'json' => [
                'tt_content' => [
                    'tca' => [],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext',
        ];

        yield 'If there are no search fields at all, empty string should be returned' => [
            'json' => [
                'tt_content' => [
                    'tca' => [],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => '',
            'expected' => '',
        ];

        yield 'One searchable field is added to the list' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_string' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'string',
                            'fullKey' => 'tx_mask_string',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext,tx_mask_string',
        ];

        yield 'One searchable field is added to the empty list' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_string' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'string',
                            'fullKey' => 'tx_mask_string',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => '',
            'expected' => 'tx_mask_string',
        ];

        yield 'Multiple searchable fields are added to the list' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_string' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'string',
                            'fullKey' => 'tx_mask_string',
                            'type' => 'string',
                        ],
                        'tx_mask_richtext' => [
                            'config' => [
                                'type' => 'text',
                            ],
                            'key' => 'richtext',
                            'fullKey' => 'tx_mask_richtext',
                            'type' => 'richtext',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext,tx_mask_string,tx_mask_richtext',
        ];

        yield 'Non-searchable field is not added to the list' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_int' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'int',
                            'fullKey' => 'tx_mask_int',
                            'type' => 'integer',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext',
        ];

        yield 'Non-existing table returns current TCA' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'tx_mask_int' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'int',
                            'fullKey' => 'tx_mask_int',
                            'type' => 'integer',
                        ],
                    ],
                ],
            ],
            'table' => 'non_existing_table',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext',
        ];

        yield 'Fields are not added twice' => [
            'json' => [
                'tt_content' => [
                    'tca' => [
                        'header' => [
                            'key' => 'header',
                            'fullKey' => 'header',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'currentTca' => 'header,bodytext',
            'expected' => 'header,bodytext',
        ];
    }

    /**
     * @test
     * @dataProvider addSearchFieldsReturnsCorrectFieldStringDataProvider
     * @param mixed[] $json
     */
    public function addSearchFieldsReturnsCorrectFieldString(array $json, string $table, string $currentTca, string $expected): void
    {
        $GLOBALS['TCA'][$table]['ctrl']['searchFields'] = $currentTca;
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());
        self::assertEquals($expected, $tcaGenerator->addSearchFields($table));
    }

    /**
     * @return mixed[]
     */
    public function extendBodytextSearchAndWhereReturnsCorrectConstraintDataProvider(): iterable
    {
        yield 'A field with bodytext is added' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'bodytext',
                            ],
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'columns' => [],
                        ],
                    ],
                    'tca' => [
                        'bodytext' => [
                            'key' => 'bodytext',
                            'fullKey' => 'bodytext',
                            'coreField' => true,
                        ],
                    ],
                ],
            ],
            'expected' => ' OR {#CType}=\'mask_element1\'',
        ];

        yield 'Multiple fields with bodytext are added' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'bodytext',
                            ],
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'columns' => [
                                'bodytext',
                            ],
                        ],
                    ],
                    'tca' => [
                        'bodytext' => [
                            'key' => 'bodytext',
                            'fullKey' => 'bodytext',
                            'coreField' => true,
                        ],
                    ],
                ],
            ],
            'expected' => ' OR {#CType}=\'mask_element1\' OR {#CType}=\'mask_element2\'',
        ];

        yield 'No field with bodytext returns empty string' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'tx_mask_bodytext',
                            ],
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'columns' => [],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_bodytext' => [
                            'key' => 'bodytext',
                            'fullKey' => 'tx_mask_bodytext',
                        ],
                    ],
                ],
            ],
            'expected' => '',
        ];
    }

    /**
     * @dataProvider extendBodytextSearchAndWhereReturnsCorrectConstraintDataProvider
     * @test
     * @param mixed[] $json
     */
    public function extendBodytextSearchAndWhereReturnsCorrectConstraint(array $json, string $expected): void
    {
        $tcaGenerator = new TcaCodeGenerator(TableDefinitionCollection::createFromArray($json), new OnlineMediaHelperRegistry());
        self::assertSame($expected, $tcaGenerator->extendBodytextSearchAndWhere());
    }
}
