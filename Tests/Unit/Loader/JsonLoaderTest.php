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

namespace MASK\Mask\Tests\Unit\Loader;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Loader\JsonLoader;
use MASK\Mask\Migrations\MigrationManager;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class JsonLoaderTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    public function tearDown(): void
    {
        parent::tearDown();
        GeneralUtility::rmdir(Environment::getPublicPath() . '/typo3conf/ext/mask/var/mask.json');
    }

    /**
     * @test
     */
    public function load(): void
    {
        $this->registerPackageManager();

        $jsonLoader = new JsonLoader(
            [
                'json' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/mask.json',
            ],
            new MigrationManager([])
        );

        self::assertEquals($this->getExpectedConfigurationArray(), $jsonLoader->load()->toArray(false));
    }

    /**
     * @test
     */
    public function write(): void
    {
        $this->registerPackageManager();

        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['type'] = 'input';
        $jsonLoader = new JsonLoader(
            [
                'json' => 'EXT:mask/var/mask.json',
            ],
            new MigrationManager([])
        );
        $jsonLoader->write(TableDefinitionCollection::createFromArray($this->getExpectedConfigurationArray()));
        $jsonPath = Environment::getPublicPath() . '/typo3conf/ext/mask/var/mask.json';
        self::assertFileExists($jsonPath);
        self::assertEquals($this->getExpectedConfigurationArray(), $jsonLoader->load()->toArray(false));
    }

    protected function getExpectedConfigurationArray(): array
    {
        return [
            'tx_mask_repeat1' => [
                'sql' => [
                    'tx_mask_a' => [
                        'tx_mask_repeat1' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'fullKey' => 'tx_mask_a',
                        'label' => 'A',
                        'type' => 'string',
                        'key' => 'a',
                        'inlineParent' => 'tx_mask_repeat1',
                        'order' => 1,
                    ],
                ],
            ],
            'tx_mask_inline' => [
                'sql' => [
                    'tx_mask_field' => [
                        'tx_mask_inline' => [
                            'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'fullKey' => 'tx_mask_field',
                        'label' => 'Field',
                        'type' => 'string',
                        'key' => 'field',
                        'inlineParent' => 'tx_mask_inline',
                        'order' => 1,
                    ],
                ],
            ],
            'tt_content' => [
                'elements' => [
                    'a' => [
                        'key' => 'a',
                        'label' => 'A',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            'tx_mask_a',
                            'tx_mask_repeat1',
                            'tx_mask_im_just_empty',
                        ],
                        'labels' => [
                            'A',
                            'Repeat1',
                            'Empty inline field',
                        ],
                        'descriptions' => [
                            '',
                            'description for field tx_mask_repeat1',
                            '',
                        ],
                        'sorting' => 0,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'b' => [
                        'key' => 'b',
                        'label' => 'B',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            'tx_mask_a',
                            'tx_mask_b',
                            'tx_mask_4e12de3d14bd5',
                        ],
                        'labels' => [
                            'A 2',
                            'B',
                            'Palette 1',
                        ],
                        'descriptions' => [
                            '',
                            '',
                            '',
                        ],
                        'sorting' => 1,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'c' => [
                        'key' => 'c',
                        'label' => 'C',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            'tx_mask_file',
                        ],
                        'labels' => [
                            'File',
                        ],
                        'descriptions' => [
                            'only images are allowed',
                        ],
                        'sorting' => 2,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                    'd' => [
                        'key' => 'd',
                        'label' => 'D',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            'tx_mask_palette',
                        ],
                        'labels' => [
                            'Palette 1',
                        ],
                        'descriptions' => [
                            'Description for palette 1',
                        ],
                        'sorting' => 3,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tt_content' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_b' => [
                        'tt_content' => [
                            'tx_mask_b' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_repeat1' => [
                        'tt_content' => [
                            'tx_mask_repeat1' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                    ],
                    'tx_mask_file' => [
                        'tt_content' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'fullKey' => 'tx_mask_a',
                        'type' => 'string',
                        'key' => 'a',
                    ],
                    'tx_mask_b' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'fullKey' => 'tx_mask_b',
                        'type' => 'string',
                        'key' => 'b',
                    ],
                    'tx_mask_repeat1' => [
                        'config' => [
                            'appearance' => [
                                'collapseAll' => 1,
                                'enabledControls' => [
                                    'dragdrop' => 1,
                                ],
                                'levelLinksPosition' => 'top',
                                'showAllLocalizationLink' => 1,
                                'showPossibleLocalizationRecords' => 1,
                            ],
                            'foreign_field' => 'parentid',
                            'foreign_sortby' => 'sorting',
                            'foreign_table' => '--inlinetable--',
                            'foreign_table_field' => 'parenttable',
                            'type' => 'inline',
                        ],
                        'fullKey' => 'tx_mask_repeat1',
                        'type' => 'inline',
                        'key' => 'repeat1',
                    ],
                    'tx_mask_inline' => [
                        'config' => [
                            'appearance' => [
                                'collapseAll' => 1,
                                'enabledControls' => [
                                    'dragdrop' => 1,
                                ],
                                'levelLinksPosition' => 'top',
                                'showAllLocalizationLink' => 1,
                                'showPossibleLocalizationRecords' => 1,
                            ],
                            'foreign_field' => 'parentid',
                            'foreign_sortby' => 'sorting',
                            'foreign_table' => '--inlinetable--',
                            'foreign_table_field' => 'parenttable',
                            'type' => 'inline',
                        ],
                        'fullKey' => 'tx_mask_inline',
                        'type' => 'inline',
                        'key' => 'inline',
                    ],
                    'tx_mask_4e12de3d14bd5' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'type' => 'palette',
                        'key' => '4e12de3d14bd5',
                        'fullKey' => 'tx_mask_4e12de3d14bd5',
                    ],
                    'header' => [
                        'coreField' => 1,
                        'type' => 'string',
                        'key' => 'header',
                        'fullKey' => 'header',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'b' => 'tx_mask_4e12de3d14bd5',
                        ],
                        'label' => [
                            'b' => 'Header',
                        ],
                        'order' => [
                            'b' => 1,
                        ],
                        'description' => [
                            'b' => '',
                        ],
                    ],
                    'tx_mask_file' => [
                        'config' => [
                            'appearance' => [
                                'fileUploadAllowed' => 1,
                            ],
                        ],
                        'type' => 'file',
                        'key' => 'file',
                        'fullKey' => 'tx_mask_file',
                        'imageoverlayPalette' => 1,
                    ],
                    'tx_mask_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'type' => 'palette',
                        'key' => 'palette',
                        'fullKey' => 'tx_mask_palette',
                    ],
                    'tx_mask_im_just_empty' => [
                        'config' => [
                            'appearance' => [
                                'collapseAll' => 1,
                                'enabledControls' => [
                                    'dragdrop' => 1,
                                ],
                                'levelLinksPosition' => 'top',
                                'showAllLocalizationLink' => 1,
                                'showPossibleLocalizationRecords' => 1,
                            ],
                            'foreign_field' => 'parentid',
                            'foreign_sortby' => 'sorting',
                            'foreign_table' => '--inlinetable--',
                            'foreign_table_field' => 'parenttable',
                            'type' => 'inline',
                        ],
                        'fullKey' => 'tx_mask_im_just_empty',
                        'type' => 'inline',
                        'key' => 'im_just_empty',
                    ],
                ],
                'palettes' => [
                    'tx_mask_4e12de3d14bd5' => [
                        'label' => 'Palette 1',
                        'description' => '',
                        'showitem' => ['header'],
                    ],
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => 'Description for palette 1',
                        'showitem' => ['tx_mask_inline'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function brokenConfigurationRaisesException(): void
    {
        $this->registerPackageManager();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectDeprecationMessage('Expected "json" to be a valid file path. The value "../mask.json" was given.');
        $this->expectExceptionCode(1639220370);
        $jsonLoader = new JsonLoader(['json' => '../mask.json'], new MigrationManager([]));
        $jsonLoader->load();
    }
}
