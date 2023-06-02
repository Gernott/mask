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

use MASK\Mask\Definition\ElementDefinitionCollection;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ElementDefinitionCollectionTest extends UnitTestCase
{
    public function elementsAreSortedBySortingDataProvider(): iterable
    {
        yield 'Sorting of elements is preserved without sorting set' => [
            'json' => [
                'element1' => [
                    'key' => 'element1',
                    'label' => 'Element1',
                ],
                'element2' => [
                    'key' => 'element2',
                    'label' => 'Element2',
                ],
                'element3' => [
                    'key' => 'element3',
                    'label' => 'Element3',
                ],
            ],
            'expected' => [
                [
                    'key' => 'element1',
                    'label' => 'Element1',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element2',
                    'label' => 'Element2',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element3',
                    'label' => 'Element3',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
            ],
        ];

        yield 'Sorting of elements is preserved with sorting set' => [
            'json' => [
                'element1' => [
                    'key' => 'element1',
                    'label' => 'Element1',
                    'sorting' => '0',
                ],
                'element2' => [
                    'key' => 'element2',
                    'label' => 'Element2',
                    'sorting' => '0',
                ],
                'element3' => [
                    'key' => 'element3',
                    'label' => 'Element3',
                    'sorting' => '0',
                ],
            ],
            'expected' => [
                [
                    'key' => 'element1',
                    'label' => 'Element1',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element2',
                    'label' => 'Element2',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element3',
                    'label' => 'Element3',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
            ],
        ];

        yield 'Sorting of elements is preserved with real sorting set' => [
            'json' => [
                'element1' => [
                    'key' => 'element1',
                    'label' => 'Element1',
                    'sorting' => '2',
                ],
                'element2' => [
                    'key' => 'element2',
                    'label' => 'Element2',
                    'sorting' => '1',
                ],
                'element3' => [
                    'key' => 'element3',
                    'label' => 'Element3',
                    'sorting' => '0',
                ],
            ],
            'expected' => [
                [
                    'key' => 'element3',
                    'label' => 'Element3',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 0,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element2',
                    'label' => 'Element2',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 1,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
                [
                    'key' => 'element1',
                    'label' => 'Element1',
                    'description' => '',
                    'shortLabel' => '',
                    'color' => '',
                    'icon' => '',
                    'columns' => [],
                    'labels' => [],
                    'descriptions' => [],
                    'sorting' => 2,
                    'colorOverlay' => '',
                    'iconOverlay' => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider elementsAreSortedBySortingDataProvider
     * @test
     */
    public function elementsAreSortedBySorting(array $json, array $expected): void
    {
        $elements = [];
        foreach (ElementDefinitionCollection::createFromArray($json, 'tt_content') as $element) {
            $elements[] = $element->toArray();
        }
        self::assertEquals($expected, $elements);
    }
}
