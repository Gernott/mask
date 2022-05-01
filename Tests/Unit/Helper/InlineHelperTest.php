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

namespace MASK\Mask\Tests\Unit\Helper;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Helper\InlineHelper;
use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use Prophecy\Argument;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class InlineHelperTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;

    public function addFilesToDataDataProvider(): array
    {
        return [
            'file fieled is filled with file reference' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_file',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_file' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'file',
                                'options' => 'file',
                            ],
                        ],
                    ],
                ],
                'tx_mask_file',
                [
                    'uid' => 1,
                    'tx_mask_file' => 1,
                ],
                'tt_content',
            ],
            'standard file field ist filled' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'assets',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [],
                    ],
                ],
                'assets',
                [
                    'uid' => 1,
                    'assets' => 1,
                ],
                'tt_content',
            ],
        ];
    }

    /**
     * @dataProvider addFilesToDataDataProvider
     * @test
     */
    public function addFilesToData(array $json, string $key, array $data, string $table): void
    {
        $GLOBALS['TCA']['tt_content']['columns'] = [
            'media' => [
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'sys_file_reference',
                ],
            ],
            'assets' => [
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'sys_file_reference',
                ],
            ],
            'image' => [
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'sys_file_reference',
                ],
            ],
        ];

        $backendLayoutRepository = $this->prophesize(BackendLayoutRepository::class);

        $fileRepository = $this->prophesize(FileRepository::class);
        $fileReference = $this->prophesize(FileReference::class);
        $fileRepository->findByRelation(Argument::cetera())->willReturn([$fileReference]);
        GeneralUtility::setSingletonInstance(FileRepository::class, $fileRepository->reveal());

        $inlineHelper = new InlineHelper(TableDefinitionCollection::createFromArray($json), $backendLayoutRepository->reveal());
        $inlineHelper->addFilesToData($data, $table);

        self::assertInstanceOf(FileReference::class, $data[$key][0]);
    }

    public function addIrreToData_tt_contentDataProvider(): array
    {
        return [
            'Inline field is added' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_repeat',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'repeat',
                            ],
                        ],
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'mask_element_1',
                    'tx_mask_repeat' => 2,
                ],
                'tt_content',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ],
                ],
            ],
            'Inline field is added if in palette' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_palette',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_palette' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'key' => 'palette',
                            ],
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'inlineParent' => [
                                    'element_1' => 'tx_mask_palette',
                                ],
                                'key' => 'repeat',
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette' => [
                                'showitem' => [
                                    'tx_mask_repeat',
                                ],
                            ],
                        ],
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'mask_element_1',
                    'tx_mask_repeat' => 2,
                ],
                'tt_content',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider addIrreToData_tt_contentDataProvider
     * @test
     * @todo refactor to functional test
     */
    public function addIrreToData_tt_content(array $json, string $key, array $data, string $table, array $inlineElements): void
    {
        $backendLayoutRepository = $this->prophesize(BackendLayoutRepository::class);

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [TableDefinitionCollection::createFromArray($json), $backendLayoutRepository->reveal()]
        );
        $inlineHelper->expects(self::any())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);

        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToData_pagesDataProvider(): array
    {
        return [
            'Inline field is added' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_repeat',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'repeat',
                            ],
                        ],
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2,
                ],
                'pages',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider addIrreToData_pagesDataProvider
     * @test
     */
    public function addIrreToData_pages(array $json, string $key, array $data, string $table, array $inlineElements): void
    {
        $backendLayoutRepository = $this->getMockBuilder(BackendLayoutRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $backendLayoutRepository->expects(self::once())->method('findIdentifierByPid')->willReturn('pagets__element_1');

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [TableDefinitionCollection::createFromArray($json), $backendLayoutRepository]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);

        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToDataNoBeLayoutDataProvider(): array
    {
        return [
            'Inline field is added' => [
                [
                    'pages' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_repeat',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'repeat',
                            ],
                        ],
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2,
                ],
                'pages',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider addIrreToDataNoBeLayoutDataProvider
     * @test
     */
    public function addIrreToDataNoBeLayout(array $json, string $key, array $data, string $table, array $inlineElements): void
    {
        $backendLayoutRepository = $this->prophesize(BackendLayoutRepository::class);

        // No backend layout found
        $backendLayoutRepository->findIdentifierByPid(Argument::any())->willReturn('');

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [TableDefinitionCollection::createFromArray($json), $backendLayoutRepository->reveal()]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);
        self::assertSame($inlineElements, $data[$key]);
    }

    public function addIrreToDataToInlineFieldDataProvider(): array
    {
        return [
            'Inline field is added to another inline field' => [
                [
                    'tx_mask_repeat_parent' => [
                        'elements' => [
                            'element_1' => [
                                'key' => 'element_1',
                                'label' => 'Element 1',
                                'columns' => [
                                    'tx_mask_repeat',
                                ],
                                'labels' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_repeat' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'key' => 'repeat',
                            ],
                        ],
                    ],
                    'tx_mask_repeat' => [
                        'tca' => [
                            'children_1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'children_1',
                                'label' => 'Children 1',
                            ],
                        ],
                    ],
                ],
                'tx_mask_repeat',
                [
                    'uid' => 1,
                    'CType' => 'tx_mask_element_1',
                    'tx_mask_repeat' => 2,
                ],
                'tx_mask_repeat_parent',
                [
                    [
                        'uid' => 123,
                    ],
                    [
                        'uid' => 123,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider addIrreToDataToInlineFieldDataProvider
     * @test
     */
    public function addIrreToDataToInlineField(array $json, string $key, array $data, string $table, array $inlineElements): void
    {
        $backendLayoutRepository = $this->prophesize(BackendLayoutRepository::class);

        $inlineHelper = $this->getAccessibleMock(
            InlineHelper::class,
            ['getInlineElements'],
            [TableDefinitionCollection::createFromArray($json), $backendLayoutRepository->reveal()]
        );
        $inlineHelper->expects(self::once())->method('getInlineElements')->willReturn($inlineElements);
        $inlineHelper->addIrreToData($data, $table);

        self::assertSame($inlineElements, $data[$key]);
    }
}
