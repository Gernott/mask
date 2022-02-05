<?php

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

namespace MASK\Mask\Tests\Unit\Loader;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Loader\ConfigCleanerTrait;
use MASK\Mask\Tests\Unit\ConfigurationLoader\FakeConfigurationLoader;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ConfigCleanerTraitTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    /**
     * @test
     */
    public function cleanupConfig(): void
    {
        $this->registerPackageManager();

        $loader = new class() {
            use ConfigCleanerTrait;
        };

        $loader->setConfigurationLoader(new FakeConfigurationLoader());

        $input = [
            'tt_content' => [
                'elements' => [
                    'element1' => [
                        'key' => 'element1',
                        'label' => 'Element 1',
                        'labels' => [
                            'RTE Field',
                        ],
                        'columns' => [
                            'tx_mask_rte',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_rte' => [
                        'key' => 'rte',
                        'config' => [
                            'type' => 'text',
                            'foo' => 'bar',
                            'enableRichtext' => 1,
                            'eval' => 'unique'
                        ],
                        'type' => 'richtext',
                        'exclude' => '1',
                        'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'
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
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '',
                        'icon' => '',
                        'descriptions' => [],
                        'columns' => [
                            'tx_mask_rte',
                        ],
                        'labels' => [
                            'RTE Field',
                        ],
                        'sorting' => 0
                    ]
                ],
                'tca' => [
                    'tx_mask_rte' => [
                        'config' => [
                            'type' => 'text',
                            'enableRichtext' => 1,
                            'eval' => 'unique'
                        ],
                        'key' => 'rte',
                        'fullKey' => 'tx_mask_rte',
                        'type' => 'richtext',
                    ]
                ],
            ],
        ];

        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($input);
        $loader->cleanUpConfig($tableDefinitionCollection);
        self::assertEquals($expected, $tableDefinitionCollection->toArray());
    }
}
