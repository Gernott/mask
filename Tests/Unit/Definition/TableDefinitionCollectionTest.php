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

namespace MASK\Mask\Tests\Unit\Definition;

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Definition\TableDefinitionCollection;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TableDefinitionCollectionTest extends BaseTestCase
{
    public function loadInlineFieldsDataProvider(): array
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
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'a',
                                    'b',
                                    'c'
                                ]
                            ]
                        ],
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
     */
    public function loadInlineFields(array $json, string $parentKey, string $elementKey, array $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertEquals($expected, $tableDefinitionCollection->loadInlineFields($parentKey, $elementKey));
    }

    public function getElementsWhichUseFieldDataProvider(): array
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
     */
    public function getElementsWhichUseField(array $json, string $column, string $table, array $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertSame($expected, $tableDefinitionCollection->getElementsWhichUseField($column, $table));
    }

    public function getFormTypeDataProvider(): array
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
            'Core field bodytext returned as richtext' => [
                [],
                [],
                'bodytext',
                '',
                'tt_content',
                FieldType::RICHTEXT
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFormTypeDataProvider
     */
    public function getFormType(array $tca, array $json, string $fieldKey, string $elementKey, string $table, string $expected): void
    {
        $GLOBALS['TCA'] = $tca;

        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertEquals($expected, $tableDefinitionCollection->getFormType($fieldKey, $elementKey, $table));
    }

    public function findFirstNonEmptyLabelDataProvider(): array
    {
        return [
            'First found field label returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'field1',
                                    'field2'
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2'
                                ]
                            ],
                            'element2' => [
                                'columns' => [
                                    'field1',
                                    'field3'
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3'
                                ]
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'field1',
                'Field 1'
            ],
            'First found field label in palette returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'columns' => [
                                    'pallette1',
                                    'field2'
                                ],
                                'labels' => [
                                    'Palette 1',
                                    'Field 2'
                                ]
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'columns' => [
                                    'field1',
                                    'field3'
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3'
                                ]
                            ]
                        ],
                        'tca' => [
                            'field1' => [
                                'label' => [
                                    'element1' => 'Field 1'
                                ]
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'field1',
                'Field 1'
            ],
            'Empty columns ignored' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'columns' => [
                                    'field1',
                                    'field3'
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3'
                                ]
                            ]
                        ],
                        'tca' => [
                            'field1' => [
                                'label' => [
                                    'element2' => 'Field 1'
                                ]
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'field1',
                'Field 1-1'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider findFirstNonEmptyLabelDataProvider
     */
    public function findFirstNonEmptyLabel(array $json, string $table, string $key, string $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertSame($expected, $tableDefinitionCollection->findFirstNonEmptyLabel($table, $key));
    }

    public function loadElementDataProvider(): array
    {
        return [
            'Element with fields returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                    'tx_mask_field2',
                                    'tx_mask_field3',
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2',
                                    'Field 3',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description'
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'eval' => 'int',
                                    'type' => 'input'
                                ],
                                'key' => 'field2',
                                'name' => 'integer',
                                'description' => 'Field 2 Description'
                            ],
                            'tx_mask_field3' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'renderType' => 'inputLink',
                                'key' => 'field3',
                                'name' => 'link',
                                'description' => 'Field 3 Description'
                            ]
                        ]
                    ]
                ],
                'tt_content',
                'element1',
                [
                    'color' => '#000000',
                    'icon' => 'fa-icon',
                    'key' => 'element1',
                    'label' => 'Element 1',
                    'description' => 'Element 1 Description',
                    'columns' => [
                        'tx_mask_field1',
                        'tx_mask_field2',
                        'tx_mask_field3',
                    ],
                    'labels' => [
                        'Field 1',
                        'Field 2',
                        'Field 3',
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input'
                            ],
                            'key' => 'field1',
                            'name' => 'string',
                            'description' => 'Field 1 Description'
                        ],
                        'tx_mask_field2' => [
                            'config' => [
                                'eval' => 'int',
                                'type' => 'input'
                            ],
                            'key' => 'field2',
                            'name' => 'integer',
                            'description' => 'Field 2 Description'
                        ],
                        'tx_mask_field3' => [
                            'config' => [
                                'type' => 'input'
                            ],
                            'renderType' => 'inputLink',
                            'key' => 'field3',
                            'name' => 'link',
                            'description' => 'Field 3 Description'
                        ]
                    ]
                ]
            ],
            'Element with no field returns only element' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description'
                            ],
                        ]
                    ]
                ],
                'tt_content',
                'element1',
                [
                    'color' => '#000000',
                    'icon' => 'fa-icon',
                    'key' => 'element1',
                    'label' => 'Element 1',
                    'description' => 'Element 1 Description',
                ]
            ],
            'Non existing element returns empty array' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                            ]
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description'
                            ],
                        ]
                    ]
                ],
                'tt_content',
                'element2',
                []
            ],
            'Tables other than tt_content or pages return empty array' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                            ]
                        ],
                        'tca' => [
                            'tx_mask_repeating' => [
                                'config' => [
                                    'type' => 'inline'
                                ],
                                'key' => 'repeating',
                                'name' => 'inline',
                                'description' => 'Field Inline Description'
                            ],
                        ]
                    ],
                    'tx_mask_repeating' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description'
                            ],
                        ]
                    ]
                ],
                'tx_mask_repeating',
                'element2',
                []
            ]
        ];
    }

    /**
     * @test
     * @dataProvider loadElementDataProvider
     */
    public function loadElement(array $json, string $table, string $element, array $expected): void
    {
        $tableDefinitonCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertSame($expected, $tableDefinitonCollection->loadElement($table, $element));
    }

    public function getLabelDataProvider(): array
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
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1'
                                ],
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
            ],
            'Field in inline returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Inline 1',
                                ],
                                'columns' => [
                                    'tx_mask_inline',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'config' => [
                                    'type' => 'inline'
                                ],
                                'key' => 'inline'
                            ],
                        ]
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'inlineParent' => 'tx_mask_inline',
                                'label' => 'Header 1'
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_field1',
                'tx_mask_inline',
                'Header 1'
            ],
            'Field which is shared and is in palette in the other element' => [
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
                            ],
                            'element_2' => [
                                'labels' => [
                                    'Header 1-1'
                                ],
                                'columns' => [
                                    'tx_mask_header'
                                ]
                            ]
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header']
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'tx_mask_header' => [
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1'
                                ]
                            ]
                        ]
                    ],
                ],
                'element_2',
                'tx_mask_header',
                'tt_content',
                'Header 1-1'
            ],
            'Field in palette which is shared and is not in palette in the other element' => [
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
                            ],
                            'element_2' => [
                                'labels' => [
                                    'Header 1-1'
                                ],
                                'columns' => [
                                    'tx_mask_header'
                                ]
                            ]
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header']
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'tx_mask_header' => [
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1'
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1'
                                ]
                            ]
                        ]
                    ],
                ],
                'element_1',
                'tx_mask_header',
                'tt_content',
                'Header 1'
            ],
            'Field in palette and palette is in inline' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Inline 1',
                                ],
                                'columns' => [
                                    'tx_mask_inline1',
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_inline1' => [
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_inline1' => [
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header']
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette_1'
                            ],
                            'tx_mask_header' => [
                                'inlineParent' => 'tx_mask_palette_1',
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_header',
                'tx_mask_inline1',
                'Header 1'
            ],
        ];
    }

    /**
     * @dataProvider getLabelDataProvider
     * @test
     */
    public function getLabel(array $json, string $elementKey, string $fieldKey, string $type, string $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertSame($expected, $tableDefinitionCollection->getLabel($elementKey, $fieldKey, $type));
    }

    public function getFieldTypeDataProvider(): array
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
     */
    public function getFieldType(array $json, string $fieldKey, string $elementKey, bool $excludeInline, string $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);

        self::assertSame($expected, $tableDefinitionCollection->getFieldType($fieldKey, $elementKey, $excludeInline));
    }
}
