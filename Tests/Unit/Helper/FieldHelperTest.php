<?php

namespace MASK\Mask\Test\Helper;

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\FieldHelper;
use TYPO3\TestingFramework\Core\BaseTestCase;

class FieldHelperTest extends BaseTestCase
{
    public function getLabelDataProvider()
    {
        return [
            'Correct label is returned' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'column_2',
                'tt_content',
                'Label 2'
            ],
            'Empty string if element does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_4',
                'column_2',
                'tt_content',
                ''
            ],
            'Empty string if field does not exist' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element_1' => [
                                'labels' => [
                                    'Label 1',
                                    'Label 2',
                                    'Label 3'
                                ],
                                'columns' => [
                                    'column_1',
                                    'column_2',
                                    'column_3'
                                ]
                            ]
                        ]
                    ]
                ],
                'element_1',
                'column_4',
                'tt_content',
                ''
            ]
        ];
    }

    /**
     * @dataProvider getLabelDataProvider
     * @test
     */
    public function getLabel($json, $elementKey, $fieldKey, $type, $expected)
    {
        $settingsService = $this->getMockBuilder(SettingsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMockBuilder(StorageRepository::class)
            ->setConstructorArgs([$settingsService])
            ->getMock();

        $storage->method('load')->willReturn($json);
        $fieldHelper = new FieldHelper($storage);
        self::assertSame($expected, $fieldHelper->getLabel($elementKey, $fieldKey, $type));
    }
}
