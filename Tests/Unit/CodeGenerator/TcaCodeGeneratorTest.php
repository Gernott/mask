<?php

namespace MASK\Mask\Test\CodeGenerator;

use MASK\Mask\CodeGenerator\TcaCodeGenerator;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\FieldHelper;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TcaCodeGeneratorTest extends BaseTestCase
{
    /**
     * @return array[]
     **/
    public function getPageTcaDataProvider()
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
                                    'tx_mask_a_in_a_tab'
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
                                    'A Feld'
                                ],
                            ],
                            '2' => [
                                'columns' => [
                                    'tx_mask_d_in_default_tab',
                                    'tx_mask_c_in_default_tab'
                                ],
                                'label' => 'Backend Layout 2',
                                'description' => 'Test backend layout 2',
                                'shortLabel' => 'BL 2',
                                'key' => '2',
                                'labels' => [
                                    'In Standard Tab',
                                    'In Stamdard Tab 2',
                                ],
                            ]
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab'
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab'
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab'
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab'
                            ],
                            'tx_mask_c_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'c_in_default_tab'
                            ],
                            'tx_mask_d_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'd_in_default_tab'
                            ],
                        ]
                    ]
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
                                    'tx_mask_a_in_a_tab'
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
                                    'A Feld'
                                ],
                            ],
                            '2' => [
                                'columns' => [
                                    'tx_mask_d_in_default_tab',
                                    'tx_mask_c_in_default_tab'
                                ],
                                'label' => 'Backend Layout 2',
                                'description' => 'Test backend layout 2',
                                'shortLabel' => 'BL 2',
                                'key' => '2',
                                'labels' => [
                                    'In Standard Tab',
                                    'In Standard Tab 2',
                                ],
                            ]
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab'
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab'
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab'
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab'
                            ],
                            'tx_mask_c_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'c_in_default_tab'
                            ],
                            'tx_mask_d_in_default_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'd_in_default_tab'
                            ],
                        ]
                    ]
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
                                    'tx_mask_a_in_a_tab'
                                ],
                                'label' => 'Backend Layout 1',
                                'description' => 'Test backend layout',
                                'shortLabel' => 'BL 1',
                                'key' => '1',
                                'labels' => [
                                    'B Tab',
                                    'B Feld',
                                    'A Tab',
                                    'A Feld'
                                ],
                            ]
                        ],
                        'tca' => [
                            'tx_mask_a_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'a_tab'
                            ],
                            'tx_mask_a_in_a_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'a_in_a_tab'
                            ],
                            'tx_mask_b_tab' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'b_tab'
                            ],
                            'tx_mask_b_in_b_tab' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'b_in_b_tab'
                            ],
                        ]
                    ]
                ],
                '1',
                ',--div--;B Tab,tx_mask_b_in_b_tab,--div--;A Tab,tx_mask_a_in_a_tab',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getPageTcaDataProvider
     * @param $json
     * @param $key
     * @param $expected
     */
    public function getPageTca($json, $key, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->onlyMethods(['load'])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        $tcaGenerator = new TcaCodeGenerator($storage, $fieldHelper);
        self::assertSame($expected, $tcaGenerator->getPageTca($key));
    }

    public function getMaskIrreTablesDataProvider()
    {
        return [
            'Returns all mask inline tables' => [
                [
                    'pages' => [],
                    'sys_file_reference' => [],
                    'tx_mask_repeat' => [],
                    'tx_mask_accordion' => []
                ],
                ['tx_mask_repeat', 'tx_mask_accordion']
            ]
        ];
    }

    /**
     * @dataProvider getMaskIrreTablesDataProvider
     * @test
     * @param $json
     * @param $expected
     */
    public function getMaskIrreTables($json, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        $tcaGenerator = new TcaCodeGenerator($storage, $fieldHelper);
        self::assertSame($expected, $tcaGenerator->getMaskIrreTables());
    }

    public function processTableTcaDataProvider()
    {
        return [
            'Order is correct and tab is put correctly' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'field_2' => [
                                'label' => 'Field 2',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'field_1' => [
                                'label' => 'Field 1',
                                'order' => '1',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'field_3' => [
                                'label' => 'Field 3',
                                'order' => '4',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tab_field' => [
                                'label' => 'New Tab',
                                'order' => '3',
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                        ]
                    ]
                ],
                [
                    'label' => 'field_1',
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,field_1,field_2,--div--;New Tab,field_3'
                ]
            ],
            'Tab at first position overrides general tab' => [
                'tx_mask_repeater',
                [
                    'tx_mask_repeater' => [
                        'tca' => [
                            'field_2' => [
                                'label' => 'Field 2',
                                'order' => '3',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'field_1' => [
                                'label' => 'Field 1',
                                'order' => '2',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'field_3' => [
                                'label' => 'Field 3',
                                'order' => '4',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tab_field' => [
                                'label' => 'New Tab',
                                'order' => '1',
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                        ]
                    ]
                ],
                [
                    'label' => 'field_1',
                    'showitem' => '--div--;New Tab,field_1,field_2,field_3'
                ]
            ]
        ];
    }

    /**
     * @dataProvider processTableTcaDataProvider
     * @test
     * @param $table
     * @param $json
     * @param $expected
     */
    public function processTableTca($table, $json, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->onlyMethods(['load'])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        $tcaGenerator = new TcaCodeGenerator($storage, $fieldHelper);
        self::assertSame($expected, $tcaGenerator->processTableTca($table, $json[$table]['tca']));
    }

    public function generateFieldsTcaDataProvider()
    {
        return [
            'Input fields are processd correctly' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => ''
                                ],
                                'key' => 'field_1'
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'trim'
                                ],
                                'key' => 'field_2'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'input'
                        ],
                    ],
                    'tx_mask_field_2' => [
                        'config' => [
                            'type' => 'input',
                            'eval' => 'trim'
                        ],
                    ]
                ]
            ],
            'Text fields are processd correctly' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'text',
                                    'eval' => '',
                                    'format' => 'typoscript'
                                ],
                                'key' => 'field_1'
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'trim'
                                ],
                                'key' => 'field_2'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'text',
                            'format' => 'typoscript',
                            'renderType' => 't3editor'
                        ],
                    ],
                    'tx_mask_field_2' => [
                        'config' => [
                            'type' => 'input',
                            'eval' => 'trim'
                        ],
                    ]
                ]
            ],
            'Tabs are ignored' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => ''
                                ],
                                'key' => 'field_1'
                            ],
                            'tx_mask_tab' => [
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'trim'
                                ],
                                'key' => 'field_2'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'input'
                        ],
                    ],
                    'tx_mask_field_2' => [
                        'config' => [
                            'type' => 'input',
                            'eval' => 'trim'
                        ],
                    ]
                ]
            ],
            'Foreign table of inline fields is replaced' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => '--inlinetable--'
                                ],
                                'key' => 'field_1'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'inline',
                            'foreign_table' => 'tx_mask_field_1'
                        ],
                    ],
                ]
            ],
            'Date fields ranges are applied' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'input',
                                    'dbType' => 'date',
                                    'eval' => 'date',
                                    'range' => [
                                        'lower' => '01-01-2021',
                                        'upper' => '30-12-2021'
                                    ]
                                ],
                                'key' => 'field_1'
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                    'dbType' => 'datetime',
                                    'eval' => 'datetime',
                                    'range' => [
                                        'upper' => '20:30 30-12-2021'
                                    ]
                                ],
                                'key' => 'field_2'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'input',
                            'dbType' => 'date',
                            'eval' => 'date,null',
                            'range' => [
                                'lower' => 1609459200,
                                'upper' => 1640822400
                            ]
                        ],
                    ],
                    'tx_mask_field_2' => [
                        'config' => [
                            'type' => 'input',
                            'dbType' => 'datetime',
                            'eval' => 'datetime,null',
                            'range' => [
                                'upper' => 1640896200
                            ]
                        ],
                    ]
                ]
            ],
            'Content inline fields are processed correctly' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'cTypes' => [
                                    'text',
                                    'textmedia'
                                ],
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'tt_content'
                                ],
                                'key' => 'field_1'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                [
                    'tx_mask_field_1' => [
                        'config' => [
                            'type' => 'inline',
                            'foreign_table' => 'tt_content',
                            'foreign_field' => 'tx_mask_field_1_parent',
                            'overrideChildTca' => [
                                'columns' => [
                                    'CType' => [
                                        'config' => [
                                            'default' => 'text'
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider generateFieldsTcaDataProvider
     * @test
     * @param $json
     * @param $table
     * @param $expected
     */
    public function generateFieldsTca($json, $table, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        $tcaGenerator = new TcaCodeGenerator($storage, $fieldHelper);
        self::assertSame($expected, $tcaGenerator->generateFieldsTca($table));
    }

    public function generateFileTcaDataProvider()
    {
        return [
            'Files are processed correctly' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field_1' => [
                                'config' => [
                                    'filter' => [
                                        [
                                            'parameters' => [
                                                'allowedFileExtensions' => 'jpeg',
                                            ]
                                        ]
                                    ],
                                    'appearance' => [
                                        'useSortable' => false,
                                        'fileUploadAllowed' => true,
                                        'expandSingle' => true
                                    ],
                                    'minitems' => '5',
                                    'maxitems' => '10'
                                ],
                                'key' => 'field_1',
                                'options' => 'file'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'tx_mask_field_1',
                [
                    'type' => 'inline',
                    'foreign_match_fields' => 'tx_mask_field_1',
                    'elementBrowserAllowed' => 'jpeg',
                    'minitems' => '5',
                    'maxitems' => '10',
                    'appearance' => [
                        'useSortable' => false,
                        'fileUploadAllowed' => true,
                        'expandSingle' => true,
                        'headerThumbnail' => [
                            'field' => 'uid_local',
                            'width' => '45',
                            'height' => '45c'
                        ],
                        'enabledControls' => [
                            'info' => true,
                            'new' => false,
                            'dragdrop' => true,
                            'sort' => false,
                            'hide' => true,
                            'delete' => true,
                        ],
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider generateFileTcaDataProvider
     * @test
     * @param $json
     * @param $table
     * @param $field
     * @param $expected
     */
    public function generateFileTca($json, $table, $field, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        $tcaGenerator = new TcaCodeGenerator($storage, $fieldHelper);
        $result = $tcaGenerator->generateFieldsTca($table);
        self::assertSame($expected['type'], $result[$field]['config']['type']);
        self::assertSame($expected['minitems'], $result[$field]['config']['minitems']);
        self::assertSame($expected['maxitems'], $result[$field]['config']['maxitems']);
        self::assertSame($expected['elementBrowserAllowed'], $result[$field]['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed']);
        self::assertEquals($expected['elementBrowserAllowed'], $result[$field]['config']['filter'][0]['parameters']['allowedFileExtensions']);
        self::assertSame($expected['foreign_match_fields'], $result[$field]['config']['foreign_match_fields']['fieldname']);
        self::assertEquals($expected['appearance'], $result[$field]['config']['appearance']);
    }
}
