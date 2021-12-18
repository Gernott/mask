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

namespace MASK\Mask\Tests\Unit\Definition;

use MASK\Mask\Definition\TcaFieldDefinition;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TcaFieldDefinitionTest extends UnitTestCase
{
    public function createFromArrayWorksOnLegacyFormatDataProvider(): iterable
    {
        yield 'Legacy file field format with "options: file" and no config type works.' => [
            'json' => [
                'options' => 'file',
                'key' => 'image',
                'config' => [
                    'filter' => [
                        [
                            'parameters' => [
                                'allowedFileExtensions' => 'jpg'
                            ]
                        ]
                    ]
                ]
            ],
            'expected' => [
                'key' => 'image',
                'fullKey' => 'tx_mask_image',
                'type' => 'file',
                'allowedFileExtensions' => 'jpg',
                'imageoverlayPalette' => 1,
            ]
        ];

        yield 'Legacy Link format (wizards) transformed to fieldControl' => [
            'json' => [
                'key' => 'link',
                'config' => [
                    'type' => 'input',
                    'wizards' => [
                        '_PADDING' => '2',
                        'link' => [
                            'type' => 'popup',
                            'title' => 'Link',
                            'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
                            'module' => [
                                'name' => 'wizard_link',
                                'urlParameters' => [
                                    'mode' => 'wizard'
                                ]
                            ],
                            'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                            'params' => [
                                'blindLinkOptions' => 'page,files',
                                'allowedExtensions' => 'jpg'
                            ]
                        ]
                    ]
                ]
            ],
            'expected' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'input',
                    'fieldControl' => [
                        'linkPopup' => [
                            'options' => [
                                'blindLinkOptions' => 'page,files',
                                'allowedExtensions' => 'jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider createFromArrayWorksOnLegacyFormatDataProvider
     * @test
     */
    public function createFromArrayWorksOnLegacyFormat(array $json, array $expected): void
    {
        self::assertEquals($expected, TcaFieldDefinition::createFromFieldArray($json)->toArray());
    }
}
