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

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Utility\ReusingFieldsUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class ReusingFieldsUtilityTest extends BaseTestCase
{
    public function convertTcaArrayToMinimalTcaDataProvider(): iterable
    {
        yield 'Simple Input Field' => [
            [
                'config' => [
                    'type' => 'input',
                    'max' => '1',
                ]
            ],
            [
                'config' => [
                    'type' => 'input',
                ]
            ],
        ];

        yield 'Complex Select Field' => [
            [
                'config' => [
                    'default' => 'select',
                    'fieldWizard' => [
                        'selectIcons' => [
                            'disabled' => 1
                        ]
                    ],
                    'itemGroups' => [
                        'group' => 'group'
                    ],
                    'items' => [
                        [
                            'option 1',
                            'option',
                            '',
                            'group',
                            ''
                        ],
                        [
                            'option 2',
                            'option2',
                            '',
                            '',
                            ''
                        ]
                    ],
                    'maxitems' => '1',
                    'minitems' => '0',
                    'renderType' => 'selectSingleBox',
                    'sortItems' => [
                        'value' => 'desc'
                    ],
                    'type' => 'select'
                ]
            ],
            [
                'config' => [
                    'type' => 'select',
                ]
            ],
        ];

        yield 'Media Field' => [
            [
                'allowedFileExtensions' => 'png,gif',
                'config' => [
                    'appearance' => [
                        'collapseAll' => '1',
                        'createNewRelationLinkTitle' => 'create new relation',
                        'elementBrowserEnabled' => 1,
                        'enabledControls' => [
                            'delete' => 1,
                            'dragdrop' => 1,
                            'hide' => 1,
                            'info' => 1,
                            'localize' => 1,
                            'sort' => 1
                        ],
                        'expandSingle' => 1,
                        'fileByUrlAllowed' => 1,
                        'fileUploadAllowed' => 1,
                        'useSortable' => 1
                    ],
                    'maxitems' => '100',
                    'minitems' => '0'
                ],
                'onlineMedia' => [
                    'youtube',
                    'vimeo'
                ]
            ],
            [
                'config' => [
                    'maskReusingField' => 'true',
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertTcaArrayToMinimalTcaDataProvider
     */
    public function convertTcaArrayToMinimalTcaTest(array $array, array $expected): void
    {
        self::assertSame($expected, ReusingFieldsUtility::getRealTcaConfig($array));
    }


    public function convertTcaArrayToOverrideTcaDataProvider(): iterable
    {
        yield 'Simple Input Field' => [
            [
                'config' => [
                    'type' => 'input',
                    'max' => '1',
                ]
            ],
            [
                'config' => [
                    'max' => '1',
                ]
            ],
        ];

        yield 'Complex Select Field' => [
            [
                'config' => [
                    'default' => 'select',
                    'fieldWizard' => [
                        'selectIcons' => [
                            'disabled' => 1
                        ]
                    ],
                    'itemGroups' => [
                        'group' => 'group'
                    ],
                    'items' => [
                        [
                            'option 1',
                            'option',
                            '',
                            'group',
                            ''
                        ],
                        [
                            'option 2',
                            'option2',
                            '',
                            '',
                            ''
                        ]
                    ],
                    'maxitems' => '1',
                    'minitems' => '0',
                    'renderType' => 'selectSingleBox',
                    'sortItems' => [
                        'value' => 'desc'
                    ],
                    'type' => 'select'
                ]
            ],
            [
                'config' => [
                    'default' => 'select',
                    'fieldWizard' => [
                        'selectIcons' => [
                            'disabled' => 1
                        ]
                    ],
                    'itemGroups' => [
                        'group' => 'group'
                    ],
                    'items' => [
                        [
                            'option 1',
                            'option',
                            '',
                            'group',
                            ''
                        ],
                        [
                            'option 2',
                            'option2',
                            '',
                            '',
                            ''
                        ]
                    ],
                    'maxitems' => '1',
                    'minitems' => '0',
                    'renderType' => 'selectSingleBox',
                    'sortItems' => [
                        'value' => 'desc'
                    ]
                ]
            ],
        ];

        yield 'Media Field' => [
            [
                'allowedFileExtensions' => 'png,gif',
                'config' => [
                    'appearance' => [
                        'collapseAll' => '1',
                        'createNewRelationLinkTitle' => 'create new relation',
                        'elementBrowserEnabled' => 1,
                        'enabledControls' => [
                            'delete' => 1,
                            'dragdrop' => 1,
                            'hide' => 1,
                            'info' => 1,
                            'localize' => 1,
                            'sort' => 1
                        ],
                        'expandSingle' => 1,
                        'fileByUrlAllowed' => 1,
                        'fileUploadAllowed' => 1,
                        'useSortable' => 1
                    ],
                    'maxitems' => '100',
                    'minitems' => '0'
                ],
                'onlineMedia' => [
                    'youtube',
                    'vimeo'
                ]
            ],
            [
                'config' => [
                    'appearance' => [
                        'collapseAll' => '1',
                        'createNewRelationLinkTitle' => 'create new relation',
                        'elementBrowserEnabled' => 1,
                        'enabledControls' => [
                            'delete' => 1,
                            'dragdrop' => 1,
                            'hide' => 1,
                            'info' => 1,
                            'localize' => 1,
                            'sort' => 1
                        ],
                        'expandSingle' => 1,
                        'fileByUrlAllowed' => 1,
                        'fileUploadAllowed' => 1,
                        'useSortable' => 1
                    ],
                    'maxitems' => '100',
                    'minitems' => '0'
                ],
                'allowedFileExtensions' => 'png,gif',
                'onlineMedia' => [
                    'youtube',
                    'vimeo'
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider convertTcaArrayToOverrideTcaDataProvider
     */
    public function convertTcaArrayToOverrideTcaTest(array $array, array $expected): void
    {
        self::assertSame($expected, ReusingFieldsUtility::getOverrideTcaConfig($array));
    }
}
