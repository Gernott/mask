<?php

namespace Domain\Repository;

use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3\TestingFramework\Core\BaseTestCase;

class StorageRepositoryTest extends BaseTestCase
{
    public function loadInlineFieldsDataProvider()
    {
        return [
            'inline fields loaded' => [
                [
                    'tx_mask_a1' => [
                        'tca' => [
                            'tx_mask_a' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'a',
                                'inlineParent' => 'tx_mask_a1',
                                'order' => 1
                            ],
                            'tx_mask_b' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'b',
                                'inlineParent' => 'tx_mask_a1',
                                'order' => 2
                            ]
                        ]
                    ]
                ],
                'tx_mask_a1',
                '',
                [
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'a',
                        'inlineParent' => 'tx_mask_a1',
                        'maskKey' => 'tx_mask_a',
                        'order' => 1
                    ],
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'b',
                        'inlineParent' => 'tx_mask_a1',
                        'maskKey' => 'tx_mask_b',
                        'order' => 2
                    ]
                ]
            ],
            'inline fields loaded recursivelely' => [
                [
                    'tx_mask_a1' => [
                        'tca' => [
                            'tx_mask_a' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'a',
                                'inlineParent' => 'tx_mask_a1',
                                'order' => 1
                            ],
                            'tx_mask_b' => [
                                'config' => [
                                    'type' => 'inline'
                                ],
                                'key' => 'b',
                                'inlineParent' => 'tx_mask_a1',
                                'order' => 3
                            ]
                        ]
                    ],
                    'tx_mask_b' => [
                        'tca' => [
                            'tx_mask_c' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'c',
                                'inlineParent' => 'tx_mask_b',
                                'order' => 2
                            ]
                        ]
                    ]
                ],
                'tx_mask_a1',
                '',
                [
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'a',
                        'inlineParent' => 'tx_mask_a1',
                        'maskKey' => 'tx_mask_a',
                        'order' => 1
                    ],
                    [
                        'config' => [
                            'type' => 'inline'
                        ],
                        'key' => 'b',
                        'inlineParent' => 'tx_mask_a1',
                        'inlineFields' => [
                            [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'c',
                                'inlineParent' => 'tx_mask_b',
                                'maskKey' => 'tx_mask_c',
                                'order' => 2
                            ]
                        ],
                        'maskKey' => 'tx_mask_b',
                        'order' => 3
                    ]
                ]
            ],
            'inline fields of palette loaded in same table' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_a' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'a'
                            ],
                            'tx_mask_b' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'b',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_a'
                                ],
                                'order' => [
                                    'element1' => 2
                                ],
                            ],
                            'tx_mask_c' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'c',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_a'
                                ],
                                'order' => [
                                    'element1' => 3
                                ],
                            ]
                        ]
                    ],
                ],
                'tx_mask_a',
                'element1',
                [
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'b',
                        'inPalette' => '1',
                        'inlineParent' => [
                            'element1' => 'tx_mask_a'
                        ],
                        'order' => [
                            'element1' => 2
                        ],
                        'maskKey' => 'tx_mask_b'
                    ],
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'c',
                        'inPalette' => '1',
                        'inlineParent' => [
                            'element1' => 'tx_mask_a'
                        ],
                        'order' => [
                            'element1' => 3
                        ],
                        'maskKey' => 'tx_mask_c'
                    ]
                ]
            ],
            'inline fields of palette loaded in inline field' => [
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline'
                                ],
                                'key' => 'repeat'
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'tx_mask_a' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'a',
                                'inlineParent' => 'tx_mask_repeat',
                                'order' => 1
                            ],
                            'tx_mask_b' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'b',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 3
                                ]
                            ],
                            'tx_mask_c' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'c',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 4
                                ]
                            ],
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette',
                                'inlineParent' => 'tx_mask_repeat',
                                'order' => 2
                            ],
                        ]
                    ]
                ],
                'tx_mask_repeat',
                'element1',
                [
                    [
                        'config' => [
                            'type' => 'input'
                        ],
                        'key' => 'a',
                        'inlineParent' => 'tx_mask_repeat',
                        'maskKey' => 'tx_mask_a',
                        'order' => 1
                    ],
                    [
                        'config' => [
                            'type' => 'palette'
                        ],
                        'key' => 'palette',
                        'inlineParent' => 'tx_mask_repeat',
                        'maskKey' => 'tx_mask_palette',
                        'order' => 2,
                        'inlineFields' => [
                            [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'b',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 3
                                ],
                                'maskKey' => 'tx_mask_b'
                            ],
                            [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'c',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 4
                                ],
                                'maskKey' => 'tx_mask_c'
                            ],
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider loadInlineFieldsDataProvider
     * @test
     * @param $json
     * @param $parentKey
     * @param $elementKey
     * @param $expected
     */
    public function loadInlineFields($json, $parentKey, $elementKey, $expected)
    {
        $storageRepository = $this->createPartialMock(StorageRepository::class, ['load']);
        $storageRepository->method('load')->willReturn($json);
        self::assertEquals($expected, $storageRepository->loadInlineFields($parentKey, $elementKey));
    }

    public function getElementsWhichUseFieldDataProvider()
    {
        return [
            'Element which uses field is returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'tx_mask_column_2',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3'
                        ]
                    ]
                ]
            ],
            'Multiple elements which uses field are returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'tx_mask_column_1',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3'
                        ]
                    ],
                    [
                        'key' => 'element_2',
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_5',
                            'tx_mask_column_6'
                        ]
                    ]
                ]
            ],
            'Elements in other table are returned' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'tx_mask_column_2',
                'pages',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3'
                        ]
                    ]
                ]
            ],
            'Fields in palettes are considered' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'palette_1'
                                ]
                            ]
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'showitem' => ['tx_mask_column_1']
                            ]
                        ],
                        'tca' => [
                            'palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ]
                            ]
                        ]
                    ]
                ],
                'tx_mask_column_1',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'tx_mask_column_1',
                        ]
                    ],
                    [
                        'key' => 'element_2',
                        'columns' => [
                            'palette_1'
                        ]
                    ]
                ]
            ],
            'core fields in palettes without config are considered' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'palette_1',
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'palette_2'
                                ]
                            ]
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'showitem' => ['header']
                            ],
                            'palette_2' => [
                                'showitem' => ['header']
                            ]
                        ],
                        'tca' => [
                            'palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ]
                            ],
                            'palette_2' => [
                                'config' => [
                                    'type' => 'palette'
                                ]
                            ],
                            'header' => [
                                'inlineParent' => [
                                    'element1' => 'palette_1',
                                    'element2' => 'palette_2'
                                ]
                            ]
                        ]
                    ]
                ],
                'header',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'palette_1',
                        ]
                    ],
                    [
                        'key' => 'element_2',
                        'columns' => [
                            'palette_2'
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider getElementsWhichUseFieldDataProvider
     * @test
     * @param $json
     * @param $column
     * @param $table
     * @param $expected
     */
    public function getElementsWhichUseField($json, $column, $table, $expected)
    {
        $storage = $this->createPartialMock(StorageRepository::class, ['load']);
        $storage->expects(self::any())->method('load')->willReturn($json);
        self::assertSame($expected, $storage->getElementsWhichUseField($column, $table));
    }

    public function addDataProvider()
    {
        return [
            'fields are added' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'field1',
                            'field2',
                            'header'
                        ],
                        'labels' => [
                            'Field 1',
                            'Field 2',
                            'Header'
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'input'
                            ]
                        ],
                        [
                            'config' => [
                                'type' => 'input'
                            ]
                        ]
                    ],
                    'sql' => [
                        'tt_content' => [
                            'tinytext',
                            'tinytext'
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_field1',
                                    'tx_mask_field2',
                                    'header'
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2',
                                    'Header'
                                ]
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext'
                                ]
                            ],
                            'tx_mask_field2' => [
                                'tt_content' => [
                                    'tx_mask_field2' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'key' => 'field1',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_field2' => [
                                'key' => 'field2',
                                'config' => [
                                    'type' => 'input'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'existing typo3 fields do not generate tca nor sql' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'header',
                        ],
                        'labels' => [
                            'Header 1',
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'header',
                                ],
                                'labels' => [
                                    'Header 1',
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            'existing custom fields do not generate tca nor sql' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'tx_mask_header',
                        ],
                        'labels' => [
                            'Header 1',
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_header',
                                ],
                                'labels' => [
                                    'Header 1',
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            'inline fields are added as new table' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'inline_field',
                            'field1',
                        ],
                        'labels' => [
                            'Inline Field',
                            'Field 1'
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'inline'
                            ]
                        ],
                        [
                            'config' => [
                                'type' => 'input',
                            ],
                            'inlineParent' => 'tx_mask_inline_field',
                            'label' => 'Field 1'
                        ]
                    ],
                    'sql' => [
                        'tt_content' => [
                            0 => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                        'tx_mask_inline_field' => [
                            1 => 'tinytext',
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_inline_field'
                                ],
                                'labels' => [
                                    'Inline Field',
                                ]
                            ]
                        ],
                        'sql' => [
                            'tx_mask_inline_field' => [
                                'tt_content' => [
                                    'tx_mask_inline_field' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                                ]
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline_field' => [
                                'key' => 'inline_field',
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ],
                        ]
                    ],
                    'tx_mask_inline_field' => [
                        'sql' => [
                            'tx_mask_field1' => [
                                'tx_mask_inline_field' => [
                                    'tx_mask_field1' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'key' => 'field1',
                                'label' => 'Field 1',
                                'inlineParent' => 'tx_mask_inline_field',
                                'order' => 1,
                                'config' => [
                                    'type' => 'input'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'RTE option is set to 1' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'inline_field',
                            'field1',
                        ],
                        'labels' => [
                            'Inline Field',
                            'Field 1'
                        ],
                        'options' => [
                            1 => 'rte'
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'inline'
                            ]
                        ],
                        [
                            'config' => [
                                'type' => 'text',
                            ],
                            'inlineParent' => 'tx_mask_inline_field',
                            'label' => 'Field 1'
                        ]
                    ],
                    'sql' => [
                        'tt_content' => [
                            0 => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                        'tx_mask_inline_field' => [
                            1 => 'text',
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_inline_field'
                                ],
                                'labels' => [
                                    'Inline Field',
                                ],
                                'options' => [
                                    1 => 'rte'
                                ]
                            ]
                        ],
                        'sql' => [
                            'tx_mask_inline_field' => [
                                'tt_content' => [
                                    'tx_mask_inline_field' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                                ]
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline_field' => [
                                'key' => 'inline_field',
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ],
                        ]
                    ],
                    'tx_mask_inline_field' => [
                        'sql' => [
                            'tx_mask_field1' => [
                                'tx_mask_inline_field' => [
                                    'tx_mask_field1' => 'text'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'key' => 'field1',
                                'label' => 'Field 1',
                                'inlineParent' => 'tx_mask_inline_field',
                                'order' => 1,
                                'rte' => '1',
                                'config' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Timestamps range converted to integer' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'timestamp',
                        ],
                        'labels' => [
                            'Timestamp',
                        ],
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'input',
                                'renderType' => 'inputDateTime',
                                'eval' => 'date,int',
                                'range' => [
                                    'lower' => '00:00 01.01.2021',
                                    'upper' => '00:00 15.01.2021'
                                ]
                            ]
                        ],
                    ],
                    'sql' => [
                        'tt_content' => [
                            0 => 'int',
                        ],
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_timestamp'
                                ],
                                'labels' => [
                                    'Timestamp',
                                ],
                            ]
                        ],
                        'sql' => [
                            'tx_mask_timestamp' => [
                                'tt_content' => [
                                    'tx_mask_timestamp' => 'int'
                                ]
                            ],
                        ],
                        'tca' => [
                            'tx_mask_timestamp' => [
                                'key' => 'timestamp',
                                'config' => [
                                    'type' => 'input',
                                    'renderType' => 'inputDateTime',
                                    'eval' => 'date,int',
                                    'range' => [
                                        'lower' => 1609459200,
                                        'upper' => 1610668800
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            'inline fields with no children are not added as new table' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'inline_field',
                        ],
                        'labels' => [
                            'Inline Field',
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'inline'
                            ]
                        ],
                    ],
                    'sql' => [
                        'tt_content' => [
                            0 => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_inline_field'
                                ],
                                'labels' => [
                                    'Inline Field',
                                ]
                            ]
                        ],
                        'sql' => [
                            'tx_mask_inline_field' => [
                                'tt_content' => [
                                    'tx_mask_inline_field' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                                ]
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline_field' => [
                                'key' => 'inline_field',
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            'palettes are added' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'palette',
                            'header',
                            'field'
                        ],
                        'labels' => [
                            'My Palette',
                            'Header',
                            'Field'
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'sql' => [
                        'tt_content' => [
                            2 => 'tinytext'
                        ]
                    ],
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'palette'
                            ]
                        ],
                        [
                            'inlineParent' => 'tx_mask_palette',
                            'inPalette' => '1',
                            'label' => 'Header'
                        ],
                        [
                            'config' => [
                                'type' => 'input',
                            ],
                            'inlineParent' => 'tx_mask_palette',
                            'inPalette' => '1',
                            'label' => 'Field'
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'labels' => [
                                    'My Palette',
                                ]
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'My Palette',
                                'showitem' => ['header', 'tx_mask_field']
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element1' => 'Field'
                                ],
                                'order' => [
                                    'element1' => 2
                                ]
                            ],
                            'header' => [
                                'key' => 'header',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element1' => 'Header'
                                ],
                                'order' => [
                                    'element1' => 1
                                ],
                                'coreField' => '1'
                            ]
                        ]
                    ]
                ]
            ],
            'existing custom field in another element added freshly to palette' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_palette_1'
                                ],
                                'labels' => [
                                    'Palette 1'
                                ],
                                'key' => 'element1',
                                'label' => 'Element 1'
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_field_1', 'header']
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field_1' => [
                                'tt_content' => [
                                    'tx_mask_field_1' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette_1'
                                ],
                                'label' => [
                                    'element1' => 'Field 1 in Element 1'
                                ],
                                'order' => [
                                    'element1' => 1
                                ]
                            ],
                            'header' => [
                                'key' => 'header',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette_1'
                                ],
                                'label' => [
                                    'element1' => 'Header in Element 1'
                                ],
                                'order' => [
                                    'element1' => 2
                                ],
                                'coreField' => '1'
                            ]
                        ]
                    ]
                ],
                [
                    'elements' => [
                        'label' => 'Element 2',
                        'key' => 'element2',
                        'columns' => [
                            'palette_2',
                            'tx_mask_field_1',
                            'header'
                        ],
                        'labels' => [
                            'Palette 2',
                            'Field 1 in Palette 2',
                            'Header 1 in Element 2',
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => '',
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'palette'
                            ]
                        ],
                        [
                            'inlineParent' => 'tx_mask_palette_2',
                            'label' => 'Field 1 in Element 2',
                            'inPalette' => '1'
                        ],
                        [
                            'inlineParent' => 'tx_mask_palette_2',
                            'label' => 'Header in Element 2',
                            'inPalette' => '1'
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                                'labels' => [
                                    'Palette 1',
                                ]
                            ],
                            'element2' => [
                                'label' => 'Element 2',
                                'key' => 'element2',
                                'columns' => [
                                    'tx_mask_palette_2'
                                ],
                                'labels' => [
                                    'Palette 2',
                                ]
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_field_1', 'header']
                            ],
                            'tx_mask_palette_2' => [
                                'label' => 'Palette 2',
                                'showitem' => ['tx_mask_field_1', 'header']
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field_1' => [
                                'tt_content' => [
                                    'tx_mask_field_1' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'tx_mask_palette_2' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_2'
                            ],
                            'tx_mask_field_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette_1',
                                    'element2' => 'tx_mask_palette_2'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element1' => 'Field 1 in Element 1',
                                    'element2' => 'Field 1 in Element 2',
                                ],
                                'order' => [
                                    'element1' => 1,
                                    'element2' => 1
                                ]
                            ],
                            'header' => [
                                'key' => 'header',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette_1',
                                    'element2' => 'tx_mask_palette_2'
                                ],
                                'label' => [
                                    'element1' => 'Header in Element 1',
                                    'element2' => 'Header in Element 2',
                                ],
                                'order' => [
                                    'element1' => 2,
                                    'element2' => 2
                                ],
                                'coreField' => '1'
                            ]
                        ]
                    ]
                ]
            ],
            'root palette having normal field after inline field. Only one palette is created' => [
                [],
                [
                    'elements' => [
                        'label' => 'Element 1',
                        'key' => 'element1',
                        'columns' => [
                            'palette',
                            'inline',
                            'inline_field',
                            'palette_inline',
                            'palette_field_inline',
                            'header'
                        ],
                        'labels' => [
                            'My Palette',
                            'Inline',
                            'Inline Field',
                            'Palette Inline',
                            'Palette Field Inline',
                            'Header'
                        ]
                    ],
                    'type' => 'tt_content',
                    'orgKey' => 'element1',
                    'sql' => [
                        'tt_content' => [
                            1 => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ],
                        'tx_mask_inline' => [
                            2 => 'tinytext',
                            4 => 'tinytext'
                        ],
                    ],
                    'tca' => [
                        [
                            'config' => [
                                'type' => 'palette'
                            ]
                        ],
                        [
                            'config' => [
                                'type' => 'inline',
                            ],
                            'inlineParent' => 'tx_mask_palette',
                            'inPalette' => '1',
                            'label' => 'Inline'
                        ],
                        [
                            'config' => [
                                'type' => 'input'
                            ],
                            'inlineParent' => 'tx_mask_inline',
                            'label' => 'Inline Field'
                        ],
                        [
                            'config' => [
                                'type' => 'palette'
                            ],
                            'inlineParent' => 'tx_mask_inline',
                            'label' => 'Palette Inline'
                        ],
                        [
                            'config' => [
                                'type' => 'input'
                            ],
                            'inlineParent' => 'tx_mask_palette_inline',
                            'inPalette' => '1',
                            'label' => 'Palette Field Inline'
                        ],
                        [
                            'inlineParent' => 'tx_mask_palette',
                            'inPalette' => '1',
                            'label' => 'Header'
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'label' => 'Element 1',
                                'key' => 'element1',
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'labels' => [
                                    'My Palette',
                                ]
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'My Palette',
                                'showitem' => ['tx_mask_inline', 'header']
                            ]
                        ],
                        'sql' => [
                            'tx_mask_inline' => [
                                'tt_content' => [
                                    'tx_mask_inline' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_inline' => [
                                'config' => [
                                    'type' => 'inline'
                                ],
                                'key' => 'inline',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element1' => 'Inline'
                                ],
                                'order' => [
                                    'element1' => 1
                                ]
                            ],
                            'header' => [
                                'key' => 'header',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element1' => 'Header'
                                ],
                                'order' => [
                                    'element1' => 5
                                ],
                                'coreField' => '1'
                            ]
                        ]
                    ],
                    'tx_mask_inline' => [
                        'sql' => [
                            'tx_mask_inline_field' => [
                                'tx_mask_inline' => [
                                    'tx_mask_inline_field' => 'tinytext',
                                ]
                            ],
                            'tx_mask_palette_field_inline' => [
                                'tx_mask_inline' => [
                                    'tx_mask_palette_field_inline' => 'tinytext'
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_inline_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'inline_field',
                                'order' => 2,
                                'label' => 'Inline Field',
                                'inlineParent' => 'tx_mask_inline'
                            ],
                            'tx_mask_palette_inline' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_inline',
                                'order' => 3,
                                'inlineParent' => 'tx_mask_inline',
                                'label' => 'Palette Inline'
                            ],
                            'tx_mask_palette_field_inline' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'palette_field_inline',
                                'order' => 4,
                                'label' => 'Palette Field Inline',
                                'inlineParent' => 'tx_mask_palette_inline',
                                'inPalette' => '1'
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette_inline' => [
                                'showitem' => [
                                    'tx_mask_palette_field_inline'
                                ],
                                'label' => 'Palette Inline',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @param $json
     * @param $content
     * @param $expected
     * @dataProvider addDataProvider
     * @test
     */
    public function add($json, $content, $expected)
    {
        $storageRepository = $this->createPartialMock(StorageRepository::class, ['load']);
        $storageRepository->expects(self::any())->method('load')->willReturn($json);
        self::assertEquals($expected, $storageRepository->add($content));
    }

    public function removeDataProvider()
    {
        return [
            'fields are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_field',
                                    'tx_mask_field_2'
                                ],
                                'key' => 'element1'
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field'
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field_2'
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ],
                            'tx_mask_field_2' => [
                                'tt_content' => [
                                    'tx_mask_field_2' => 'tinytext'
                                ]
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ]
                ]
            ],
            'inline fields are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                                'key' => 'element1'
                            ]
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ],
                        ],
                        'sql' => [
                            'tx_mask_inline' => [
                                'tt_content' => [
                                    'tx_mask_inline' => 'int'
                                ]
                            ],
                        ]
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field',
                                'inlineParent' => 'tx_mask_inline',
                                'order' => 1
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field_2',
                                'inlineParent' => 'tx_mask_inline',
                                'order' => 2
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ],
                            'tx_mask_field_2' => [
                                'tt_content' => [
                                    'tx_mask_field_2' => 'tinytext'
                                ]
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ]
                ]
            ],
            'palette fields and palettes are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'key' => 'element1'
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 1
                                ],
                                'inPalette' => '1'
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field_2',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette'
                                ],
                                'order' => [
                                    'element1' => 2
                                ],
                                'inPalette' => '1'
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ],
                            'tx_mask_field_2' => [
                                'tt_content' => [
                                    'tx_mask_field_2' => 'tinytext'
                                ]
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'Palette',
                                'showitem' => ['tx_mask_field', 'tx_mask_field_2']
                            ]
                        ]
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ]
                ]
            ],
            'palette fields in use only inlineParent removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'key' => 'element1'
                            ],
                            'element2' => [
                                'columns' => [
                                    'tx_mask_palette2'
                                ],
                                'key' => 'element2'
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_palette2' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                    'element2' => 'tx_mask_palette2'
                                ],
                                'label' => [
                                    'element1' => 'Field',
                                    'element2' => 'Field'
                                ],
                                'order' => [
                                    'element1' => 1,
                                    'element2' => 1
                                ],
                                'inPalette' => '1'
                            ],
                            'header' => [
                                'coreField' => '1',
                                'key' => 'header',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                    'element2' => 'tx_mask_palette2'
                                ],
                                'label' => [
                                    'element1' => 'Header',
                                    'element2' => 'Header'
                                ],
                                'order' => [
                                    'element1' => 2,
                                    'element2' => 2
                                ],
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'Palette',
                                'showitem' => ['tx_mask_field', 'header']
                            ],
                            'tx_mask_palette2' => [
                                'label' => 'Palette 2',
                                'showitem' => ['tx_mask_field', 'header']
                            ]
                        ]
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [
                            'element2' => [
                                'columns' => [
                                    'tx_mask_palette2'
                                ],
                                'key' => 'element2'
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette2' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element2' => 'tx_mask_palette2'
                                ],
                                'label' => [
                                    'element2' => 'Field'
                                ],
                                'order' => [
                                    'element2' => 1
                                ],
                                'inPalette' => '1'
                            ],
                            'header' => [
                                'coreField' => '1',
                                'key' => 'header',
                                'inlineParent' => [
                                    'element2' => 'tx_mask_palette2'
                                ],
                                'label' => [
                                    'element2' => 'Header'
                                ],
                                'order' => [
                                    'element2' => 2
                                ],
                                'inPalette' => '1'
                            ]
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'tinytext'
                                ]
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette2' => [
                                'label' => 'Palette 2',
                                'showitem' => ['tx_mask_field', 'header']
                            ]
                        ]
                    ],
                ]
            ],
        ];
    }

    /**
     * @param $json
     * @param $table
     * @param $elementKey
     * @param $remainingFields
     * @param $expected
     * @test
     * @dataProvider removeDataProvider
     */
    public function remove($json, $table, $elementKey, $expected)
    {
        $storageRepository = $this->createPartialMock(StorageRepository::class, ['load']);
        $storageRepository->expects(self::any())->method('load')->willReturn($json);
        self::assertEquals($expected, $storageRepository->remove($table, $elementKey));
    }

    public function getFormTypeDataProvider()
    {
        return [
            'Type String is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::STRING
            ],
            'Type Integer is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'int'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::INTEGER
            ],
            'Type Float is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'double2'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::FLOAT
            ],
            'Type Date is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'date',
                                    'dbType' => 'date'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::DATE
            ],
            'Type Datetime is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'datetime',
                                    'dbType' => 'datetime'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::DATETIME
            ],
            'Type Link is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'renderType' => 'inputLink'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::LINK
            ],
            'Type Text is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'text',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::TEXT
            ],
            'Type Richtext is returned' => [
                [],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'field1'
                                ],
                                'options' => [
                                    'rte'
                                ]
                            ]
                        ],
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'text',
                                ],
                                'rte' => '1'
                            ]
                        ]
                    ]
                ],
                'field1',
                'element1',
                'tt_content',
                FieldType::RICHTEXT
            ],
            'Type Richtext is returned if in inline' => [
                [],
                [
                    'tx_mask_inline' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'text',
                                ],
                                'rte' => '1'
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tx_mask_inline',
                FieldType::RICHTEXT
            ],
            'Type Check is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'check',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::CHECK
            ],
            'Type Radio is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'radio',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::RADIO
            ],
            'Type Select is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'select',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::SELECT
            ],
            'Type Inline is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'inline',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::INLINE
            ],
            'Type File is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'sys_file_reference'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::FILE
            ],
            'Type File by option is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'options' => 'file'
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::FILE
            ],
            'Type Content is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'tt_content'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::CONTENT
            ],
            'Type Tab is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'tab',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::TAB
            ],
            'Type Palette is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'palette',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::PALETTE
            ],
            'Type Group is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'group',
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::GROUP
            ],
            'Type Timestamp is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'evaĺ' => 'date,int',
                                    'renderType' => 'inputDateTime'
                                ]
                            ]
                        ]
                    ]
                ],
                'field1',
                '',
                'tt_content',
                FieldType::TIMESTAMP
            ],
            'Type from global tca is returned' => [
                [
                    'tt_content' => [
                        'columns' => [
                            'date' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' =>  'date',
                                    'dbType' => 'date'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'tt_content' => [
                        'tca' => [
                            'field1' => [
                                'config' => [
                                    'type' => 'group',
                                ]
                            ]
                        ]
                    ]
                ],
                'date',
                '',
                'tt_content',
                FieldType::DATE
            ],
        ];
    }

    /**
     * @param $tca
     * @param $json
     * @param $fieldKey
     * @param $elementKey
     * @param $table
     * @param $expected
     * @test
     * @dataProvider getFormTypeDataProvider
     */
    public function getFormType($tca, $json, $fieldKey, $elementKey, $table, $expected)
    {
        $GLOBALS['TCA'] = $tca;
        $storageRepository = $this->createPartialMock(StorageRepository::class, ['load']);
        $storageRepository->expects(self::any())->method('load')->willReturn($json);
        self::assertEquals($expected, $storageRepository->getFormType($fieldKey, $elementKey, $table));
    }
}
