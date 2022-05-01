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

use MASK\Mask\Definition\ArrayDefinitionSorter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ArrayDefinitionSorterTest extends UnitTestCase
{
    /**
     * @return array<string|int, mixed> iterable
     */
    public function arrayIsSortedByKeysDataProvider(): iterable
    {
        yield 'associative array is sorted by keys recursively' => [
            'array' => [
                'c' => [
                    'c' => '',
                    'b' => '',
                    'a' => '',
                ],
                'b' => [
                    'c' => '',
                    'b' => '',
                    'a' => '',
                ],
                'a' => [
                    'c' => '',
                    'b' => '',
                    'a' => '',
                ],
            ],
            'expected' => [
                'a' => [
                    'a' => '',
                    'b' => '',
                    'c' => '',
                ],
                'b' => [
                    'a' => '',
                    'b' => '',
                    'c' => '',
                ],
                'c' => [
                    'a' => '',
                    'b' => '',
                    'c' => '',
                ],
            ],
        ];

        yield 'lists stay the way they are' => [
            'array' => [
                [
                    'c',
                    'b',
                    'a',
                ],
                [
                    'c',
                    'b',
                    'a',
                ],
                [
                    'c',
                    'b',
                    'a',
                ],
            ],
            'expected' => [
                [
                    'c',
                    'b',
                    'a',
                ],
                [
                    'c',
                    'b',
                    'a',
                ],
                [
                    'c',
                    'b',
                    'a',
                ],
            ],
        ];
    }

    /**
     * @dataProvider arrayIsSortedByKeysDataProvider
     * @test
     * @param array<string|int, mixed> $array
     * @param array<string|int, mixed> $expected
     */
    public function arrayIsSortedByKeys(array $array, array $expected): void
    {
        $arrayDefinitionSorter = new ArrayDefinitionSorter();
        self::assertSame($expected, $arrayDefinitionSorter->sort($array));
    }

    /**
     * @return array<string|int, mixed> iterable
     */
    public function excludedKeysAreNotSortedDataProvider(): iterable
    {
        yield 'given excluded keys are not sorted' => [
            'array' => [
                'a' => [
                    'excluded' => [
                        'c' => '',
                        'b' => '',
                        'a' => '',
                    ],
                    'alsoExcluded' => [
                        'c' => '',
                        'b' => '',
                        'a' => '',
                    ],
                    'notExcluded' => [
                        'c' => '',
                        'b' => '',
                        'a' => '',
                    ],
                ],
            ],
            'expected' => [
                'a' => [
                    'alsoExcluded' => [
                        'c' => '',
                        'b' => '',
                        'a' => '',
                    ],
                    'excluded' => [
                        'c' => '',
                        'b' => '',
                        'a' => '',
                    ],
                    'notExcluded' => [
                        'a' => '',
                        'b' => '',
                        'c' => '',
                    ],
                ],
            ],
            [
                'excluded',
                'alsoExcluded',
            ],
        ];
    }

    /**
     * @dataProvider excludedKeysAreNotSortedDataProvider
     * @test
     * @param array<string|int, mixed> $array
     * @param array<string|int, mixed> $expected
     * @param array<string> $excludedKeys
     */
    public function excludedKeysAreNotSorted(array $array, array $expected, array $excludedKeys): void
    {
        $arrayDefinitionSorter = new ArrayDefinitionSorter();
        $arrayDefinitionSorter->setExcludedKeys($excludedKeys);
        self::assertSame($expected, $arrayDefinitionSorter->sort($array));
    }
}
