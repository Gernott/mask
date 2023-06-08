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

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\OverrideFieldsUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class OverrideFieldsUtilityTest extends BaseTestCase
{
    public static function restructuringFieldsWorksDataProvider(): iterable
    {
        yield 'simple fields on root' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_bar',
                            ],
                        ],
                        'element2' => [
                            'key' => 'element2',
                            'label' => 'Element2',
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_fizz',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_foo' => [
                            'type' => 'string',
                            'key' => 'foo',
                            'fullKey' => 'tx_mask_foo',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                        'tx_mask_bar' => [
                            'type' => 'string',
                            'key' => 'bar',
                            'fullKey' => 'tx_mask_bar',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                        'tx_mask_fizz' => [
                            'type' => 'string',
                            'key' => 'fizz',
                            'fullKey' => 'tx_mask_fizz',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'color' => '',
                            'colorOverlay' => '',
                            'description' => '',
                            'descriptions' => [],
                            'icon' => '',
                            'iconOverlay' => '',
                            'labels' => [],
                            'shortLabel' => '',
                            'sorting' => 0,
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_bar',
                            ],
                            'columnsOverride' => [
                                'tx_mask_foo' => [
                                    'type' => 'string',
                                    'key' => 'foo',
                                    'fullKey' => 'tx_mask_foo',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                                'tx_mask_bar' => [
                                    'type' => 'string',
                                    'key' => 'bar',
                                    'fullKey' => 'tx_mask_bar',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                        'element2' => [
                            'key' => 'element2',
                            'label' => 'Element2',
                            'color' => '',
                            'colorOverlay' => '',
                            'description' => '',
                            'descriptions' => [],
                            'icon' => '',
                            'iconOverlay' => '',
                            'labels' => [],
                            'shortLabel' => '',
                            'sorting' => 0,
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_fizz',
                            ],
                            'columnsOverride' => [
                                'tx_mask_foo' => [
                                    'type' => 'string',
                                    'key' => 'foo',
                                    'fullKey' => 'tx_mask_foo',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                                'tx_mask_fizz' => [
                                    'type' => 'string',
                                    'key' => 'fizz',
                                    'fullKey' => 'tx_mask_fizz',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'Existing overrideColumns are kept as they are' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_bar',
                            ],
                            'columnsOverride' => [
                                'tx_mask_foo' => [
                                    'key' => 'foo',
                                    'type' => 'string',
                                    'config' => [
                                        'eval' => 'alpha',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_foo' => [
                            'type' => 'string',
                            'key' => 'foo',
                            'fullKey' => 'tx_mask_foo',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                        'tx_mask_bar' => [
                            'type' => 'string',
                            'key' => 'bar',
                            'fullKey' => 'tx_mask_bar',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'color' => '',
                            'colorOverlay' => '',
                            'description' => '',
                            'descriptions' => [],
                            'icon' => '',
                            'iconOverlay' => '',
                            'labels' => [],
                            'shortLabel' => '',
                            'sorting' => 0,
                            'columns' => [
                                'tx_mask_foo',
                                'tx_mask_bar',
                            ],
                            'columnsOverride' => [
                                'tx_mask_foo' => [
                                    'type' => 'string',
                                    'key' => 'foo',
                                    'fullKey' => 'tx_mask_foo',
                                    'config' => [
                                        'eval' => 'alpha',
                                    ],
                                ],
                                'tx_mask_bar' => [
                                    'type' => 'string',
                                    'key' => 'bar',
                                    'fullKey' => 'tx_mask_bar',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'simple fields in palette' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'columns' => [
                                'tx_mask_palette1',
                            ],
                        ],
                        'element2' => [
                            'key' => 'element2',
                            'label' => 'Element2',
                            'columns' => [
                                'tx_mask_fizz',
                                'tx_mask_bar',
                            ],
                        ],
                    ],
                    'palettes' => [
                        'tx_mask_palette1' => [
                            'showitem' => [
                                'tx_mask_foo',
                                'tx_mask_bar',
                            ],
                        ],
                    ],
                    'tca' => [
                        'tx_mask_palette1' => [
                            'type' => 'palette',
                            'key' => 'palette1',
                            'fullKey' => 'tx_mask_palette1',
                            'config' => [
                                'type' => 'palette',
                            ],
                        ],
                        'tx_mask_foo' => [
                            'type' => 'string',
                            'key' => 'foo',
                            'fullKey' => 'tx_mask_foo',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                        'tx_mask_bar' => [
                            'type' => 'string',
                            'key' => 'bar',
                            'fullKey' => 'tx_mask_bar',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                        'tx_mask_fizz' => [
                            'type' => 'string',
                            'key' => 'fizz',
                            'fullKey' => 'tx_mask_fizz',
                            'config' => [
                                'type' => 'input',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'key' => 'element1',
                            'label' => 'Element1',
                            'color' => '',
                            'colorOverlay' => '',
                            'description' => '',
                            'descriptions' => [],
                            'icon' => '',
                            'iconOverlay' => '',
                            'labels' => [],
                            'shortLabel' => '',
                            'sorting' => 0,
                            'columns' => [
                                'tx_mask_palette1',
                            ],
                            'columnsOverride' => [
                                'tx_mask_foo' => [
                                    'type' => 'string',
                                    'key' => 'foo',
                                    'fullKey' => 'tx_mask_foo',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                                'tx_mask_bar' => [
                                    'type' => 'string',
                                    'key' => 'bar',
                                    'fullKey' => 'tx_mask_bar',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                        'element2' => [
                            'key' => 'element2',
                            'label' => 'Element2',
                            'color' => '',
                            'colorOverlay' => '',
                            'description' => '',
                            'descriptions' => [],
                            'icon' => '',
                            'iconOverlay' => '',
                            'labels' => [],
                            'shortLabel' => '',
                            'sorting' => 0,
                            'columns' => [
                                'tx_mask_fizz',
                                'tx_mask_bar',
                            ],
                            'columnsOverride' => [
                                'tx_mask_fizz' => [
                                    'type' => 'string',
                                    'key' => 'fizz',
                                    'fullKey' => 'tx_mask_fizz',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                                'tx_mask_bar' => [
                                    'type' => 'string',
                                    'key' => 'bar',
                                    'fullKey' => 'tx_mask_bar',
                                    'config' => [
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider restructuringFieldsWorksDataProvider
     */
    public function restructuringFieldsWorks(array $json, array $expected): void
    {
        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);

        $result = OverrideFieldsUtility::restructureTcaDefinitions($tableDefinitionCollection);

        self::assertEquals($expected['tt_content']['elements'], $result->toArray()['tables']['tt_content']['elements']);
    }
}
