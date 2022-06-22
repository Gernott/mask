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
use MASK\Mask\Loader\JsonSplitLoader;
use MASK\Mask\Migrations\MigrationManager;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class JsonSplitLoaderTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    use PackageManagerTrait;

    public function tearDown(): void
    {
        parent::tearDown();
        GeneralUtility::rmdir(Environment::getPublicPath() . '/typo3conf/ext/mask/var/ContentElements', true);
    }

    /**
     * @test
     */
    public function load(): void
    {
        $this->registerPackageManager();
        $jsonSplitLoader = new JsonSplitLoader(
            [
                'content_elements_folder' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/ContentElements',
                'backend_layouts_folder' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/BackendLayouts',
            ],
            new MigrationManager([])
        );

        self::assertEquals($this->getExpectedConfigurationArray(), $jsonSplitLoader->load()->toArray(false));
    }

    /**
     * @test
     */
    public function write(): void
    {
        $this->registerPackageManager();

        $GLOBALS['TCA']['tt_content']['columns']['header']['config']['type'] = 'input';
        $jsonSplitLoader = new JsonSplitLoader(
            [
                'content_elements_folder' => 'EXT:mask/var/ContentElements',
                'backend_layouts_folder' => 'EXT:mask/var/BackendLayouts',
            ],
            new MigrationManager([])
        );

        $jsonSplitLoader->write(TableDefinitionCollection::createFromArray($this->getExpectedConfigurationArray()));

        $contentElementsPath = Environment::getPublicPath() . '/typo3conf/ext/mask/var/ContentElements';
        self::assertFileExists($contentElementsPath . '/a.json');
        self::assertFileExists($contentElementsPath . '/b.json');
        self::assertFileExists($contentElementsPath . '/c.json');
        self::assertFileExists($contentElementsPath . '/d.json');
        self::assertFileExists($contentElementsPath . '/e.json');

        $configurationA = [
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
                        'label' => 'A',
                        'type' => 'string',
                        'key' => 'a',
                        'fullKey' => 'tx_mask_a',
                        'inlineParent' => 'tx_mask_repeat1',
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
                        ],
                        'labels' => [
                            'A',
                            'Repeat1',
                        ],
                        'descriptions' => [
                            '',
                            'description for field tx_mask_repeat1',
                        ],
                        'sorting' => 0,
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
                    'tx_mask_repeat1' => [
                        'tt_content' => [
                            'tx_mask_repeat1' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                    ],
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'type' => 'string',
                        'key' => 'a',
                        'fullKey' => 'tx_mask_a',
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
                        'type' => 'inline',
                        'key' => 'repeat1',
                        'fullKey' => 'tx_mask_repeat1',
                    ],
                ],
            ],
        ];

        self::assertEquals($configurationA, json_decode(file_get_contents($contentElementsPath . '/a.json'), true)['tables']);

        $configurationB = [
            'tt_content' => [
                'elements' => [
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
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'type' => 'string',
                        'key' => 'a',
                        'fullKey' => 'tx_mask_a',
                    ],
                    'tx_mask_b' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'type' => 'string',
                        'key' => 'b',
                        'fullKey' => 'tx_mask_b',
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
                    ],
                ],
                'palettes' => [
                    'tx_mask_4e12de3d14bd5' => [
                        'label' => 'Palette 1',
                        'description' => '',
                        'showitem' => ['header'],
                    ],
                ],
            ],
        ];

        self::assertEquals($configurationB, json_decode(file_get_contents($contentElementsPath . '/b.json'), true)['tables']);

        $configurationC = [
            'tt_content' => [
                'elements' => [
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
                ],
                'sql' => [
                    'tx_mask_file' => [
                        'tt_content' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
                        ],
                    ],
                ],
                'tca' => [
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
                ],
            ],
        ];

        self::assertEquals($configurationC, json_decode(file_get_contents($contentElementsPath . '/c.json'), true)['tables']);

        $configurationD = [
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
                'tca' => [
                    'tx_mask_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'type' => 'palette',
                        'key' => 'palette',
                        'fullKey' => 'tx_mask_palette',
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
                        'type' => 'inline',
                        'key' => 'inline',
                        'fullKey' => 'tx_mask_inline',
                    ],
                ],
                'palettes' => [
                    'tx_mask_palette' => [
                        'label' => 'Palette 1',
                        'description' => 'Description for palette 1',
                        'showitem' => ['tx_mask_inline'],
                    ],
                ],
            ],
        ];

        self::assertEquals($configurationD, json_decode(file_get_contents($contentElementsPath . '/d.json'), true)['tables']);

        $configurationE = [
            'tt_content' => [
                'elements' => [
                    'e' => [
                        'key' => 'e',
                        'label' => 'E',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            0 => 'tx_mask_inline_e',
                        ],
                        'labels' => [
                            0 => 'Inline',
                        ],
                        'descriptions' => [
                            0 => 'Description for inline 1',
                        ],
                        'sorting' => 3,
                        'colorOverlay' => '',
                        'iconOverlay' => '',
                    ],
                ],
                'tca' => [
                    'tx_mask_inline_e' => [
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
                        'fullKey' => 'tx_mask_inline_e',
                        'type' => 'inline',
                        'key' => 'inline',
                    ],
                ],
            ],
            'tx_mask_inline_e' => [
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Field',
                        'type' => 'string',
                        'key' => 'field',
                        'fullKey' => 'tx_mask_field',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 1,
                    ],
                    'tx_mask_inline_inner' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'label' => 'Inline Inner',
                        'type' => 'inline',
                        'key' => 'inline_inner',
                        'fullKey' => 'tx_mask_inline_inner',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 2,
                    ],
                    'tx_mask_inline_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'label' => 'Inline Palette',
                        'type' => 'palette',
                        'key' => 'inline_palette',
                        'fullKey' => 'tx_mask_inline_palette',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 3,
                    ],
                    'tx_mask_inline_palette_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Inline Palette Field',
                        'type' => 'string',
                        'key' => 'inline_palette_field',
                        'fullKey' => 'tx_mask_inline_palette_field',
                        'inPalette' => 1,
                        'inlineParent' => 'tx_mask_inline_palette',
                        'order' => 1,
                    ],
                ],
                'sql' => [
                    'tx_mask_field' => [
                        'tx_mask_inline_e' => [
                            'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_inline_palette_field' => [
                        'tx_mask_inline_e' => [
                            'tx_mask_inline_palette_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'palettes' => [
                    'tx_mask_inline_palette' => [
                        'label' => 'Inline Palette',
                        'description' => '',
                        'showitem' => [
                            0 => 'tx_mask_inline_palette_field',
                        ],
                    ],
                ],
            ],
            'tx_mask_inline_inner' => [
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Field',
                        'type' => 'string',
                        'key' => 'field',
                        'fullKey' => 'tx_mask_field',
                        'inlineParent' => 'tx_mask_inline_inner',
                        'order' => 1,
                    ],
                    'tx_mask_inline_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'label' => 'Inline Palette',
                        'type' => 'palette',
                        'key' => 'inline_palette',
                        'fullKey' => 'tx_mask_inline_palette',
                        'inlineParent' => 'tx_mask_inline_inner',
                        'order' => 2,
                    ],
                    'tx_mask_inline_palette_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Inline Palette Field',
                        'type' => 'string',
                        'key' => 'inline_palette_field',
                        'fullKey' => 'tx_mask_inline_palette_field',
                        'inPalette' => 1,
                        'inlineParent' => 'tx_mask_inline_palette',
                        'order' => 1,
                    ],
                ],
                'sql' => [
                    'tx_mask_field' => [
                        'tx_mask_inline_inner' => [
                            'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_inline_palette_field' => [
                        'tx_mask_inline_inner' => [
                            'tx_mask_inline_palette_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'palettes' => [
                    'tx_mask_inline_palette' => [
                        'label' => 'Inline Palette',
                        'description' => '',
                        'showitem' => [
                            0 => 'tx_mask_inline_palette_field',
                        ],
                    ],
                ],
            ],
        ];

        self::assertEquals($configurationE, json_decode(file_get_contents($contentElementsPath . '/e.json'), true)['tables']);
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
            'tx_mask_inline_e' => [
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Field',
                        'type' => 'string',
                        'key' => 'field',
                        'fullKey' => 'tx_mask_field',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 1,
                    ],
                    'tx_mask_inline_inner' => [
                        'config' => [
                            'type' => 'inline',
                        ],
                        'label' => 'Inline Inner',
                        'type' => 'inline',
                        'key' => 'inline_inner',
                        'fullKey' => 'tx_mask_inline_inner',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 2,
                    ],
                    'tx_mask_inline_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'label' => 'Inline Palette',
                        'type' => 'palette',
                        'key' => 'inline_palette',
                        'fullKey' => 'tx_mask_inline_palette',
                        'inlineParent' => 'tx_mask_inline_e',
                        'order' => 3,
                    ],
                    'tx_mask_inline_palette_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Inline Palette Field',
                        'type' => 'string',
                        'key' => 'inline_palette_field',
                        'fullKey' => 'tx_mask_inline_palette_field',
                        'inPalette' => 1,
                        'inlineParent' => 'tx_mask_inline_palette',
                        'order' => 1,
                    ],
                ],
                'sql' => [
                    'tx_mask_field' => [
                        'tx_mask_inline_e' => [
                            'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_inline_palette_field' => [
                        'tx_mask_inline_e' => [
                            'tx_mask_inline_palette_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'palettes' => [
                    'tx_mask_inline_palette' => [
                        'label' => 'Inline Palette',
                        'description' => '',
                        'showitem' => [
                            0 => 'tx_mask_inline_palette_field',
                        ],
                    ],
                ],
            ],
            'tx_mask_inline_inner' => [
                'tca' => [
                    'tx_mask_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Field',
                        'type' => 'string',
                        'key' => 'field',
                        'fullKey' => 'tx_mask_field',
                        'inlineParent' => 'tx_mask_inline_inner',
                        'order' => 1,
                    ],
                    'tx_mask_inline_palette' => [
                        'config' => [
                            'type' => 'palette',
                        ],
                        'label' => 'Inline Palette',
                        'type' => 'palette',
                        'key' => 'inline_palette',
                        'fullKey' => 'tx_mask_inline_palette',
                        'inlineParent' => 'tx_mask_inline_inner',
                        'order' => 2,
                    ],
                    'tx_mask_inline_palette_field' => [
                        'config' => [
                            'type' => 'input',
                        ],
                        'label' => 'Inline Palette Field',
                        'type' => 'string',
                        'key' => 'inline_palette_field',
                        'fullKey' => 'tx_mask_inline_palette_field',
                        'inPalette' => 1,
                        'inlineParent' => 'tx_mask_inline_palette',
                        'order' => 1,
                    ],
                ],
                'sql' => [
                    'tx_mask_field' => [
                        'tx_mask_inline_inner' => [
                            'tx_mask_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                    'tx_mask_inline_palette_field' => [
                        'tx_mask_inline_inner' => [
                            'tx_mask_inline_palette_field' => 'varchar(255) DEFAULT \'\' NOT NULL',
                        ],
                    ],
                ],
                'palettes' => [
                    'tx_mask_inline_palette' => [
                        'label' => 'Inline Palette',
                        'description' => '',
                        'showitem' => [
                            0 => 'tx_mask_inline_palette_field',
                        ],
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
                        ],
                        'labels' => [
                            'A',
                            'Repeat1',
                        ],
                        'descriptions' => [
                            '',
                            'description for field tx_mask_repeat1',
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
                    'e' => [
                        'key' => 'e',
                        'label' => 'E',
                        'description' => '',
                        'shortLabel' => '',
                        'color' => '#000000',
                        'icon' => '',
                        'columns' => [
                            0 => 'tx_mask_inline_e',
                        ],
                        'labels' => [
                            0 => 'Inline',
                        ],
                        'descriptions' => [
                            0 => 'Description for inline 1',
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
                    'tx_mask_inline_e' => [
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
                        'fullKey' => 'tx_mask_inline_e',
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
        $this->expectDeprecationMessage('Expected content_elements_folder to be a correct file system path. The value "" was given.');
        $this->expectExceptionCode(1639218892);
        $jsonSplitLoader = new JsonSplitLoader(['content_elements_folder' => '../folder'], new MigrationManager([]));
        $jsonSplitLoader->load();
    }
}
