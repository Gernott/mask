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
    protected bool $resetSingletonInstances = true;

    public static function createFromArrayWorksOnLegacyFormatDataProvider(): iterable
    {
        yield 'Legacy file field format with "options: file" and no config type works.' => [
            'json' => [
                'options' => 'file',
                'key' => 'image',
                'config' => [
                    'filter' => [
                        [
                            'parameters' => [
                                'allowedFileExtensions' => 'jpg',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'config' => [
                    'minitems' => '',
                ],
                'key' => 'image',
                'fullKey' => 'tx_mask_image',
                'type' => 'file',
                'allowedFileExtensions' => 'jpg',
                'imageoverlayPalette' => 1,
            ],
        ];

        yield 'Blank options are removed' => [
            'json' => [
                'config' => [
                    'type' => 'input',
                ],
                'type' => 'string',
                'key' => 'field1',
                'foo' => '',
                'bar' => 'baz',
            ],
            'expected' => [
                'config' => [
                    'type' => 'input',
                ],
                'type' => 'string',
                'key' => 'field1',
                'fullKey' => 'tx_mask_field1',
                'bar' => 'baz',
            ],
        ];

        yield 'Allowed blank options by type not removed' => [
            'json' => [
                'config' => [
                    'type' => 'slug',
                    'generatorOptions' => [
                        'replacements' => [
                            'foo' => '',
                        ],
                    ],
                ],
                'type' => 'slug',
                'key' => 'field1',
            ],
            'expected' => [
                'config' => [
                    'type' => 'slug',
                    'generatorOptions' => [
                        'replacements' => [
                            'foo' => '',
                        ],
                    ],
                ],
                'type' => 'slug',
                'key' => 'field1',
                'fullKey' => 'tx_mask_field1',
            ],
        ];

        yield '#94765: levelLinksPosition "none" migrated in TYPO3 v11 to showNewRecordLink' => [
            'json' => [
                'config' => [
                    'type' => 'inline',
                    'appearance' => [
                        'levelLinksPosition' => 'none',
                    ],
                ],
                'type' => 'inline',
                'key' => 'inline1',
                'fullKey' => 'tx_mask_inline1',
            ],
            'expected' => [
                'config' => [
                    'type' => 'inline',
                    'appearance' => [
                        'levelLinksPosition' => 'top',
                        'showNewRecordLink' => 0,
                    ],
                ],
                'type' => 'inline',
                'key' => 'inline1',
                'fullKey' => 'tx_mask_inline1',
            ],
        ];

        yield '#94406: fileFolderConfig migration (only TYPO3 v11).' => [
            'json' => [
                'config' => [
                    'type' => 'select',
                    'fileFolder' => 'EXT:some_extension/some/folder/',
                    'fileFolder_extList' => 'jpg,png',
                    'fileFolder_recursions' => 10,
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
            'expected' => [
                'config' => [
                    'type' => 'select',
                    'fileFolderConfig' => [
                        'folder' => 'EXT:some_extension/some/folder/',
                        'allowedExtensions' => 'jpg,png',
                        'depth' => 10,
                    ],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
        ];

        yield 'arrays set to stop recursion are not checked for empty values inside' => [
            'json' => [
                'config' => [
                    'type' => 'select',
                    'itemGroups' => [
                        'group1' => '',
                    ],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
            'expected' => [
                'config' => [
                    'type' => 'select',
                    'itemGroups' => [
                        'group1' => '',
                    ],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
        ];

        yield 'arrays set to stop recursion can themselves be removed though' => [
            'json' => [
                'config' => [
                    'type' => 'select',
                    'itemGroups' => [],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
            'expected' => [
                'config' => [
                    'type' => 'select',
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
        ];

        yield 'unset select item array keys are filled with empty strings' => [
            'json' => [
                'config' => [
                    'type' => 'select',
                    'items' => [
                        [
                            'label' => 'Label 1',
                            'value' => 'value1',
                        ],
                        [
                            'label' => 'Label 2',
                            'value' => 'value2',
                            'icon' => '',
                        ],
                        [
                            'label' => 'Label 3',
                            'value' => 'value3',
                            'icon' => '',
                            'group' => '',
                        ],
                    ],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
            'expected' => [
                'config' => [
                    'type' => 'select',
                    'items' => [
                        [
                            'label' => 'Label 1',
                            'value' => 'value1',
                            'icon' => '',
                            'group' => '',
                            'description' => '',
                        ],
                        [
                            'label' => 'Label 2',
                            'value' => 'value2',
                            'icon' => '',
                            'group' => '',
                            'description' => '',
                        ],
                        [
                            'label' => 'Label 3',
                            'value' => 'value3',
                            'icon' => '',
                            'group' => '',
                            'description' => '',
                        ],
                    ],
                ],
                'type' => 'select',
                'key' => 'select',
                'fullKey' => 'tx_mask_select',
            ],
        ];

        yield 'The core field bodytext without any type defined interpreted as richtext' => [
            'json' => [
                'key' => 'bodytext',
            ],
            'expected' => [
                'key' => 'bodytext',
                'fullKey' => 'bodytext',
                'coreField' => 1,
                'type' => 'richtext',
            ],
        ];

        yield 'The core field bodytext type can be set by bodytextTypeByElement per element' => [
            'json' => [
                'key' => 'bodytext',
                'type' => 'richtext',
                'bodytextTypeByElement' => [
                    'element1' => 'text',
                    'element2' => 'richtext',
                ],
            ],
            'expected' => [
                'key' => 'bodytext',
                'fullKey' => 'bodytext',
                'coreField' => 1,
                'bodytextTypeByElement' => [
                    'element1' => 'text',
                    'element2' => 'richtext',
                ],
            ],
        ];

        yield 'Old Mask file fields without config converted to file field with dummy config content.' => [
            'json' => [
                'key' => 'image',
                'options' => 'file',
            ],
            'expected' => [
                'config' => [
                    'minitems' => '',
                ],
                'key' => 'image',
                'fullKey' => 'tx_mask_image',
                'type' => 'file',
                'imageoverlayPalette' => 1,
            ],
        ];

        yield 'Legacy Timestamp eval is moved to "format' => [
            'json' => [
                'config' => [
                    'type' => 'datetime',
                    'eval' => 'date,foo',
                ],
                'key' => 'timestamp',
                'type' => 'timestamp',
            ],
            'expected' => [
                'config' => [
                    'type' => 'datetime',
                    'format' => 'date',
                    'eval' => 'foo',
                ],
                'key' => 'timestamp',
                'fullKey' => 'tx_mask_timestamp',
                'type' => 'timestamp',
            ],
        ];

        yield 'If eval is empty, nothing happens.' => [
            'json' => [
                'config' => [
                    'type' => 'datetime',
                    'format' => 'date',
                ],
                'key' => 'timestamp',
                'type' => 'timestamp',
            ],
            'expected' => [
                'config' => [
                    'type' => 'datetime',
                    'format' => 'date',
                ],
                'key' => 'timestamp',
                'fullKey' => 'tx_mask_timestamp',
                'type' => 'timestamp',
            ],
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
                                    'mode' => 'wizard',
                                ],
                            ],
                            'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
                            'params' => [
                                'blindLinkOptions' => 'page,file',
                                'allowedExtensions' => 'jpg,png',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'input',
                    'allowedTypes' => ['folder', 'url', 'email', 'record', 'telephone'],
                    'appearance' => [
                        'allowedFileExtensions' => ['jpg', 'png'],
                    ],
                ],
            ],
        ];

        yield 'Legacy inputLink is migrated to type link' => [
            'json' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputLink',
                ],
            ],
            'expected' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'link',
                ],
            ],
        ];

        yield 'Legacy Link fieldControl is migrated.' => [
            'json' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'link',
                    'fieldControl' => [
                        'linkPopup' => [
                            'options' => [
                                'blindLinkOptions' => 'page,file',
                                'allowedExtensions' => 'jpg,png',
                            ],
                        ],
                    ],
                ],
            ],
            'expected' => [
                'key' => 'link',
                'fullKey' => 'tx_mask_link',
                'type' => 'link',
                'config' => [
                    'type' => 'link',
                    'allowedTypes' => ['folder', 'url', 'email', 'record', 'telephone'],
                    'appearance' => [
                        'allowedFileExtensions' => ['jpg', 'png'],
                    ],
                ],
            ],
        ];

        yield 'Legacy E-Mail field is migrated' => [
            'json' => [
                'key' => 'email',
                'fullKey' => 'tx_mask_email',
                'type' => 'string',
                'config' => [
                    'type' => 'input',
                    'eval' => 'email',
                ],
            ],
            'expected' => [
                'key' => 'email',
                'fullKey' => 'tx_mask_email',
                'type' => 'email',
                'config' => [
                    'type' => 'email',
                ],
            ],
        ];

        yield 'Legacy folder field defined in group with internal_type is migrated' => [
            'json' => [
                'key' => 'folder_field',
                'fullKey' => 'tx_mask_folder_field',
                'type' => 'group',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'folder',
                ],
            ],
            'expected' => [
                'key' => 'folder_field',
                'fullKey' => 'tx_mask_folder_field',
                'type' => 'folder',
                'config' => [
                    'type' => 'folder',
                ],
            ],
        ];

        yield 'Legacy eval=required and eval=null migrated' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'string',
                'config' => [
                    'type' => 'input',
                    'eval' => 'required,null,trim',
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'string',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                    'required' => 1,
                    'nullable' => 1,
                ],
            ],
        ];

        yield 'Legacy indexed keys for type=select migrated' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'select',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['aLabel', 0, 'icon-identifier', 'mask', 'description'],
                        ['aLabel 2', 1, 'icon-identifier', 'mask', 'description'],
                    ],
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'select',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'aLabel',
                            'value' => 0,
                            'icon' => 'icon-identifier',
                            'group' => 'mask',
                            'description' => 'description',
                        ],
                        [
                            'label' => 'aLabel 2',
                            'value' => 1,
                            'icon' => 'icon-identifier',
                            'group' => 'mask',
                            'description' => 'description',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Legacy indexed keys for type=radio migrated' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'radio',
                'config' => [
                    'type' => 'radio',
                    'items' => [
                        ['aLabel', 0],
                        ['aLabel 2', 1],
                    ],
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'radio',
                'config' => [
                    'type' => 'radio',
                    'items' => [
                        ['label' => 'aLabel', 'value' => 0],
                        ['label' => 'aLabel 2', 'value' => 1],
                    ],
                ],
            ],
        ];

        yield 'Legacy indexed keys for type=check migrated' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'check',
                'config' => [
                    'type' => 'check',
                    'items' => [
                        ['aLabel', 'invertStateDisplay' => 1],
                        ['aLabel 2'],
                    ],
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'check',
                'config' => [
                    'type' => 'check',
                    'items' => [
                        ['label' => 'aLabel', 'invertStateDisplay' => true],
                        ['label' => 'aLabel 2'],
                    ],
                ],
            ],
        ];

        yield 'Type Number removes eval=int' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'integer',
                'config' => [
                    'eval' => 'int',
                    'type' => 'number',
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'integer',
                'config' => [
                    'type' => 'number',
                ],
            ],
        ];

        yield 'Type Float removes eval=double2' => [
            'json' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'integer',
                'config' => [
                    'eval' => 'double2',
                    'type' => 'number',
                    'format' => 'decimal',
                ],
            ],
            'expected' => [
                'key' => 'afield',
                'fullKey' => 'tx_mask_afield',
                'type' => 'integer',
                'config' => [
                    'type' => 'number',
                    'format' => 'decimal',
                ],
            ],
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

    /**
     * @test
     */
    public function defaultMinitemsMaxitemsNotAddedForCoreFields(): void
    {
        $input = [
            'coreField' => 1,
            'key' => 'image',
            'type' => 'file',
        ];

        $expected = [
            'coreField' => 1,
            'key' => 'image',
            'fullKey' => 'image',
            'type' => 'file',
            'imageoverlayPalette' => 1,
        ];

        self::assertEquals($expected, TcaFieldDefinition::createFromFieldArray($input)->toArray());
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function hasTypeReturnsTrueIfTypeCanBeFoundDataProvider(): iterable
    {
        yield 'Mask field with type attribute' => [
            'json' => [
                'config' => [
                    'type' => 'string',
                ],
                'key' => 'field1',
                'type' => 'string',
            ],
            'expected' => true,
            'elementKey' => '',
        ];

        yield 'Mask field without type attribute resolved by config' => [
            'json' => [
                'config' => [
                    'type' => 'string',
                ],
                'key' => 'field1',
            ],
            'expected' => true,
            'elementKey' => '',
        ];

        yield 'Core field without type attribute' => [
            'json' => [
                'key' => 'corefield',
            ],
            'expected' => false,
            'elementKey' => '',
        ];

        yield 'Core field with type attribute' => [
            'json' => [
                'key' => 'corefield',
                'type' => 'string',
            ],
            'expected' => true,
            'elementKey' => '',
        ];

        yield 'core bodytext field without type resolved to richtext' => [
            'json' => [
                'key' => 'bodytext',
            ],
            'expected' => true,
            'elementKey' => '',
        ];

        yield 'core bodytext field with type resolved to richtext' => [
            'json' => [
                'key' => 'bodytext',
                'type' => 'richtext',
            ],
            'expected' => true,
            'elementKey' => '',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and no elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'expected' => false,
            'elementKey' => '',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and valid elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'expected' => true,
            'elementKey' => 'a',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and invalid elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'expected' => true,
            'elementKey' => 'b',
        ];
    }

    /**
     * @dataProvider hasTypeReturnsTrueIfTypeCanBeFoundDataProvider
     * @test
     * @param array<string, mixed> $json
     */
    public function hasTypeReturnsTrueIfTypeCanBeFound(array $json, bool $expected, string $elementKey): void
    {
        $tcaFieldDefinition = TcaFieldDefinition::createFromFieldArray($json);
        self::assertSame($expected, $tcaFieldDefinition->hasFieldType($elementKey));
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function getTypeReturnsTypeIfTypeCanBeFoundDataProvider(): iterable
    {
        yield 'Mask field with type attribute' => [
            'json' => [
                'config' => [
                    'type' => 'string',
                ],
                'key' => 'field1',
                'type' => 'string',
            ],
            'expected' => 'string',
            'elementKey' => '',
        ];

        yield 'Mask field without type attribute resolved by config' => [
            'json' => [
                'config' => [
                    'type' => 'string',
                ],
                'key' => 'field1',
            ],
            'expected' => 'string',
            'elementKey' => '',
        ];

        yield 'Core field with type attribute' => [
            'json' => [
                'key' => 'corefield',
                'type' => 'string',
            ],
            'expected' => 'string',
            'elementKey' => '',
        ];

        yield 'core bodytext field without type resolved to richtext' => [
            'json' => [
                'key' => 'bodytext',
            ],
            'expected' => 'richtext',
            'elementKey' => '',
        ];

        yield 'core bodytext field with type resolved to richtext' => [
            'json' => [
                'key' => 'bodytext',
                'type' => 'richtext',
            ],
            'expected' => 'richtext',
            'elementKey' => '',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and valid elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'expected' => 'richtext',
            'elementKey' => 'a',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and invalid elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'expected' => 'richtext',
            'elementKey' => 'b',
        ];
    }

    /**
     * @dataProvider getTypeReturnsTypeIfTypeCanBeFoundDataProvider
     * @test
     * @param array<string, mixed> $json
     */
    public function getTypeReturnsTypeIfTypeCanBeFound(array $json, string $expected, string $elementKey): void
    {
        $tcaFieldDefinition = TcaFieldDefinition::createFromFieldArray($json);
        self::assertSame($expected, $tcaFieldDefinition->getFieldType($elementKey)->value);
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function getTypeThrowsExceptionIfTypeCanNotBeFoundDataProvider(): iterable
    {
        yield 'Core field without type attribute' => [
            'json' => [
                'key' => 'corefield',
            ],
            'elementKey' => '',
        ];

        yield 'core bodytext field with bodytextTypeByElement set and no elementKey provided' => [
            'json' => [
                'key' => 'bodytext',
                'bodytextTypeByElement' => [
                    'a' => 'richtext',
                ],
            ],
            'elementKey' => '',
        ];
    }

    /**
     * @dataProvider getTypeThrowsExceptionIfTypeCanNotBeFoundDataProvider
     * @test
     * @param array<string, mixed> $json
     */
    public function getTypeThrowsExceptionIfTypeCanNotBeFound(array $json, string $elementKey): void
    {
        $tcaFieldDefinition = TcaFieldDefinition::createFromFieldArray($json);
        $this->expectException(\OutOfBoundsException::class);
        $tcaFieldDefinition->getFieldType($elementKey);
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function isNullableReturnsTrueIfTCADefinesNullableFieldDataProvider(): iterable
    {
        yield 'eval contains null' => [
            'json' => [
                'key' => 'field1',
                'config' => [
                    'type' => 'input',
                    'eval' => 'null,trim',
                ],
            ],
            'expected' => true,
        ];

        yield 'eval does not contain null' => [
            'json' => [
                'key' => 'field1',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim,required',
                ],
            ],
            'expected' => false,
        ];

        yield 'nullable defined' => [
            'json' => [
                'key' => 'field1',
                'config' => [
                    'type' => 'input',
                    'nullable' => 1,
                ],
            ],
            'expected' => true,
        ];
    }

    /**
     * @dataProvider isNullableReturnsTrueIfTCADefinesNullableFieldDataProvider
     * @test
     * @param array<string, mixed> $json
     */
    public function isNullableReturnsTrueIfTCADefinesNullableField(array $json, bool $expected): void
    {
        $tcaFieldDefinition = TcaFieldDefinition::createFromFieldArray($json);
        self::assertSame($expected, $tcaFieldDefinition->isNullable());
    }
}
