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

namespace MASK\Mask\Tests\UnitDeprecated\Domain\Repository;

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use TYPO3\TestingFramework\Core\BaseTestCase;

class StorageRepositoryTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;

    public function loadInlineFieldsDataProvider(): iterable
    {
        yield 'inline fields loaded' => [
            'json' => [
                'tx_mask_a1' => [
                    'tca' => [
                        'tx_mask_a' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'a',
                            'inlineParent' => 'tx_mask_a1',
                            'order' => 1,
                        ],
                        'tx_mask_b' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'b',
                            'inlineParent' => 'tx_mask_a1',
                            'order' => 2,
                        ],
                    ],
                ],
            ],
            'parent' => 'tx_mask_a1',
            'element' => '',
            'expected' => [
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'a',
                    'inlineParent' => 'tx_mask_a1',
                    'maskKey' => 'tx_mask_a',
                    'fullKey' => 'tx_mask_a',
                    'order' => 1,
                    'type' => 'string',
                ],
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'b',
                    'inlineParent' => 'tx_mask_a1',
                    'maskKey' => 'tx_mask_b',
                    'fullKey' => 'tx_mask_b',
                    'order' => 2,
                    'type' => 'string',
                ],
            ],
        ];

        yield 'inline fields loaded recursivelely' => [
            'json' => [
                'tx_mask_a1' => [
                    'tca' => [
                        'tx_mask_a' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'a',
                            'inlineParent' => 'tx_mask_a1',
                            'order' => 1,
                        ],
                        'tx_mask_b' => [
                            'config' => [
                                'type' => 'inline',
                            ],
                            'key' => 'b',
                            'inlineParent' => 'tx_mask_a1',
                            'order' => 3,
                        ],
                    ],
                ],
                'tx_mask_b' => [
                    'tca' => [
                        'tx_mask_c' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'c',
                            'inlineParent' => 'tx_mask_b',
                            'order' => 2,
                        ],
                    ],
                ],
            ],
            'parent' => 'tx_mask_a1',
            'element' => '',
            'expected' => [
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'a',
                    'inlineParent' => 'tx_mask_a1',
                    'maskKey' => 'tx_mask_a',
                    'fullKey' => 'tx_mask_a',
                    'order' => 1,
                    'type' => 'string',
                ],
                [
                    'config' => [
                        'type' => 'inline',
                    ],
                    'key' => 'b',
                    'inlineParent' => 'tx_mask_a1',
                    'inlineFields' => [
                        [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'c',
                            'inlineParent' => 'tx_mask_b',
                            'maskKey' => 'tx_mask_c',
                            'fullKey' => 'tx_mask_c',
                            'order' => 2,
                            'type' => 'string',
                        ],
                    ],
                    'maskKey' => 'tx_mask_b',
                    'fullKey' => 'tx_mask_b',
                    'order' => 3,
                    'type' => 'inline',
                ],
            ],
        ];

        yield 'inline fields of palette loaded in same table' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_a',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_a' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'a',
                        ],
                        'tx_mask_b' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'b',
                            'inPalette' => '1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_a',
                            ],
                            'order' => [
                                'element1' => 2,
                            ],
                        ],
                        'tx_mask_c' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'c',
                            'inPalette' => '1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_a',
                            ],
                            'order' => [
                                'element1' => 3,
                            ],
                        ],
                    ],
                ],
            ],
            'parent' => 'tx_mask_a',
            'element' => 'element1',
            'expected' => [
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'b',
                    'inPalette' => '1',
                    'inlineParent' => [
                        'element1' => 'tx_mask_a',
                    ],
                    'order' => [
                        'element1' => 2,
                    ],
                    'maskKey' => 'tx_mask_b',
                    'fullKey' => 'tx_mask_b',
                    'type' => 'string',
                ],
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'c',
                    'inPalette' => '1',
                    'inlineParent' => [
                        'element1' => 'tx_mask_a',
                    ],
                    'order' => [
                        'element1' => 3,
                    ],
                    'maskKey' => 'tx_mask_c',
                    'fullKey' => 'tx_mask_c',
                    'type' => 'string',
                ],
            ],
        ];

        yield 'inline fields of palette loaded in inline field' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'columns' => [
                                'tx_mask_repeat',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_repeat' => [
                            'config' => [
                                'type' => 'inline',
                            ],
                            'key' => 'repeat',
                        ],
                    ],
                ],
                'tx_mask_repeat' => [
                    'tca' => [
                        'tx_mask_a' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'a',
                            'inlineParent' => 'tx_mask_repeat',
                            'order' => 1,
                        ],
                        'tx_mask_b' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'b',
                            'inPalette' => '1',
                            'inlineParent' => 'tx_mask_palette',
                            'order' => 3,
                        ],
                        'tx_mask_c' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'c',
                            'inPalette' => '1',
                            'inlineParent' => 'tx_mask_palette',
                            'order' => 4,
                        ],
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                            'inlineParent' => 'tx_mask_repeat',
                            'order' => 2,
                        ],
                    ],
                ],
            ],
            'parent' => 'tx_mask_repeat',
            'element' => 'element1',
            'expected' => [
                [
                    'config' => [
                        'type' => 'input',
                    ],
                    'key' => 'a',
                    'inlineParent' => 'tx_mask_repeat',
                    'maskKey' => 'tx_mask_a',
                    'fullKey' => 'tx_mask_a',
                    'order' => 1,
                    'type' => 'string',
                ],
                [
                    'config' => [
                        'type' => 'palette',
                    ],
                    'key' => 'palette',
                    'inlineParent' => 'tx_mask_repeat',
                    'maskKey' => 'tx_mask_palette',
                    'fullKey' => 'tx_mask_palette',
                    'order' => 2,
                    'type' => 'palette',
                    'inlineFields' => [
                        [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'b',
                            'inPalette' => 1,
                            'inlineParent' => 'tx_mask_palette',
                            'order' => 3,
                            'maskKey' => 'tx_mask_b',
                            'fullKey' => 'tx_mask_b',
                            'type' => 'string',
                        ],
                        [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'c',
                            'inPalette' => 1,
                            'inlineParent' => 'tx_mask_palette',
                            'order' => 4,
                            'maskKey' => 'tx_mask_c',
                            'fullKey' => 'tx_mask_c',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider loadInlineFieldsDataProvider
     * @test
     */
    public function loadInlineFields(array $json, string $parentKey, string $elementKey, array $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);

        self::assertEquals($expected, $storageRepository->loadInlineFields($parentKey, $elementKey));
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
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
                                ],
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_1',
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_2',
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_3',
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_4',
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_5',
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_6',
                            ],
                        ],
                    ],
                ],
                'tx_mask_column_2',
                'tt_content',
                [
                    'element_1' => [
                        'key' => 'element_1',
                        'label' => 'Element 1',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
            ],
            'Multiple elements which uses field are returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
                                ],
                                'sorting' => 0,
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6',
                                ],
                                'sorting' => 0,
                            ],
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_1',
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_2',
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_3',
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_4',
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_5',
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_6',
                            ],
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'tt_content',
                [
                    'element_1' => [
                        'key' => 'element_1',
                        'label' => 'Element 1',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'element_2' => [
                        'key' => 'element_2',
                        'label' => 'Element 2',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_5',
                            'tx_mask_column_6',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
            ],
            'Elements in other table are returned' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
                                ],
                                'sorting' => 0,
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_4',
                                    'tx_mask_column_5',
                                    'tx_mask_column_6',
                                ],
                                'sorting' => 0,
                            ],
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_1',
                            ],
                            'tx_mask_column_2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_2',
                            ],
                            'tx_mask_column_3' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_3',
                            ],
                            'tx_mask_column_4' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_4',
                            ],
                            'tx_mask_column_5' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_5',
                            ],
                            'tx_mask_column_6' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column_6',
                            ],
                        ],
                    ],
                ],
                'tx_mask_column_2',
                'pages',
                [
                    'element_1' => [
                        'key' => 'element_1',
                        'label' => 'Element 1',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_column_1',
                            'tx_mask_column_2',
                            'tx_mask_column_3',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
            ],
            'Fields in palettes are considered' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_column_1',
                                ],
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette_1' => [
                                'showitem' => ['tx_mask_column_1'],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_column_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'column1',
                            ],
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'tt_content',
                [
                    'element_1' => [
                        'key' => 'element_1',
                        'label' => 'Element 1',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_column_1',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'element_2' => [
                        'key' => 'element_2',
                        'label' => 'Element 2',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_palette_1',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
            ],
            'core fields in palettes without config are considered' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_palette_2',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette_1' => [
                                'showitem' => ['header'],
                            ],
                            'tx_mask_palette_2' => [
                                'showitem' => ['header'],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_1',
                            ],
                            'tx_mask_palette_2' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_2',
                            ],
                            'header' => [
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette_1',
                                    'element2' => 'tx_mask_palette_2',
                                ],
                                'key' => 'header',
                            ],
                        ],
                    ],
                ],
                'header',
                'tt_content',
                [
                    'element_1' => [
                        'key' => 'element_1',
                        'label' => 'Element 1',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_palette_1',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'element_2' => [
                        'key' => 'element_2',
                        'label' => 'Element 2',
                        'description' => '',
                        'shortLabel' => '',
                        'icon' => '',
                        'color' => '',
                        'labels' => [],
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_palette_2',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getElementsWhichUseFieldDataProvider
     * @test
     */
    public function getElementsWhichUseField(array $json, string $column, string $table, array $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);

        self::assertEquals($expected, $storageRepository->getElementsWhichUseField($column, $table));
    }

    public function getFormTypeDataProvider(): array
    {
        return [
            'Type String is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::STRING,
            ],
            'Type Integer is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'int',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::INTEGER,
            ],
            'Type Float is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'double2',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::FLOAT,
            ],
            'Type Date is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'date',
                                    'dbType' => 'date',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::DATE,
            ],
            'Type Datetime is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'datetime',
                                    'dbType' => 'datetime',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::DATETIME,
            ],
            'Type Link is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'renderType' => 'inputLink',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::LINK,
            ],
            'Type Text is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'text',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::TEXT,
            ],
            'Type Richtext is returned if in inline' => [
                [],
                [
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'text',
                                ],
                                'rte' => '1',
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tx_mask_inline',
                FieldType::RICHTEXT,
            ],
            'Type Richtext is returned, if enableRichtext is set' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'text',
                                    'enableRichtext' => '1',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::RICHTEXT,
            ],
            'Type Check is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'check',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::CHECK,
            ],
            'Type Radio is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'radio',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::RADIO,
            ],
            'Type Select is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'select',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::SELECT,
            ],
            'Type Inline is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::INLINE,
            ],
            'Type File is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'sys_file_reference',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::FILE,
            ],
            'Type File by option is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'options' => 'file',
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::FILE,
            ],
            'Type Content is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'tt_content',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::CONTENT,
            ],
            'Type Tab is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'tab',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::TAB,
            ],
            'Type Palette is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::PALETTE,
            ],
            'Field is found when in palette' => [
                [],
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_palette2',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette',
                            ],
                            'tx_mask_palette2' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette2',
                            ],
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'int',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
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
                'tx_mask_field1',
                'tt_content',
                FieldType::INTEGER,
            ],
            'Type Group is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'group',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::GROUP,
            ],
            'Type Timestamp is returned' => [
                [],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'evaĺ' => 'date,int',
                                    'renderType' => 'inputDateTime',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_field1',
                'tt_content',
                FieldType::TIMESTAMP,
            ],
            'Type from global tca is returned' => [
                [
                    'tt_content' => [
                        'columns' => [
                            'date' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' =>  'date',
                                    'dbType' => 'date',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'tt_content' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'group',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'date',
                'tt_content',
                FieldType::DATE,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFormTypeDataProvider
     */
    public function getFormType(array $tca, array $json, string $fieldKey, string $table, string $expected): void
    {
        $GLOBALS['TCA'] = $tca;

        $storageRepository = $this->createStorageRepository($json);

        self::assertEquals($expected, $storageRepository->getFormType($fieldKey, '', $table));
    }

    public function findFirstNonEmptyLabelDataProvider(): array
    {
        return [
            'First found field label returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'field1',
                                    'field2',
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2',
                                ],
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'field1',
                                    'field3',
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'field1',
                'Field 1',
            ],
            'First found field label in palette returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'pallette1',
                                    'field2',
                                ],
                                'labels' => [
                                    'Palette 1',
                                    'Field 2',
                                ],
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'field1',
                                    'field3',
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3',
                                ],
                            ],
                        ],
                        'tca' => [
                            'field1' => [
                                'label' => [
                                    'element1' => 'Field 1',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'field1',
                'Field 1',
            ],
            'Empty columns ignored' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'key' => 'element1',
                                'label' => 'Element 1',
                            ],
                            'element2' => [
                                'key' => 'element2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'field1',
                                    'field3',
                                ],
                                'labels' => [
                                    'Field 1-1',
                                    'Field 3',
                                ],
                            ],
                        ],
                        'tca' => [
                            'field1' => [
                                'label' => [
                                    'element2' => 'Field 1',
                                ],
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'field1',
                'Field 1-1',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider findFirstNonEmptyLabelDataProvider
     */
    public function findFirstNonEmptyLabel(array $json, string $table, string $key, string $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);

        self::assertSame($expected, $storageRepository->findFirstNonEmptyLabel($table, $key));
    }

    public function loadElementDataProvider(): iterable
    {
        yield 'Element with fields returned' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'color' => '#000000',
                            'icon' => 'fa-icon',
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'shortLabel' => 'My short Label',
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
                            'descriptions' => [
                                '',
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
                            'name' => 'string',
                            'description' => 'Field 1 Description',
                        ],
                        'tx_mask_field2' => [
                            'config' => [
                                'eval' => 'int',
                                'type' => 'input',
                            ],
                            'key' => 'field2',
                            'name' => 'integer',
                            'description' => 'Field 2 Description',
                        ],
                        'tx_mask_field3' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'renderType' => 'inputLink',
                            'key' => 'field3',
                            'name' => 'link',
                            'description' => 'Field 3 Description',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'element' => 'element1',
            'expected' => [
                'color' => '#000000',
                'icon' => 'fa-icon',
                'key' => 'element1',
                'label' => 'Element 1',
                'shortLabel' => 'My short Label',
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
                'descriptions' => [
                    '',
                    '',
                    '',
                ],
                'tca' => [
                    'tx_mask_field1' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field1',
                        'fullKey' => 'tx_mask_field1',
                        'type' => 'string',
                        'description' => 'Field 1 Description',
                    ],
                    'tx_mask_field2' => [
                        'config' => [
                            'eval' => 'int',
                            'type' => 'input',
                        ],
                        'key' => 'field2',
                        'fullKey' => 'tx_mask_field2',
                        'type' => 'integer',
                        'description' => 'Field 2 Description',
                    ],
                    'tx_mask_field3' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'renderType' => 'inputLink',
                        'key' => 'field3',
                        'fullKey' => 'tx_mask_field3',
                        'type' => 'link',
                        'description' => 'Field 3 Description',
                    ],
                ],
                'sorting' => 0,
                'colorOverlay' => '',
                'iconOverlay' => '',
            ],
        ];

        yield 'Element with no field returns only element' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'color' => '#000000',
                            'icon' => 'fa-icon',
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'description' => 'Element 1 Description',
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'name' => 'string',
                            'description' => 'Field 1 Description',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'element' => 'element1',
            'expected' => [
                'color' => '#000000',
                'icon' => 'fa-icon',
                'key' => 'element1',
                'label' => 'Element 1',
                'shortLabel' => '',
                'description' => 'Element 1 Description',
                'columns' => [],
                'labels' => [],
                'descriptions' => [],
                'tca' => [],
                'sorting' => 0,
                'colorOverlay' => '',
                'iconOverlay' => '',
            ],
        ];

        yield 'Non existing element returns empty array' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'color' => '#000000',
                            'icon' => 'fa-icon',
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'description' => 'Element 1 Description',
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'name' => 'string',
                            'description' => 'Field 1 Description',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'element' => 'element2',
            'expected' => [],
            'sorting' => 0,
            'colorOverlay' => '',
            'iconOverlay' => '',
        ];

        yield 'Tables other than tt_content or pages return empty array' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'color' => '#000000',
                            'icon' => 'fa-icon',
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'description' => 'Element 1 Description',
                        ],
                    ],
                    'tca' => [
                        'tx_mask_repeating' => [
                            'config' => [
                                'type' => 'inline',
                            ],
                            'key' => 'repeating',
                            'name' => 'inline',
                            'description' => 'Field Inline Description',
                        ],
                    ],
                ],
                'tx_mask_repeating' => [
                    'tca' => [
                        'tx_mask_field1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field1',
                            'name' => 'string',
                            'description' => 'Field 1 Description',
                        ],
                    ],
                ],
            ],
            'table' => 'tx_mask_repeating',
            'element' => 'element2',
            'expected' => [],
        ];
    }

    /**
     * @test
     * @dataProvider loadElementDataProvider
     */
    public function loadElement(array $json, string $table, string $element, array $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);

        self::assertEquals($expected, $storageRepository->loadElement($table, $element));
    }
}
