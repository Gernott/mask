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

namespace MASK\Mask\Tests\Unit\Domain\Repository;

use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class StorageRepositoryTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;

    public function addDataProvider(): iterable
    {
        yield 'fields are added' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'shortLabel' => '',
                'description' => '',
                'icon' => '',
                'color' => '#000000',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_field1',
                    'label' => 'Field 1',
                    'description' => 'Field 1 description',
                    'name' => 'string',
                    'tca' => [],
                ],
                [
                    'key' => 'tx_mask_field2',
                    'label' => 'Field 2',
                    'description' => '',
                    'name' => 'string',
                    'tca' => [],
                ],
                [
                    'key' => 'header',
                    'label' => 'Header',
                    'description' => '',
                    'name' => 'string',
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [
                                'tx_mask_field1',
                                'tx_mask_field2',
                                'header',
                            ],
                            'labels' => [
                                'Field 1',
                                'Field 2',
                                'Header',
                            ],
                            'descriptions' => [
                                'Field 1 description',
                                '',
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'sql' => [
                        'tx_mask_field1' => [
                            'tt_content' => [
                                'tx_mask_field1' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                        'tx_mask_field2' => [
                            'tt_content' => [
                                'tx_mask_field2' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                            'type' => 'string',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'tx_mask_field2' => [
                            'key' => 'field2',
                            'fullKey' => 'tx_mask_field2',
                            'type' => 'string',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                        'header' => [
                            'key' => 'header',
                            'fullKey' => 'header',
                            'type' => 'string',
                            'coreField' => 1,
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'existing typo3 fields do not generate tca nor sql' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'shortLabel' => '',
                'description' => '',
                'icon' => '',
                'color' => '#000000',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'header',
                    'label' => 'Header 1',
                    'description' => '',
                    'name' => 'string',
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [
                                'header',
                            ],
                            'labels' => [
                                'Header 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'tca' => [
                        'header' => [
                            'key' => 'header',
                            'fullKey' => 'header',
                            'type' => 'string',
                            'coreField' => 1,
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'inline fields are added as new table' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'shortLabel' => '',
                'description' => '',
                'icon' => '',
                'color' => '#000000',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_inline_field',
                    'label' => 'Inline Field',
                    'description' => 'Inline Field description',
                    'name' => 'inline',
                    'tca' => [],
                    'fields' => [
                        [
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'description' => 'Field 1 description',
                            'name' => 'string',
                            'tca' => [],
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [
                                'tx_mask_inline_field',
                            ],
                            'labels' => [
                                'Inline Field',
                            ],
                            'descriptions' => [
                                'Inline Field description',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'sql' => [
                        'tx_mask_inline_field' => [
                            'tt_content' => [
                                'tx_mask_inline_field' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_inline_field' => [
                            'key' => 'inline_field',
                            'fullKey' => 'tx_mask_inline_field',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => '--inlinetable--',
                                'foreign_field' => 'parentid',
                                'foreign_table_field' => 'parenttable',
                                'foreign_sortby' => 'sorting',
                                'appearance' => [
                                    'enabledControls' => [
                                        'dragdrop' => 1,
                                    ],
                                ],
                            ],
                            'type' => 'inline',
                        ],
                    ],
                ],
                'tx_mask_inline_field' => [
                    'sql' => [
                        'tx_mask_field1' => [
                            'tx_mask_inline_field' => [
                                'tx_mask_field1' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_field1' => [
                            'key' => 'field1',
                            'fullKey' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'inlineParent' => 'tx_mask_inline_field',
                            'order' => 1,
                            'description' => 'Field 1 description',
                            'type' => 'string',
                            'config' => [
                                'type' => 'input',
                            ],
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'Timestamps range converted to integer' => [
            'json' => [],
            'element' => [
                'key' => 'element1',
                'label' => 'Element 1',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_timestamp',
                    'label' => 'Timestamp',
                    'description' => '',
                    'name' => 'timestamp',
                    'tca' => [
                        'config.eval.date' => 1,
                        'config.range.lower' => '00:00 01.01.2021',
                        'config.range.upper' => '00:00 15.01.2021',
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'tx_mask_timestamp',
                            ],
                            'labels' => [
                                'Timestamp',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'sql' => [
                        'tx_mask_timestamp' => [
                            'tt_content' => [
                                'tx_mask_timestamp' => 'int(10) unsigned DEFAULT \'0\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_timestamp' => [
                            'key' => 'timestamp',
                            'fullKey' => 'tx_mask_timestamp',
                            'type' => 'timestamp',
                            'config' => [
                                'type' => 'input',
                                'renderType' => 'inputDateTime',
                                'eval' => 'date,int',
                                'range' => [
                                    'lower' => 1609459200,
                                    'upper' => 1610668800,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'inline fields with no children are not added as new table' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_inline_field',
                    'name' => 'inline',
                    'label' => 'Inline Field',
                    'description' => 'Inline Field description',
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'tx_mask_inline_field',
                            ],
                            'labels' => [
                                'Inline Field',
                            ],
                            'descriptions' => [
                                'Inline Field description',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'sql' => [
                        'tx_mask_inline_field' => [
                            'tt_content' => [
                                'tx_mask_inline_field' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_inline_field' => [
                            'key' => 'inline_field',
                            'fullKey' => 'tx_mask_inline_field',
                            'type' => 'inline',
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => '--inlinetable--',
                                'foreign_field' => 'parentid',
                                'foreign_table_field' => 'parenttable',
                                'foreign_sortby' => 'sorting',
                                'appearance' => [
                                    'enabledControls' => [
                                        'dragdrop' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'palettes are added' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_palette',
                    'label' => 'My Palette',
                    'name' => 'palette',
                    'fields' => [
                        [
                            'key' => 'header',
                            'label' => 'Header',
                            'name' => 'string',
                            'description' => '',
                        ],
                        [
                            'key' => 'tx_mask_field',
                            'label' => 'Field',
                            'name' => 'string',
                            'description' => '',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
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
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette' => [
                            'label' => 'My Palette',
                            'description' => '',
                            'showitem' => ['header', 'tx_mask_field'],
                        ],
                    ],
                    'sql' => [
                        'tx_mask_field' => [
                            'tt_content' => [
                                'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                            'fullKey' => 'tx_mask_palette',
                            'type' => 'palette',
                        ],
                        'tx_mask_field' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'type' => 'string',
                            'key' => 'field',
                            'fullKey' => 'tx_mask_field',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette',
                            ],
                            'inPalette' => 1,
                            'label' => [
                                'element1' => 'Field',
                            ],
                            'description' => [
                                'element1' => '',
                            ],
                            'order' => [
                                'element1' => 2,
                            ],
                        ],
                        'header' => [
                            'key' => 'header',
                            'fullKey' => 'header',
                            'type' => 'string',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette',
                            ],
                            'inPalette' => 1,
                            'label' => [
                                'element1' => 'Header',
                            ],
                            'description' => [
                                'element1' => '',
                            ],
                            'order' => [
                                'element1' => 1,
                            ],
                            'coreField' => 1,
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'empty palettes are added' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_palette',
                    'label' => 'My Palette',
                    'name' => 'palette',
                    'fields' => [],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
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
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette' => [
                            'label' => 'My Palette',
                            'description' => '',
                            'showitem' => [],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                            'fullKey' => 'tx_mask_palette',
                            'type' => 'palette',
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'Fields in palette of inline field point directly to inline table' => [
            'json' => [],
            'element' => [
                'label' => 'Element 1',
                'key' => 'element1',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_inline',
                    'label' => 'Inline Field',
                    'description' => '',
                    'name' => 'inline',
                    'fields' => [
                        [
                            'key' => 'tx_mask_palette',
                            'label' => 'My Palette',
                            'description' => '',
                            'name' => 'palette',
                            'fields' => [
                                [
                                    'key' => 'tx_mask_field',
                                    'label' => 'Field',
                                    'description' => '',
                                    'name' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'columns' => [
                                'tx_mask_inline',
                            ],
                            'labels' => [
                                'Inline Field',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'sql' => [
                        'tx_mask_inline' => [
                            'tt_content' => [
                                'tx_mask_inline' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_inline' => [
                            'config' => [
                                'type' => 'inline',
                                'foreign_table' => '--inlinetable--',
                                'foreign_field' => 'parentid',
                                'foreign_table_field' => 'parenttable',
                                'foreign_sortby' => 'sorting',
                                'appearance' => [
                                    'enabledControls' => [
                                        'dragdrop' => 1,
                                    ],
                                ],
                            ],
                            'key' => 'inline',
                            'fullKey' => 'tx_mask_inline',
                            'type' => 'inline',
                        ],
                    ],
                ],
                'tx_mask_inline' => [
                    'palettes' => [
                        'tx_mask_palette' => [
                            'label' => 'My Palette',
                            'description' => '',
                            'showitem' => ['tx_mask_field'],
                        ],
                    ],
                    'sql' => [
                        'tx_mask_field' => [
                            'tx_mask_inline' => [
                                'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette',
                            'fullKey' => 'tx_mask_palette',
                            'description' => '',
                            'type' => 'palette',
                            'inlineParent' => 'tx_mask_inline',
                            'label' => 'My Palette',
                            'order' => 1,
                        ],
                        'tx_mask_field' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'type' => 'string',
                            'key' => 'field',
                            'fullKey' => 'tx_mask_field',
                            'description' => '',
                            'inlineParent' => 'tx_mask_palette',
                            'inPalette' => 1,
                            'label' => 'Field',
                            'order' => 1,
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'existing custom field in another element added freshly to palette' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'columns' => [
                                'tx_mask_palette_1',
                            ],
                            'labels' => [
                                'Palette 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'key' => 'element1',
                            'label' => 'Element 1',
                            'sorting' => '0',
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette_1' => [
                            'label' => 'Palette 1',
                            'description' => '',
                            'showitem' => ['tx_mask_field_1', 'header'],
                        ],
                    ],
                    'sql' => [
                        'tx_mask_field_1' => [
                            'tt_content' => [
                                'tx_mask_field_1' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette_1' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette_1',
                            'type' => 'palette',
                        ],
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field_1',
                            'inPalette' => '1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette_1',
                            ],
                            'label' => [
                                'element1' => 'Field 1 in Element 1',
                            ],
                            'description' => [
                                'element1' => '',
                            ],
                            'order' => [
                                'element1' => 1,
                            ],
                            'type' => 'string',
                        ],
                        'header' => [
                            'key' => 'header',
                            'inPalette' => '1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette_1',
                            ],
                            'label' => [
                                'element1' => 'Header in Element 1',
                            ],
                            'description' => [
                                'element1' => '',
                            ],
                            'order' => [
                                'element1' => 2,
                            ],
                            'coreField' => '1',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'element' => [
                'label' => 'Element 2',
                'key' => 'element2',
                'sorting' => '1',
            ],
            'fields' => [
                [
                    'key' => 'tx_mask_palette_2',
                    'label' => 'Palette 2',
                    'name' => 'palette',
                    'fields' => [
                        [
                            'key' => 'tx_mask_field_1',
                            'label' => 'Field 1 in Element 2',
                            'description' => '',
                            'name' => 'string',
                        ],
                        [
                            'key' => 'header',
                            'label' => 'Header in Element 2',
                            'description' => '',
                            'name' => 'string',
                        ],
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '',
                            'columns' => [
                                'tx_mask_palette_1',
                            ],
                            'labels' => [
                                'Palette 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => 0,
                            'colorOverlay' => '',
                            'iconOverlay' => '',
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'columns' => [
                                'tx_mask_palette_2',
                            ],
                            'labels' => [
                                'Palette 2',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'sorting' => '1',
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette_1' => [
                            'label' => 'Palette 1',
                            'description' => '',
                            'showitem' => ['tx_mask_field_1', 'header'],
                        ],
                        'tx_mask_palette_2' => [
                            'label' => 'Palette 2',
                            'description' => '',
                            'showitem' => ['tx_mask_field_1', 'header'],
                        ],
                    ],
                    'sql' => [
                        'tx_mask_field_1' => [
                            'tt_content' => [
                                'tx_mask_field_1' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette_1' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette_1',
                            'fullKey' => 'tx_mask_palette_1',
                            'type' => 'palette',
                        ],
                        'tx_mask_palette_2' => [
                            'config' => [
                                'type' => 'palette',
                            ],
                            'key' => 'palette_2',
                            'fullKey' => 'tx_mask_palette_2',
                            'type' => 'palette',
                        ],
                        'tx_mask_field_1' => [
                            'config' => [
                                'type' => 'input',
                            ],
                            'key' => 'field_1',
                            'fullKey' => 'tx_mask_field_1',
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette_1',
                                'element2' => 'tx_mask_palette_2',
                            ],
                            'inPalette' => 1,
                            'label' => [
                                'element1' => 'Field 1 in Element 1',
                                'element2' => 'Field 1 in Element 2',
                            ],
                            'description' => [
                                'element1' => '',
                                'element2' => '',
                            ],
                            'order' => [
                                'element1' => 1,
                                'element2' => 1,
                            ],
                            'type' => 'string',
                        ],
                        'header' => [
                            'key' => 'header',
                            'fullKey' => 'header',
                            'inPalette' => 1,
                            'inlineParent' => [
                                'element1' => 'tx_mask_palette_1',
                                'element2' => 'tx_mask_palette_2',
                            ],
                            'label' => [
                                'element1' => 'Header in Element 1',
                                'element2' => 'Header in Element 2',
                            ],
                            'description' => [
                                'element1' => '',
                                'element2' => '',
                            ],
                            'order' => [
                                'element1' => 2,
                                'element2' => 2,
                            ],
                            'coreField' => 1,
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];

        yield 'New content element added as last sorting' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '0',
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '2',
                        ],
                    ],
                ],
            ],
            'element' => [
                'label' => 'Element 3',
                'key' => 'element3',
                'shortLabel' => '',
                'description' => '',
                'icon' => '',
                'color' => '#000000',
            ],
            'fields' => [],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [],
                            'labels' => [],
                            'descriptions' => [],
                            'sorting' => '0',
                            'colorOverlay' => '',
                            'iconOverlay' => '',
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [],
                            'labels' => [],
                            'descriptions' => [],
                            'sorting' => '2',
                            'colorOverlay' => '',
                            'iconOverlay' => '',
                        ],
                        'element3' => [
                            'label' => 'Element 3',
                            'key' => 'element3',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '3',
                        ],
                    ],
                ],
            ],
            'isNew' => true,
        ];

        yield 'Array like TCA fields are replaced completely when used in other elements' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '1',
                            'columns' => [
                                'tx_mask_select',
                            ],
                            'labels' => [
                                'Select 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '2',
                            'columns' => [
                                'tx_mask_select',
                            ],
                            'labels' => [
                                'Select 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_select' => [
                            'key' => 'select',
                            'fullKey' => 'tx_mask_select',
                            'type' => 'select',
                            'config' => [
                                'type' => 'select',
                                'items' => [
                                    [
                                        'Item 1',
                                        '1',
                                        '',
                                        '',
                                    ],
                                    [
                                        'Item 2',
                                        '2',
                                        '',
                                        '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'element' => [
                'label' => 'Element 2',
                'key' => 'element2',
                'shortLabel' => '',
                'description' => '',
                'icon' => '',
                'color' => '#000000',
            ],
            'fields' => [
                'tx_mask_select' => [
                    'key' => 'tx_mask_select',
                    'label' => 'Select 1',
                    'description' => '',
                    'name' => 'select',
                    'tca' => [
                        'config.items' => [
                            [
                                'Item 1',
                                '1',
                                '',
                                '',
                            ],
                        ],
                        'config.renderType' => 'selectSingle',
                    ],
                ],
            ],
            'table' => 'tt_content',
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'sorting' => '1',
                            'columns' => [
                                'tx_mask_select',
                            ],
                            'labels' => [
                                'Select 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                            'colorOverlay' => '',
                            'iconOverlay' => '',
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'shortLabel' => '',
                            'description' => '',
                            'icon' => '',
                            'color' => '#000000',
                            'columns' => [
                                'tx_mask_select',
                            ],
                            'labels' => [
                                'Select 1',
                            ],
                            'descriptions' => [
                                '',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_select' => [
                            'key' => 'select',
                            'fullKey' => 'tx_mask_select',
                            'type' => 'select',
                            'config' => [
                                'type' => 'select',
                                'items' => [
                                    [
                                        'Item 1',
                                        '1',
                                        '',
                                        '',
                                    ],
                                ],
                                'renderType' => 'selectSingle',
                            ],
                        ],
                    ],
                    'sql' => [
                        'tx_mask_select' => [
                            'tt_content' => [
                                'tx_mask_select' => 'varchar(255) DEFAULT \'\' NOT NULL',
                            ],
                        ],
                    ],
                ],
            ],
            'isNew' => false,
        ];
    }

    /**
     * @dataProvider addDataProvider
     * @test
     */
    public function add(array $json, array $element, array $fields, string $table, array $expected, bool $isNew): void
    {
        $storageRepository = $this->createStorageRepository($json);

        // @todo Replace workaround for resolvePackagePath.
        if (method_exists(PackageManager::class, 'resolvePackagePath')) {
            $packageManager = $this->prophesize(PackageManager::class);
            $packageManager->resolvePackagePath('EXT:mask/Tests/Unit/Fixtures/Defaults.php')->willReturn(Environment::getProjectPath() . '/Tests/Unit/Fixtures/Defaults.php');
            ExtensionManagementUtility::setPackageManager($packageManager->reveal());
        }

        self::assertEquals($expected, $storageRepository->add($element, $fields, $table, $isNew));
    }

    public function removeDataProvider(): array
    {
        return [
            'fields are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_field',
                                    'tx_mask_field_2',
                                ],
                                'key' => 'element1',
                                'label' => 'Elemen 1',
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field',
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'field_2',
                            ],
                            'header' => [
                                'key' => 'header',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_field_2',
                                ],
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                            'tx_mask_field_2' => [
                                'tt_content' => [
                                    'tx_mask_field_2' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_field_2' => [
                                'label' => 'Palette',
                                'description' => '',
                                'showitem' => ['header'],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ],
                ],
            ],
            'inline fields are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_inline',
                                ],
                                'key' => 'element1',
                                'label' => 'Element 1',
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
                        'sql' => [
                            'tx_mask_inline' => [
                                'tt_content' => [
                                    'tx_mask_inline' => 'int',
                                ],
                            ],
                        ],
                    ],
                    'tx_mask_inline' => [
                        'tca' => [
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field',
                                'inlineParent' => 'tx_mask_inline',
                                'order' => 1,
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field_2',
                                'inlineParent' => 'tx_mask_inline',
                                'order' => 2,
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tx_mask_inline' => [
                                    'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                            'tx_mask_field_2' => [
                                'tx_mask_inline' => [
                                    'tx_mask_field_2' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ],
                ],
            ],
            'palette fields and palettes are removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'key' => 'element1',
                                'label' => 'Element 1',
                            ],
                        ],
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
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                ],
                                'order' => [
                                    'element1' => 1,
                                ],
                                'inPalette' => '1',
                            ],
                            'tx_mask_field_2' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field_2',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                ],
                                'order' => [
                                    'element1' => 2,
                                ],
                                'inPalette' => '1',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                            'tx_mask_field_2' => [
                                'tt_content' => [
                                    'tx_mask_field_2' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'Palette',
                                'description' => '',
                                'showitem' => ['tx_mask_field', 'tx_mask_field_2'],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [],
                    ],
                ],
            ],
            'palette fields in use only inlineParent removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'labels' => [
                                    'Palette 1',
                                ],
                                'descriptions' => [
                                    '',
                                ],
                            ],
                            'element2' => [
                                'columns' => [
                                    'tx_mask_palette2',
                                ],
                                'key' => 'element2',
                                'label' => 'Element 2',
                                'labels' => [
                                    'Palette 2',
                                ],
                                'descriptions' => [
                                    '',
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
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                    'element2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    'element1' => 'Field',
                                    'element2' => 'Field',
                                ],
                                'order' => [
                                    'element1' => 1,
                                    'element2' => 1,
                                ],
                                'inPalette' => '1',
                            ],
                            'header' => [
                                'coreField' => '1',
                                'key' => 'header',
                                'inPalette' => '1',
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette',
                                    'element2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    'element1' => 'Header',
                                    'element2' => 'Header',
                                ],
                                'order' => [
                                    'element1' => 2,
                                    'element2' => 2,
                                ],
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'label' => 'Palette',
                                'description' => '',
                                'showitem' => ['tx_mask_field', 'header'],
                            ],
                            'tx_mask_palette2' => [
                                'label' => 'Palette 2',
                                'description' => '',
                                'showitem' => ['tx_mask_field', 'header'],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'tt_content' => [
                        'elements' => [
                            'element2' => [
                                'columns' => [
                                    'tx_mask_palette2',
                                ],
                                'key' => 'element2',
                                'label' => 'Element 2',
                                'description' => '',
                                'shortLabel' => '',
                                'color' => '',
                                'icon' => '',
                                'labels' => [
                                    'Palette 2',
                                ],
                                'descriptions' => [
                                    '',
                                ],
                                'sorting' => 0,
                                'colorOverlay' => '',
                                'iconOverlay' => '',
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette2' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette2',
                                'fullKey' => 'tx_mask_palette2',
                                'type' => 'palette',
                            ],
                            'tx_mask_field' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field',
                                'fullKey' => 'tx_mask_field',
                                'inlineParent' => [
                                    'element2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    'element2' => 'Field',
                                ],
                                'order' => [
                                    'element2' => 1,
                                ],
                                'inPalette' => 1,
                                'type' => 'string',
                            ],
                            'header' => [
                                'key' => 'header',
                                'fullKey' => 'header',
                                'coreField' => '1',
                                'inlineParent' => [
                                    'element2' => 'tx_mask_palette2',
                                ],
                                'label' => [
                                    'element2' => 'Header',
                                ],
                                'order' => [
                                    'element2' => 2,
                                ],
                                'inPalette' => 1,
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field' => [
                                'tt_content' => [
                                    'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette2' => [
                                'label' => 'Palette 2',
                                'description' => '',
                                'showitem' => ['tx_mask_field', 'header'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider removeDataProvider
     */
    public function remove(array $json, string $table, string $elementKey, array $expected): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['type'] = 'input';

        $storageRepository = $this->createStorageRepository($json);

        self::assertEquals($expected, $storageRepository->remove($table, $elementKey));
    }
}
