<?php

namespace MASK\Mask\Test\Helper;

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
use TYPO3\TestingFramework\Core\BaseTestCase;

class FieldHelperTest extends BaseTestCase
{
    public function getLabelDataProvider()
    {
        return [
            'Correct label is returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'tx_mask_tx_mask_column_1',
                                    'tx_mask_tx_mask_column_2',
                                    'tx_mask_tx_mask_column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_tx_mask_column_2',
                'tt_content',
                'Label 2'
            ],
            'Empty string if element does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'tx_mask_tx_mask_column_1',
                                    'tx_mask_tx_mask_column_2',
                                    'tx_mask_tx_mask_column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_4',
                'tx_mask_tx_mask_column_2',
                'tt_content',
                ''
            ],
            'Empty string if field does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'tx_mask_tx_mask_column_1',
                                    'tx_mask_tx_mask_column_2',
                                    'tx_mask_tx_mask_column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_tx_mask_column_4',
                'tt_content',
                ''
            ],
            'Core field returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Header 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'header',
                                    'tx_mask_tx_mask_column_2',
                                    'tx_mask_tx_mask_column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'header',
                'tt_content',
                'Header 1'
            ],
            'Core field in palette returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Palette 1',
                                ],
                                'columns' => [
                                    'tx_mask_palette_1',
                                ]
                            ]
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['header']
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'header' => [
                                'inlineParent' => 'tx_mask_palette_1',
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1'
                                ]
                            ]
                        ]
                    ],
                ],
                'element_1',
                'header',
                'tt_content',
                'Header 1'
            ]
        ];
    }

    /**
     * @dataProvider getLabelDataProvider
     * @test
     * @param $json
     * @param $elementKey
     * @param $fieldKey
     * @param $type
     * @param $expected
     */
    public function getLabel($json, $elementKey, $fieldKey, $type, $expected)
    {
        $storage = $this->createPartialMock(StorageRepository::class, ['load']);
        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getLabel($elementKey, $fieldKey, $type));
    }

    public function getFieldTypeDataProvider()
    {
        return [
            'Correct table is returned for field' => [
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
                'tx_mask_column_1',
                'element_1',
                false,
                'tt_content'
            ],
            'Correct table is returned for field 2' => [
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
                'tx_mask_column_4',
                'element_2',
                false,
                'pages'
            ],
            'First table is returned for ambiguous field' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ]
                        ]
                    ],
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
                        ]
                    ],
                ],
                'tx_mask_column_1',
                '',
                false,
                'tt_content'
            ],
            'First table is not returned if elementKey is not empty' => [
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
                        ]
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ]
                        ]
                    ],
                ],
                'tx_mask_column_1',
                'element_2',
                false,
                'tt_content'
            ],
            'Correct table is returned for field and element' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ]
                        ]
                    ],
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
                        ]
                    ],
                ],
                'tx_mask_column_1',
                'element_1',
                false,
                'pages'
            ],
            'Inline is not excluded by default' => [
                [
                    'tx_mask_custom_table' => [
                        'tca' => [
                            'tx_mask_column_1' => [],
                            'tx_mask_column_2' => [],
                            'tx_mask_column_3' => [],
                        ]
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ]
                        ]
                    ],
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
                        ]
                    ],
                ],
                'tx_mask_column_1',
                'element_1',
                false,
                'tx_mask_custom_table'
            ],
            'Inline is excluded when set to true' => [
                [
                    'tx_mask_custom_table' => [
                        'tca' => [
                            'tx_mask_column_1' => [],
                            'tx_mask_column_2' => [],
                            'tx_mask_column_3' => [],
                        ]
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ]
                            ]
                        ]
                    ],
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
                        ]
                    ],
                ],
                'tx_mask_column_1',
                'element_2',
                true,
                'tt_content'
            ],
        ];
    }

    /**
     * @dataProvider getFieldTypeDataProvider
     * @test
     * @param $json
     * @param $fieldKey
     * @param $elementKey
     * @param $excludeInline
     * @param $expected
     */
    public function getFieldType($json, $fieldKey, $elementKey, $excludeInline, $expected)
    {
        $storage = $this->createPartialMock(StorageRepository::class, ['load']);
        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getFieldType($fieldKey, $elementKey, $excludeInline));
    }

    public function getFieldsByTypeDataProvider()
    {
        return [
            'Fields by type returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ],
                                'labels' => [
                                    'Column 1',
                                    'Column 2',
                                    'Column 3',
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'group'
                                ]
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
                ],
                'input',
                'tt_content',
                [
                    [
                        'field' => 'tx_mask_column_1',
                        'label' => 'Column 1'
                    ],
                    [
                        'field' => 'tx_mask_column_4',
                        'label' => 'Column 4'
                    ],
                ]
            ],
            'Type is transformed to lowercase' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ],
                                'labels' => [
                                    'Column 1',
                                    'Column 2',
                                    'Column 3',
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ],
                ],
                'Tab',
                'tt_content',
                [
                    [
                        'field' => 'tx_mask_column_2',
                        'label' => 'Column 2'
                    ],
                ]
            ],
            'Empty array is returned if type is empty' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3'
                                ],
                                'labels' => [
                                    'Column 1',
                                    'Column 2',
                                    'Column 3',
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ],
                ],
                'Select',
                'tt_content',
                []
            ],
        ];
    }

    /**
     * @param $json
     * @param $tcaType
     * @param $elementKey
     * @param $expected
     * @dataProvider getFieldsByTypeDataProvider
     * @test
     */
    public function getFieldsByType($json, $tcaType, $elementKey, $expected)
    {
        $storage = $this->createPartialMock(StorageRepository::class, ['load']);

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getFieldsByType($tcaType, $elementKey));
    }
}
