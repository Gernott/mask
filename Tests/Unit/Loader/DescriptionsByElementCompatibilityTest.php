<?php

namespace MASK\Mask\Tests\Unit\Loader;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Loader\DescriptionByElementCompatibilityTrait;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DescriptionsByElementCompatibilityTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    /**
     * @test
     */
    public function descriptionsByElementsAddedIfMissing(): void
    {
        $loader = new class {
            use DescriptionByElementCompatibilityTrait;
        };

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
                        ],
                        'columns' => [
                            'tx_mask_field',
                            'tx_mask_direct',
                            'tx_mask_palette',
                            'tx_mask_inline',
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
                        'description' => 'Palette description'
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
                            'element1' => 'tx_mask_palette'
                        ]
                    ],
                    'tx_mask_field3' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'key' => 'field3',
                        'type' => 'string',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'element1' => 'tx_mask_palette'
                        ]
                    ],
                    'tx_mask_inline' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'key' => 'inline',
                        'type' => 'inline',
                        'description' => 'Inline Field Description'
                    ],
                ],
                'palettes' => [
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => '',
                        'showitem' => [
                            'tx_mask_field2',
                            'tx_mask_field3',
                        ]
                    ]
                ]
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
                        ],
                        'columns' => [
                            'tx_mask_field',
                            'tx_mask_direct',
                            'tx_mask_palette',
                            'tx_mask_inline',
                        ],
                        'descriptions' => [
                            '',
                            'Direct description',
                            'Palette description',
                            'Inline Field Description',
                        ],
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '',
                        'icon' => '',
                        'sorting' => 0,
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
                            'element1' => 'tx_mask_palette'
                        ],
                        'description' => [
                            'element1' => 'Description in Palette'
                        ]
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
                            'element1' => 'tx_mask_palette'
                        ],
                        'description' => [
                            'element1' => ''
                        ]
                    ],
                    'tx_mask_inline' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'key' => 'inline',
                        'fullKey' => 'tx_mask_inline',
                        'type' => 'inline',
                    ],
                ],
                'palettes' => [
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => 'Palette description',
                        'showitem' => [
                            'tx_mask_field2',
                            'tx_mask_field3',
                        ]
                    ]
                ],
                'sql' => []
            ],
        ];

        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($input);
        $loader->addMissingDescriptionsByElement($tableDefinitionCollection);
        self::assertEquals($expected, $tableDefinitionCollection->toArray());
    }
}
