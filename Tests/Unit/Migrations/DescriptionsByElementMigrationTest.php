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

namespace MASK\Mask\Tests\Unit\Migrations;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Migrations\DescriptionByElementMigration;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DescriptionsByElementMigrationTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    /**
     * @test
     */
    public function descriptionsByElementsAddedIfMissing(): void
    {
        $input = [
            'tt_content' => [
                'elements' => [
                    'element1' => [
                        'key' => 'element1',
                        'label' => 'Element 1',
                        'labels' => [
                            'Field 1',
                            'Field direct description',
                            'Palette 1',
                            'Inline Field',
                            '',
                        ],
                        'columns' => [
                            'tx_mask_field',
                            'tx_mask_direct',
                            'tx_mask_palette',
                            'tx_mask_inline',
                            'header',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field',
                    ],
                    'tx_mask_direct' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'direct',
                        'description' => 'Direct description',
                    ],
                    'tx_mask_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'key' => 'palette',
                        'description' => 'Palette description',
                    ],
                    'tx_mask_field2' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field2',
                        'type' => 'string',
                        'inPalette' => 1,
                        'description' => 'Description in Palette',
                        'inlineParent' => [
                            'element1' => 'tx_mask_palette',
                        ],
                    ],
                    'tx_mask_field3' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field3',
                        'type' => 'string',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'element1' => 'tx_mask_palette',
                        ],
                    ],
                    'tx_mask_inline' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'key' => 'inline',
                        'type' => 'inline',
                        'description' => 'Inline Field Description',
                    ],
                    'header' => [
                        'key' => 'header',
                    ],
                ],
                'palettes' => [
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => '',
                        'showitem' => [
                            'tx_mask_field2',
                            'tx_mask_field3',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'tt_content' => [
                'elements' => [
                    'element1' => [
                        'key' => 'element1',
                        'label' => 'Element 1',
                        'labels' => [
                            'Field 1',
                            'Field direct description',
                            'Palette 1',
                            'Inline Field',
                            '',
                        ],
                        'columns' => [
                            'tx_mask_field',
                            'tx_mask_direct',
                            'tx_mask_palette',
                            'tx_mask_inline',
                            'header',
                        ],
                        'descriptions' => [
                            '',
                            'Direct description',
                            'Palette description',
                            'Inline Field Description',
                            '',
                        ],
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '',
                        'icon' => '',
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field',
                        'fullKey' => 'tx_mask_field',
                        'type' => 'string',
                    ],
                    'tx_mask_direct' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'direct',
                        'fullKey' => 'tx_mask_direct',
                        'type' => 'string',
                    ],
                    'tx_mask_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'key' => 'palette',
                        'fullKey' => 'tx_mask_palette',
                        'type' => 'palette',
                    ],
                    'tx_mask_field2' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field2',
                        'fullKey' => 'tx_mask_field2',
                        'type' => 'string',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'element1' => 'tx_mask_palette',
                        ],
                        'description' => [
                            'element1' => 'Description in Palette',
                        ],
                    ],
                    'tx_mask_field3' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field3',
                        'fullKey' => 'tx_mask_field3',
                        'type' => 'string',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'element1' => 'tx_mask_palette',
                        ],
                        'description' => [
                            'element1' => '',
                        ],
                    ],
                    'tx_mask_inline' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'key' => 'inline',
                        'fullKey' => 'tx_mask_inline',
                        'type' => 'inline',
                    ],
                    'header' => [
                        'key' => 'header',
                        'fullKey' => 'header',
                        'coreField' => 1,
                    ],
                ],
                'palettes' => [
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => 'Palette description',
                        'showitem' => [
                            'tx_mask_field2',
                            'tx_mask_field3',
                        ],
                    ],
                ],
            ],
        ];

        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($input);
        $descriptionByElementMigration = new DescriptionByElementMigration();
        self::assertEquals($expected, $descriptionByElementMigration->migrate($tableDefinitionCollection)->toArray(false));
    }
}
