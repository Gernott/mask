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

use MASK\Mask\Utility\TcaConverter;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\BaseTestCase;

class TcaConverterTest extends BaseTestCase
{
    public function convertTcaArrayToFlatTestDataProvider(): iterable
    {
        yield 'Simple array converted to flat' => [
            [
                'type' => 'input',
                'max' => '1',
            ],
            [
                'config.type' => 'input',
                'config.max' => '1',
            ],
        ];

        yield 'Nested array converted to flat' => [
            [
                'type' => 'input',
                'nested' => [
                    'option' => '1',
                ],
            ],
            [
                'config.type' => 'input',
                'config.nested.option' => '1',
            ],
        ];

        yield 'Items are kept as array' => [
            [
                'items' => [
                    ['label', 'item'],
                    ['label2', 'item2'],
                ],
            ],
            [
                'config.items' => [
                    ['label', 'item'],
                    ['label2', 'item2'],
                ],
            ],
        ];

        yield 'Items are kept as associative array' => [
            [
                'items' => [
                    [0 => 'label', 1 => 'item', 'key' => 'value'],
                    [0 => 'label2', 1 => 'item2', 'key' => 'value'],
                ],
            ],
            [
                'config.items' => [
                    [0 => 'label', 1 => 'item', 'key' => 'value'],
                    [0 => 'label2', 1 => 'item2', 'key' => 'value'],
                ],
            ],
        ];

        yield 'Eval values converted as seperate entries' => [
            [
                'eval' => 'required,int',
            ],
            [
                'config.eval.required' => 1,
                'config.eval.int' => 1,
            ],
        ];

        yield 'Empty eval values are ignored' => [
            [
                'eval' => '',
            ],
            [],
        ];

        if ((new Typo3Version())->getMajorVersion() === 11) {
            yield 'Date types in eval moved to config.eval instead' => [
                [
                    'eval' => 'date',
                ],
                [
                    'config.eval' => 'date',
                ],
            ];
        }

        yield 'blindLinkOptions values converted to array' => [
            [
                'fieldControl' => [
                    'linkPopup' => [
                        'options' => [
                            'blindLinkOptions' => 'file,folder',
                        ],
                    ],
                ],
            ],
            [
                'config.fieldControl.linkPopup.options.blindLinkOptions' => [
                    'file',
                    'folder',
                ],
            ],
        ];

        yield 'slug fields converted to flat array structure' => [
            [
                'generatorOptions' => [
                    'fields' => [
                        ['a', 'b'],
                        'c',
                    ],
                ],
            ],
            [
                'config.generatorOptions.fields' => 'a|b,c',
            ],
        ];

        yield 'slug eval converted to special slug eval' => [
            [
                'type' => 'slug',
                'eval' => 'unique',
            ],
            [
                'config.type' => 'slug',
                'config.eval.slug' => 'unique',
            ],
        ];

        yield 'slug replacements transformed to key-value pairs and empty values are not removed' => [
            [
                'generatorOptions' => [
                    'replacements' => [
                        'a' => 'b',
                        'c' => '',
                    ],
                ],
            ],
            [
                'config.generatorOptions.replacements' => [
                    [
                        'key' => 'a',
                        'value' => 'b',
                    ],
                    [
                        'key' => 'c',
                        'value' => '',
                    ],
                ],
            ],
        ];

        yield 'associative array transformed to key value pairs' => [
            [
                'itemGroups' => [
                    'group1' => 'Label Group 1',
                    'group2' => 'Label Group 2',
                ],
            ],
            [
                'config.itemGroups' => [
                    [
                        'key' => 'group1',
                        'value' => 'Label Group 1',
                    ],
                    [
                        'key' => 'group2',
                        'value' => 'Label Group 2',
                    ],
                ],
            ],
        ];

        yield 'Lists are not flattened' => [
            [
                'allowedTypes' => ['page', 'file'],
            ],
            [
                'config.allowedTypes' => ['page', 'file'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertTcaArrayToFlatTestDataProvider
     */
    public function convertTcaArrayToFlatTest(array $array, array $expected): void
    {
        self::assertSame($expected, TcaConverter::convertTcaArrayToFlat($array, ['config']));
    }

    public function convertFlatTcaToArrayTestDataProvider(): iterable
    {
        yield 'Nested flat array is converted to original TCA array' => [
            [
                'config.nested.option' => 'value',
            ],
            [
                'config' => [
                    'nested' => [
                        'option' => 'value',
                    ],
                ],
            ],
        ];

        yield 'Items are trimmed' => [
            [
                'config.items' => [
                    [' label', ' item'],
                    ['label2 ', 'item2 '],
                ],
            ],
            [
                'config' => [
                    'items' => [
                        ['label', 'item'],
                        ['label2', 'item2'],
                    ],
                ],
            ],
        ];

        yield 'Eval values converted to comma separated list' => [
            [
                'config.eval.required' => 1,
                'config.eval.int' => 1,
            ],
            [
                'config' => [
                    'eval' => 'required,int',
                ],
            ],
        ];

        if ((new Typo3Version())->getMajorVersion() === 11) {
            yield 'Date eval value moves back to eval list' => [
                [
                    'config.eval.required' => 1,
                    'config.eval' => 'date',
                ],
                [
                    'config' => [
                        'eval' => 'required,date',
                    ],
                ],
            ];
        }

        yield 'blindLinkOptions values converted to comma separated list' => [
            [
                'config.fieldControl.linkPopup.options.blindLinkOptions' => [
                    'file',
                    'folder',
                ],
            ],
            [
                'config' => [
                    'fieldControl' => [
                        'linkPopup' => [
                            'options' => [
                                'blindLinkOptions' => 'file,folder',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'slug fields converted to nested array structure' => [
            [
                'config.generatorOptions.fields' => 'a|b,c',
            ],
            [
                'config' => [
                    'generatorOptions' => [
                        'fields' => [
                            ['a', 'b'],
                            'c',
                        ],
                    ],
                ],
            ],
        ];

        yield 'slug eval converted to normal eval' => [
            [
                'config.type' => 'slug',
                'config.eval.slug' => 'unique',
            ],
            [
                'config' => [
                    'type' => 'slug',
                    'eval' => 'unique',
                ],
            ],
        ];

        yield 'slug replacements key value pairs transformed to associative array' => [
            [
                'config.generatorOptions.replacements' => [
                    [
                        'key' => 'a',
                        'value' => 'b',
                    ],
                    [
                        'key' => 'c',
                        'value' => '',
                    ],
                ],
            ],
            [
                'config' => [
                    'generatorOptions' => [
                        'replacements' => [
                            'a' => 'b',
                            'c' => '',
                        ],
                    ],
                ],
            ],
        ];

        yield 'key value pairs transformed to associative array' => [
            [
                'config.itemGroups' => [
                    [
                        'key' => 'group1',
                        'value' => 'Label Group 1',
                    ],
                    [
                        'key' => 'group2',
                        'value' => 'Label Group 2',
                    ],
                ],
            ],
            [
                'config' => [
                    'itemGroups' => [
                        'group1' => 'Label Group 1',
                        'group2' => 'Label Group 2',
                    ],
                ],
            ],
        ];

        yield 'lists are not transformed' => [
            [
                'config.allowedTypes' => ['page', 'file'],
            ],
            [
                'config' => [
                    'allowedTypes' => ['page', 'file'],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertFlatTcaToArrayTestDataProvider
     */
    public function convertFlatTcaToArrayTest(array $array, array $expected): void
    {
        self::assertSame($expected, TcaConverter::convertFlatTcaToArray($array));
    }
}
