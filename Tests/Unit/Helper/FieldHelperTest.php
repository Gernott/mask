<?php

namespace MASK\Mask\Test\Helper;

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'column_2',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_4',
                'column_2',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'column_4',
                'tt_content',
                ''
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
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getLabel($elementKey, $fieldKey, $type));
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'column_2',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'column_1',
                            'column_2',
                            'column_3'
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_1',
                                    'column_5',
                                    'column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'column_1',
                'tt_content',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'column_1',
                            'column_2',
                            'column_3'
                        ]
                    ],
                    [
                        'key' => 'element_2',
                        'columns' => [
                            'column_1',
                            'column_5',
                            'column_6'
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                            'element_2' => [
                                'columns' => [
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'column_2',
                'pages',
                [
                    [
                        'key' => 'element_1',
                        'columns' => [
                            'column_1',
                            'column_2',
                            'column_3'
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
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);

        self::assertSame($expected, $fieldHelper->getElementsWhichUseField($column, $table));
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'column_1',
                '',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ]
                            ]
                        ]
                    ]
                ],
                'column_4',
                '',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                        ]
                    ],
                ],
                'column_1',
                '',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                        ]
                    ],
                ],
                'column_1',
                'element_1',
                false,
                'pages'
            ],
            'Inline is not excluded by default' => [
                [
                    'tx_mask_custom_table' => [
                        'elements' => [
                            'element_3' => [
                                'key' => 'element_3',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                        ]
                    ],
                ],
                'column_1',
                '',
                false,
                'tx_mask_custom_table'
            ],
            'Inline is excluded when set to true and pages is first' => [
                [
                    'tx_mask_custom_table' => [
                        'elements' => [
                            'element_3' => [
                                'key' => 'element_3',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ],
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ],
                        ]
                    ],
                ],
                'column_1',
                '',
                true,
                'pages'
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
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

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
                                    'column_1',
                                    'column_2',
                                    'column_3'
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
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_2' => [
                                'config' => [
                                    'type' => 'group'
                                ]
                            ],
                            'column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'column_6' => [
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
                        'field' => 'column_1',
                        'label' => 'Column 1'
                    ],
                    [
                        'field' => 'column_4',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
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
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_2' => [
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                            'column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'column_6' => [
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
                        'field' => 'column_2',
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
                                    'column_1',
                                    'column_2',
                                    'column_3'
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
                                    'column_4',
                                    'column_5',
                                    'column_6'
                                ],
                                'labels' => [
                                    'Column 4',
                                    'Column 5',
                                    'Column 6',
                                ]
                            ]
                        ],
                        'tca' => [
                            'column_1' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_2' => [
                                'config' => [
                                    'type' => 'tab'
                                ]
                            ],
                            'column_3' => [
                                'config' => [
                                    'type' => 'check'
                                ]
                            ],
                            'column_4' => [
                                'config' => [
                                    'type' => 'input'
                                ]
                            ],
                            'column_5' => [
                                'config' => [
                                    'type' => 'select'
                                ]
                            ],
                            'column_6' => [
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
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getFieldsByType($tcaType, $elementKey));
    }
}
