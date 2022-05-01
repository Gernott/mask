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
use MASK\Mask\Loader\DefaultTcaCompatibilityTrait;
use MASK\Mask\Tests\Unit\ConfigurationLoader\FakeConfigurationLoader;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DefaultTcaCompatibilityTraitTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    /**
     * @test
     */
    public function missingDefaults(): void
    {
        $this->registerPackageManager();

        $loader = new class() {
            use DefaultTcaCompatibilityTrait;
        };

        $loader->setConfigurationLoader(new FakeConfigurationLoader());

        $input = [
            'tt_content' => [
                'elements' => [
                    'element1' => [
                        'key' => 'element1',
                        'label' => 'Element 1',
                        'labels' => [
                            0 => 'Integer Field',
                            1 => 'File Field',
                            2 => 'RTE Field',
                        ],
                        'columns' => [
                            0 => 'tx_mask_integer',
                            1 => 'tx_mask_file',
                            2 => 'tx_mask_rte',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_integer' => [
                        'config' => [
                            'type' => 'input',
                            'eval' => 'int,required',
                        ],
                        'key' => 'integer',
                    ],
                    'tx_mask_file' => [
                        'config' => [
                            'minitems' => '',
                            'maxitems' => '',
                        ],
                        'key' => 'file',
                        'options' => 'file',
                    ],
                    'tx_mask_rte' => [
                        'key' => 'rte',
                        'config' => [
                            'type' => 'text',
                        ],
                        'type' => 'richtext',
                        'exclude' => '1',
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
                            'tx_mask_integer',
                            'tx_mask_file',
                            'tx_mask_rte',
                        ],
                        'labels' => [
                            'Integer Field',
                            'File Field',
                            'RTE Field',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
                'tca' => [
                    'tx_mask_integer' => [
                        'key' => 'integer',
                        'fullKey' => 'tx_mask_integer',
                        'type' => 'integer',
                        'config' => [
                            'type' => 'input',
                            'eval' => 'int,required',
                        ],
                    ],
                    'tx_mask_file' => [
                        'key' => 'file',
                        'fullKey' => 'tx_mask_file',
                        'type' => 'file',
                        'config' => [
                            'type' => 'inline',
                            'foreign_table' => 'sys_file_reference',
                        ],
                        'imageoverlayPalette' => 1,
                    ],
                    'tx_mask_rte' => [
                        'key' => 'rte',
                        'fullKey' => 'tx_mask_rte',
                        'type' => 'richtext',
                        'config' => [
                            'type' => 'text',
                            'enableRichtext' => 1,
                        ],
                        'exclude' => 1,
                    ],
                ],
            ],
        ];

        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($input);
        $loader->addMissingDefaults($tableDefinitionCollection);
        self::assertEquals($expected, $tableDefinitionCollection->toArray());
    }
}
