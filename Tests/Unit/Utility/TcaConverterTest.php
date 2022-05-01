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
use TYPO3\TestingFramework\Core\BaseTestCase;

class TcaConverterTest extends BaseTestCase
{
    public function convertTcaArrayToFlatTestDataProvider(): iterable
    {
        return [
            'Simple array converted to flat' => [
                [
                    'type' => 'input',
                    'max' => '1',
                ],
                [
                    'config.type' => 'input',
                    'config.max' => '1',
                ],
            ],
            'Nested array converted to flat' => [
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
            ],
            'Items are kept as array' => [
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
            ],
            'Items are kept as associative array' => [
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
            ],
            'Eval values converted as seperate entries' => [
                [
                    'eval' => 'required,int',
                ],
                [
                    'config.eval.required' => 1,
                    'config.eval.int' => 1,
                ],
            ],
            'Empty eval values are ignored' => [
                [
                    'eval' => '',
                ],
                [],
            ],
            'Date types in eval moved to config.eval instead' => [
                [
                    'eval' => 'date',
                ],
                [
                    'config.eval' => 'date',
                ],
            ],
            'blindLinkOptions values converted to array' => [
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
            ],
            'slug fields converted to flat array structure' => [
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
            ],
            'slug eval converted to special slug eval' => [
                [
                    'type' => 'slug',
                    'eval' => 'unique',
                ],
                [
                    'config.type' => 'slug',
                    'config.eval.slug' => 'unique',
                ],
            ],
            'slug replacements transformed to key-value pairs and empty values are not removed' => [
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
            ],
            'associative array transformed to key value pairs' => [
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
        return [
            'Simple flat array is converted to original TCA array' => [
                [
                    'config.type' => 'input',
                ],
                [
                    'config' => [
                        'type' => 'input',
                    ],
                ],
            ],
            'Nested flat array is converted to original TCA array' => [
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
            ],
            'Items are trimmed' => [
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
            ],
            'Eval values converted to comma separated list' => [
                [
                    'config.eval.required' => 1,
                    'config.eval.int' => 1,
                ],
                [
                    'config' => [
                        'eval' => 'required,int',
                    ],
                ],
            ],
            'Date eval value moves back to eval list' => [
                [
                    'config.eval.required' => 1,
                    'config.eval' => 'date',
                ],
                [
                    'config' => [
                        'eval' => 'required,date',
                    ],
                ],
            ],
            'blindLinkOptions values converted to comma separated list' => [
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
            ],
            'slug fields converted to nested array structure' => [
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
            ],
            'slug eval converted to normal eval' => [
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
            ],
            'slug replacements key value pairs transformed to associative array' => [
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
            ],
            'key value pairs transformed to associative array' => [
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
