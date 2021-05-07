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

namespace MASK\Mask\Tests\Unit;

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Utility\GeneralUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class GeneralUtilityTest extends UnitTestCase
{
    public function getTemplatePathDataProvider()
    {
        return [
            'UpperCamelCase exists' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                false,
                null,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html'
            ],
            'File does not exist' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                'noelement',
                false,
                null,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/Noelement.html'
            ],
            'under_scored exists' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                'under_scored',
                false,
                null,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/under_scored.html'
            ],
            'Uc_first exists' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                'uc_first',
                false,
                null,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/Uc_first.html'
            ],
            'Manually configured path works' => [
                ['content' => ''],
                'upper_exists',
                false,
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html'
            ],
            'Manually configured absolute path works' => [
                ['content' => ''],
                'upper_exists',
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html'
            ],
            'Only template is returned' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                true,
                null,
                'UpperExists.html'
            ],
            'Manually configured path and only template' => [
                ['content' => ''],
                'upper_exists',
                true,
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                'UpperExists.html'
            ],
            'Empty path returns empty string' => [
                ['content' => ''],
                'does_not_exist',
                false,
                null,
                ''
            ],
            'Wrong path returns empty string' => [
                ['content' => '/does/not/exist'],
                'does_not_exist',
                false,
                null,
                ''
            ],
            'Empty element key returns empty string' => [
                ['content' => 'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/'],
                '',
                false,
                null,
                ''
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getTemplatePathDataProvider
     * @param $settings
     * @param $elementKey
     * @param $onlyTemplateName
     * @param $path
     * @param $expectedPath
     */
    public function getTemplatePath($settings, $elementKey, $onlyTemplateName, $path, $expectedPath)
    {
        $this->resetSingletonInstances = true;
        $path = GeneralUtility::getTemplatePath($settings, $elementKey, $onlyTemplateName, $path);
        self::assertSame($expectedPath, $path);
    }

    public function removeBlankOptionsDataProvider()
    {
        return [
            'Array mixed filled and empty' => [
                [
                    'key' => [
                        'option1' => 'setting',
                        'option2' => ['setting'],
                        'option3' => '',
                        'option4' => [],
                        'option5' => null,
                        'option6' => false,
                        'option7' => 0,
                        'option8' => '0',
                        'option9' => [
                            'option1' => 'setting',
                            'option2' => ['setting'],
                            'option3' => '',
                            'option4' => [],
                            'option5' => null,
                            'option6' => false,
                            'option7' => 0,
                            'option8' => '0',
                        ],
                    ],
                ],
                [
                    'key' => [
                        'option1' => 'setting',
                        'option2' => ['setting'],
                        'option5' => null,
                        'option6' => false,
                        'option7' => 0,
                        'option8' => '0',
                        'option9' => [
                            'option1' => 'setting',
                            'option2' => ['setting'],
                            'option5' => null,
                            'option6' => false,
                            'option7' => 0,
                            'option8' => '0',
                        ],
                    ],
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider removeBlankOptionsDataProvider
     * @param $array
     * @param $expected
     */
    public function removeBlankOptions($array, $expected)
    {
        self::assertSame($expected, GeneralUtility::removeBlankOptions($array));
    }

    public function isEvalValueSetDataProvider()
    {
        return [
            'Eval key exists' => [
                'the_field',
                'trim',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => 'trim, other'
                                ]
                            ]
                        ]
                    ]
                ],
                true
            ],
            'Case provided does not matter' => [
                'the_field',
                'TRIM',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => 'trim, other'
                                ]
                            ]
                        ]
                    ]
                ],
                true
            ],
            'Case in config does not matter' => [
                'the_field',
                'trim',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => 'TRIM, other'
                                ]
                            ]
                        ]
                    ]
                ],
                true
            ],
            'Eval key does not exist' => [
                'the_field',
                'int',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => 'trim, other'
                                ]
                            ]
                        ]
                    ]
                ],
                false
            ],
            'Field does not exist' => [
                'the_field',
                'trim',
                'the_table',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => 'trim, other'
                                ]
                            ]
                        ]
                    ]
                ],
                false
            ],
            'Strict compare' => [
                'the_field',
                'null',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'eval' => null
                                ]
                            ]
                        ]
                    ]
                ],
                false
            ]
        ];
    }

    /**
     * @test
     * @dataProvider isEvalValueSetDataProvider
     * @param $fieldKey
     * @param $evalValue
     * @param $type
     * @param $json
     * @param $result
     */
    public function isEvalValueSet($fieldKey, $evalValue, $type, $json, $result)
    {
        $storage = $this->getAccessibleMock(
            StorageRepository::class,
            [],
            [],
            '',
            false
        );
        $storage->method('load')->willReturn($json);
        $utility = new GeneralUtility($storage);
        self::assertSame($result, $utility->isEvalValueSet($fieldKey, $evalValue, $type));
    }

    public function isBlindLinkOptionSetDataProvider()
    {
        return [
            'Option is set' => [
                'the_field',
                'file',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'fieldControl' => [
                                        'linkPopup' => [
                                            'options' => [
                                                'blindLinkOptions' => 'file, mail, page'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                true
            ],
            'Option is not set' => [
                'the_field',
                'file',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => [
                                    'fieldControl' => [
                                        'linkPopup' => [
                                            'options' => [
                                                'blindLinkOptions' => 'mail, page'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                false
            ],
            'No config entirely' => [
                'the_field',
                'file',
                'tt_content',
                [
                    'tt_content' => [
                        'tca' => [
                            'the_field' => [
                                'config' => []
                            ]
                        ]
                    ]
                ],
                false
            ]
        ];
    }

    /**
     * @test
     * @dataProvider isBlindLinkOptionSetDataProvider
     * @param $fieldKey
     * @param $evalValue
     * @param $type
     * @param $json
     * @param $expected
     */
    public function isBlindLinkOptionSet($fieldKey, $evalValue, $type, $json, $expected)
    {
        $storage = $this->getAccessibleMock(
            StorageRepository::class,
            [],
            [],
            '',
            false
        );
        $storage->method('load')->willReturn($json);
        $utility = new GeneralUtility($storage);
        self::assertSame($expected, $utility->isBlindLinkOptionSet($fieldKey, $evalValue, $type));
    }

    public function getFirstNoneTabFieldDataProvider()
    {
        return [
            'Tab is first element' => [
                ['--div--;My Tab', 'tx_mask_the_field', 'tx_mask_another_field'],
                'tx_mask_the_field'
            ],
            'Tab is not first element' => [
                ['tx_mask_the_field', '--div--;My Tab', 'tx_mask_another_field'],
                'tx_mask_the_field'
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getFirstNoneTabFieldDataProvider
     * @param $data
     * @param $expected
     */
    public function getFirstNoneTabField($data, $expected)
    {
        self::assertSame($expected, GeneralUtility::getFirstNoneTabField($data));
    }
}
