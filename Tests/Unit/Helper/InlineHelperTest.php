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

namespace MASK\Mask\Test\Helper;

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\InlineHelper;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class InlineHelperTest extends BaseTestCase
{
    public function addFilesToDataDataProvider()
    {
        return [
            'file fieled is filled with file reference' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_file'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_file' => [
                                'options' => 'file'
                            ]
                        ]
                    ]
                ],
                'tx_mask_file',
                [
                    'uid' => 1,
                    'tx_mask_file' => 1
                ],
                'tt_content'
            ],
            'standard file field ist filled' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'assets'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => []
                    ]
                ],
                'assets',
                [
                    'uid' => 1,
                    'assets' => 1
                ],
                'tt_content'
            ]
        ];
    }

    /**
     * @param $json
     * @param $data
     * @param $table
     * @param $expected
     * @dataProvider addFilesToDataDataProvider
     * @test
     */
    public function addFilesToData($json, $key, $data, $table)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->expects(self::once())->method('load')->willReturn($json);
        $storage->expects(self::any())->method('getFormType')
            ->willReturnCallback(
                function ($arg1) use ($key) {
                    return $arg1 === $key ? FieldType::FILE : '';
                }
            );

        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileRepository = $this->getMockBuilder(FileRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileReference = $this->getMockBuilder(FileReference::class)->disableOriginalConstructor()->getMock();
        $fileRepository->expects(self::once())->method('findByRelation')->willReturn([$fileReference]);
        GeneralUtility::setSingletonInstance(FileRepository::class, $fileRepository);

        $inlineHelper = new InlineHelper($storage, $backendLayoutRepository);
        $inlineHelper->addFilesToData($data, $table);

        self::assertInstanceOf(FileReference::class, $data[$key][0]);
    }

    public function addIrreToData_tt_contentDataProvider()
    {
        return [
            'Inline field is added' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_repeat'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1'
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'mask_element_1',
                    'tx_mask_repeat' => 2
                ],
                'tt_content',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ]
                ]
            ],
            'Inline field is added if in palette' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_palette'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette'
                                ],
                                'key' => 'palette'
                            ],
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette'
                                ],
                                'key' => 'repeat'
                            ]
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'showitem' => [
                                    'tx_mask_repeat'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1'
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'mask_element_1',
                    'tx_mask_repeat' => 2
                ],
                'tt_content',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider addIrreToData_tt_contentDataProvider
     * @test
     * @param $json
     * @param $element
     * @param $key
     * @param $data
     * @param $table
     * @param $inlineElements
     */
    public function addIrreToData_tt_content($json, $element, $key, $data, $table, $inlineElements)
    {
        $storage = $this->createPartialMock(StorageRepository::class, ['load']);
        $storage->expects(self::any())->method('load')->willReturn($json);

        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [$storage, $backendLayoutRepository]
        );
        $inlineHelper->expects(self::any())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);
        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToData_pagesDataProvider()
    {
        return [
            'Inline field is added' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_repeat'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'Content'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1'
                            ]
                        ]
                    ]
                ],
                'element_1',
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2
                ],
                'pages',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider addIrreToData_pagesDataProvider
     * @test
     * @param $json
     * @param $element
     * @param $key
     * @param $data
     * @param $table
     * @param $inlineElements
     */
    public function addIrreToData_pages($json, $element, $key, $data, $table, $inlineElements)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->expects(self::once())->method('load')->willReturn($json);
        $storage->expects(self::once())->method('loadElement')->willReturn($json[$table]['elements'][$element]);
        $storage->expects(self::once())->method('getFormType')->willReturn('inline');

        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backendLayoutRepository->expects(self::once())->method('findIdentifierByPid')->willReturn('pagets__element_1');

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [$storage, $backendLayoutRepository]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);
        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToDataNoBeLayoutDataProvider()
    {
        return [
            'Inline field is added' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_repeat'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'Content'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1'
                            ]
                        ]
                    ]
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2
                ],
                'pages',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider addIrreToDataNoBeLayoutDataProvider
     * @test
     * @param $json
     * @param $key
     * @param $data
     * @param $table
     * @param $inlineElements
     */
    public function addIrreToDataNoBeLayout($json, $key, $data, $table, $inlineElements)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->expects(self::once())->method('load')->willReturn($json);
        $storage->expects(self::once())->method('getFormType')->willReturn('inline');

        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // No backend layout found
        $backendLayoutRepository->expects(self::once())->method('findIdentifierByPid')->willReturn('');

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [$storage, $backendLayoutRepository]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);
        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToDataToInlineFieldDataProvider()
    {
        return [
            'Inline field is added to another inline field' => [
                [
                    'tx_mask_repeat_parent' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'columns' => [
                                    'tx_mask_repeat'
                                ],
                                'labels' => [
                                    ''
                                ]
                            ]
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'Content'
                                ]
                            ]
                        ]
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input'
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1'
                            ]
                        ]
                    ]
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2
                ],
                'tx_mask_repeat_parent',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider addIrreToDataToInlineFieldDataProvider
     * @test
     * @param $json
     * @param $element
     * @param $key
     * @param $data
     * @param $table
     * @param $inlineElements
     */
    public function addIrreToDataToInlineField($json, $key, $data, $table, $inlineElements)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->expects(self::once())->method('load')->willReturn($json);
        $storage->expects(self::once())->method('getFormType')->willReturn('inline');

        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [$storage, $backendLayoutRepository]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);
        self::assertSame($inlineElements, $data[$key]);
    }
}
