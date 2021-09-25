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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class JsonSplitLoaderTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function load(): void
    {
        $jsonSplitLoader = new JsonSplitLoader(
            [
                'content_elements_folder' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/ContentElements',
                'backend_layouts_folder' => 'EXT:mask/Tests/Unit/Fixtures/Configuration/BackendLayouts'
            ]
        );

        self::assertEquals($this->getExpectedConfigurationArray(), $jsonSplitLoader->load()->toArray());
    }

    /**
     * @test
     */
    public function write(): void
    {
        $jsonSplitLoader = new JsonSplitLoader(
            [
                'content_elements_folder' => 'typo3temp/ContentElements',
                'backend_layouts_folder' => 'typo3temp/BackendLayouts'
            ]
        );

        $contentElementsPath = Environment::getPublicPath() . '/typo3temp/ContentElements';
        GeneralUtility::mkdir($contentElementsPath);
        $this->testFilesToDelete[] = $contentElementsPath;

        $jsonSplitLoader->write(TableDefinitionCollection::createFromArray($this->getExpectedConfigurationArray()));

        self::assertFileExists($contentElementsPath . '/a.json');
        self::assertFileExists($contentElementsPath . '/b.json');

        $configurationA = [
            'tx_mask_repeat1' => [
                'elements' => [
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tx_mask_repeat1' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'label' => 'A',
                        'type' => 'string',
                        'key' => 'a',
                        'fullKey' => 'tx_mask_a',
                        'inlineParent' => 'tx_mask_repeat1',
                        'order' => 1
                    ]
                ],
                'palettes' => [
                ]
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
                            'tx_mask_repeat1'
                        ],
                        'labels' => [
                            'A',
                            'Repeat1'
                        ]
                    ]
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tt_content' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ],
                    'tx_mask_repeat1' => [
                        'tt_content' => [
                            'tx_mask_repeat1' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input'
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
                                    'dragdrop' => 1
                                ],
                                'levelLinksPosition' => 'top',
                                'showAllLocalizationLink' => 1,
                                'showPossibleLocalizationRecords' => 1
                            ],
                            'foreign_field' => 'parentid',
                            'foreign_sortby' => 'sorting',
                            'foreign_table' => '--inlinetable--',
                            'foreign_table_field' => 'parenttable',
                            'type' => 'inline'
                        ],
                        'type' => 'inline',
                        'key' => 'repeat1',
                        'fullKey' => 'tx_mask_repeat1',
                    ]
                ],
                'palettes' => [
                ]
            ]
        ];

        self::assertSame($configurationA, json_decode(file_get_contents($contentElementsPath . '/a.json'), true));

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
                            'tx_mask_4e12de3d14bd5'
                        ],
                        'labels' => [
                            'A 2',
                            'B',
                            'Palette 1'
                        ]
                    ]
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tt_content' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ],
                    'tx_mask_b' => [
                        'tt_content' => [
                            'tx_mask_b' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'type' => 'string',
                        'key' => 'a',
                        'fullKey' => 'tx_mask_a',
                    ],
                    'tx_mask_b' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'type' => 'string',
                        'key' => 'b',
                        'fullKey' => 'tx_mask_b',
                    ],
                    'tx_mask_4e12de3d14bd5' => [
                        'config' => [
                            'type' => 'palette'
                        ],
                        'type' => 'palette',
                        'key' => '4e12de3d14bd5',
                        'fullKey' => 'tx_mask_4e12de3d14bd5'
                    ],
                    'header' => [
                        'coreField' => 1,
                        'type' => 'string',
                        'key' => 'header',
                        'fullKey' => 'header',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'b' => 'tx_mask_4e12de3d14bd5'
                        ],
                        'label' => [
                            'b' => 'Header'
                        ],
                        'order' => [
                            'b' => 1
                        ]
                    ],
                ],
                'palettes' => [
                    'tx_mask_4e12de3d14bd5' => [
                        'label' => 'Palette 1',
                        'showitem' => ['header']
                    ]
                ]
            ]
        ];

        self::assertSame($configurationB, json_decode(file_get_contents($contentElementsPath . '/b.json'), true));

        $configurationC = [
            'sys_file_reference' => [
                'sql' => [
                    'tx_mask_file' => [
                        'sys_file_reference' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ]
                ]
            ],
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
                            'tx_mask_file'
                        ],
                        'labels' => [
                            'File'
                        ]
                    ]
                ],
                'sql' => [
                    'tx_mask_file' => [
                        'tt_content' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_file' => [
                        'config' => [
                            'appearance' => [
                                'fileUploadAllowed' => 1
                            ]
                        ],
                        'type' => 'file',
                        'key' => 'file',
                        'fullKey' => 'tx_mask_file',
                        'imageoverlayPalette' => 1
                    ]
                ],
                'palettes' => []
            ]
        ];

        self::assertSame($configurationC, json_decode(file_get_contents($contentElementsPath . '/c.json'), true));
    }

    protected function getExpectedConfigurationArray(): array
    {
        return [
            'tx_mask_repeat1' => [
                'elements' => [
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tx_mask_repeat1' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'fullKey' => 'tx_mask_a',
                        'label' => 'A',
                        'type' => 'string',
                        'key' => 'a',
                        'inlineParent' => 'tx_mask_repeat1',
                        'order' => 1
                    ]
                ],
                'palettes' => [
                ]
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
                            'tx_mask_repeat1'
                        ],
                        'labels' => [
                            'A',
                            'Repeat1'
                        ]
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
                            'tx_mask_4e12de3d14bd5'
                        ],
                        'labels' => [
                            'A 2',
                            'B',
                            'Palette 1'
                        ]
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
                        ]
                    ]
                ],
                'sql' => [
                    'tx_mask_a' => [
                        'tt_content' => [
                            'tx_mask_a' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ],
                    'tx_mask_b' => [
                        'tt_content' => [
                            'tx_mask_b' => 'varchar(255) DEFAULT \'\' NOT NULL'
                        ]
                    ],
                    'tx_mask_repeat1' => [
                        'tt_content' => [
                            'tx_mask_repeat1' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ],
                    'tx_mask_file' => [
                        'tt_content' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [
                    'tx_mask_a' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'fullKey' => 'tx_mask_a',
                        'type' => 'string',
                        'key' => 'a'
                    ],
                    'tx_mask_b' => [
                        'config' => [
                            'type' => 'input'
                        ],
                        'fullKey' => 'tx_mask_b',
                        'type' => 'string',
                        'key' => 'b'
                    ],
                    'tx_mask_repeat1' => [
                        'config' => [
                            'appearance' => [
                                'collapseAll' => 1,
                                'enabledControls' => [
                                    'dragdrop' => 1
                                ],
                                'levelLinksPosition' => 'top',
                                'showAllLocalizationLink' => 1,
                                'showPossibleLocalizationRecords' => 1
                            ],
                            'foreign_field' => 'parentid',
                            'foreign_sortby' => 'sorting',
                            'foreign_table' => '--inlinetable--',
                            'foreign_table_field' => 'parenttable',
                            'type' => 'inline'
                        ],
                        'fullKey' => 'tx_mask_repeat1',
                        'type' => 'inline',
                        'key' => 'repeat1'
                    ],
                    'tx_mask_4e12de3d14bd5' => [
                        'config' => [
                            'type' => 'palette'
                        ],
                        'type' => 'palette',
                        'key' => '4e12de3d14bd5',
                        'fullKey' => 'tx_mask_4e12de3d14bd5'
                    ],
                    'header' => [
                        'coreField' => 1,
                        'type' => 'string',
                        'key' => 'header',
                        'fullKey' => 'header',
                        'inPalette' => 1,
                        'inlineParent' => [
                            'b' => 'tx_mask_4e12de3d14bd5'
                        ],
                        'label' => [
                            'b' => 'Header'
                        ],
                        'order' => [
                            'b' => 1
                        ]
                    ],
                    'tx_mask_file' => [
                        'config' => [
                            'appearance' => [
                                'fileUploadAllowed' => 1
                            ]
                        ],
                        'type' => 'file',
                        'key' => 'file',
                        'fullKey' => 'tx_mask_file',
                        'imageoverlayPalette' => 1
                    ]
                ],
                'palettes' => [
                    'tx_mask_4e12de3d14bd5' => [
                        'label' => 'Palette 1',
                        'showitem' => ['header']
                    ]
                ]
            ],
            'sys_file_reference' => [
                'elements' => [],
                'sql' => [
                    'tx_mask_file' => [
                        'sys_file_reference' => [
                            'tx_mask_file' => 'int(11) unsigned DEFAULT \'0\' NOT NULL'
                        ]
                    ]
                ],
                'tca' => [],
                'palettes' => []
            ]
        ];
    }
}
