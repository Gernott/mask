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

namespace MASK\Mask\Tests\UnitDeprecated\Helper;

use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use TYPO3\TestingFramework\Core\BaseTestCase;

class FieldHelperTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;

    public function getLabelDataProvider(): array
    {
        return [
            'Correct label is returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3',
                                ],
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
                ],
                'element_1',
                'tx_mask_column_2',
                'tt_content',
                'Label 2',
            ],
            'Empty string if element does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3',
                                ],
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
                ],
                'element_4',
                'tx_mask_column_2',
                'tt_content',
                '',
            ],
            'Empty string if field does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3',
                                ],
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
                ],
                'element_1',
                'tx_mask_column_4',
                'tt_content',
                '',
            ],
            'Core field returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Header 1',
                                    'Label 2',
                                    'Label 3',
                                ],
                                'columns' => [
                                    'header',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
                                ],
                            ],
                        ],
                        'tca' => [
                            'header' => [
                                'key' => 'header',
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
                        ],
                    ],
                ],
                'element_1',
                'header',
                'tt_content',
                'Header 1',
            ],
            'Core field in palette returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Palette 1',
                                ],
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
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
                            'header' => [
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1',
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1',
                                ],
                                'key' => 'header',
                            ],
                        ],
                    ],
                ],
                'element_1',
                'header',
                'tt_content',
                'Header 1',
            ],
            'Field in inline returns correct label' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Inline 1',
                                ],
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'inline',
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inlineParent' => 'tx_mask_inline',
                                'label' => 'Header 1',
                                'key' => 'field1',
                            ],
                        ],
                    ],
                ],
                'element_1',
                'tx_mask_field1',
                'tx_mask_inline',
                'Header 1',
            ],
            'Field which is shared and is in palette in the other element' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Palette 1',
                                ],
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'labels' => [
                                    'Header 1-1',
                                ],
                                'columns' => [
                                    'tx_mask_header',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header'],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_1',
                            ],
                            'tx_mask_header' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1',
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1',
                                ],
                                'key' => 'header',
                            ],
                        ],
                    ],
                ],
                'element_2',
                'tx_mask_header',
                'tt_content',
                'Header 1-1',
            ],
            'Field in palette which is shared and is not in palette in the other element' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Palette 1',
                                ],
                                'columns' => [
                                    'tx_mask_palette_1',
                                ],
                            ],
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'labels' => [
                                    'Header 1-1',
                                ],
                                'columns' => [
                                    'tx_mask_header',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header'],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_1',
                            ],
                            'tx_mask_header' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette_1',
                                ],
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1',
                                ],
                                'key' => 'header',
                            ],
                        ],
                    ],
                ],
                'element_1',
                'tx_mask_header',
                'tt_content',
                'Header 1',
            ],
            'Field in palette and palette is in inline' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Inline 1',
                                ],
                                'columns' => [
                                    'tx_mask_inline1',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline1' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'inline1',
                            ],
                        ],
                    ],
                    'tx_mask_inline1' => [
                        'palettes' => [
                            'palette_1' => [
                                'label' => 'Palette 1',
                                'showitem' => ['tx_mask_header'],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette_1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette_1',
                            ],
                            'tx_mask_header' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'inlineParent' => 'tx_mask_palette_1',
                                'inPalette' => '1',
                                'label' => [
                                    'element_1' => 'Header 1',
                                ],
                                'key' => 'header',
                            ],
                        ],
                    ],
                ],
                'element_1',
                'tx_mask_header',
                'tx_mask_inline1',
                'Header 1',
            ],
        ];
    }

    /**
     * @dataProvider getLabelDataProvider
     * @test
     */
    public function getLabel(array $json, string $elementKey, string $fieldKey, string $type, string $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);
        $fieldHelper = new FieldHelper($storageRepository);

        self::assertSame($expected, $fieldHelper->getLabel($elementKey, $fieldKey, $type));
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
                'tx_mask_column_1',
                'element_1',
                false,
                'tt_content',
            ],
            'Correct table is returned for field 2' => [
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
                'tx_mask_column_4',
                'element_2',
                false,
                'pages',
            ],
            'First table is returned for ambiguous field' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
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
                        ],
                    ],
                ],
                'tx_mask_column_1',
                '',
                false,
                'tt_content',
            ],
            'First table is not returned if elementKey is not empty' => [
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
                        ],
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'element_2',
                false,
                'tt_content',
            ],
            'Correct table is returned for field and element' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
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
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'element_1',
                false,
                'pages',
            ],
            'Inline is not excluded by default' => [
                [
                    'tx_mask_custom_table' => [
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
                        ],
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
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
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'element_1',
                false,
                'tx_mask_custom_table',
            ],
            'Inline is excluded when set to true' => [
                [
                    'tx_mask_custom_table' => [
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
                        ],
                    ],
                    'tt_content' => [
                        'elements' => [
                            'element_2' => [
                                'key' => 'element_2',
                                'label' => 'Element 2',
                                'columns' => [
                                    'tx_mask_column_1',
                                    'tx_mask_column_2',
                                    'tx_mask_column_3',
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
                        ],
                    ],
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
                        ],
                    ],
                ],
                'tx_mask_column_1',
                'element_2',
                true,
                'tt_content',
            ],
        ];
    }

    /**
     * @dataProvider getFieldTypeDataProvider
     * @test
     */
    public function getFieldType(array $json, string $fieldKey, string $elementKey, bool $excludeInline, string $expected): void
    {
        $storageRepository = $this->createStorageRepository($json);
        $fieldHelper = new FieldHelper($storageRepository);

        self::assertSame($expected, $fieldHelper->getFieldType($fieldKey, $elementKey, $excludeInline));
    }
}
