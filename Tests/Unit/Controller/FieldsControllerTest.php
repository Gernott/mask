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

namespace MASK\Mask\Tests\Unit\Controller;

use MASK\Mask\Controller\FieldsController;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Tests\Unit\ConfigurationLoader\FakeConfigurationLoader;
use MASK\Mask\Tests\Unit\PackageManagerTrait;
use MASK\Mask\Tests\Unit\StorageRepositoryCreatorTrait;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\TestingFramework\Core\BaseTestCase;

class FieldsControllerTest extends BaseTestCase
{
    use StorageRepositoryCreatorTrait;
    use PackageManagerTrait;

    public function setUp(): void
    {
        // Default LANG prophecy just returns incoming value as label if calling ->sL()
        $languageServiceProphecy = $this->prophesize(LanguageService::class);
        $languageServiceProphecy->sL(Argument::cetera())->willReturnArgument(0);
        $GLOBALS['LANG'] = $languageServiceProphecy->reveal();
    }

    public function loadElementDataProvider(): array
    {
        return [
            'Simple fields converted to fields array' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                    'tx_mask_field2',
                                    'header',
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2',
                                    'Core Header',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                    'Field 2 Description',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description',
                                'l10n_mode' => '',
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'eval' => 'int',
                                    'type' => 'input',
                                ],
                                'key' => 'field2',
                                'name' => 'integer',
                                'description' => 'Field 2 Description',
                                'l10n_mode' => 'exclude',
                            ],
                            'header' => [
                                'coreField' => 1,
                                'key' => 'header',
                                'name' => 'string',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                            'tx_mask_field2' => [
                                'tt_content' => [
                                    'tx_mask_field2' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'string',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                            ],
                        ],
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field2',
                            'label' => 'Field 2',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'integer',
                            'icon' => '',
                            'description' => 'Field 2 Description',
                            'tca' => [
                                'l10n_mode' => 'exclude',
                                'config.eval.null' => 0,
                            ],
                        ],
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'header',
                            'label' => 'Core Header',
                            'translatedLabel' => '',
                            'name' => 'string',
                            'icon' => '',
                            'description' => '',
                            'tca' => [],
                        ],
                    ],
                ],
            ],
            'Palette fields work' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                    'tx_mask_palette1',
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Palette 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description',
                            ],
                            'tx_mask_palette1' => [
                                'config' => [
                                    'type' => 'palette',
                                ],
                                'name' => 'palette',
                                'key' => 'palette1',
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'eval' => 'int',
                                    'type' => 'input',
                                ],
                                'key' => 'field2',
                                'name' => 'integer',
                                'description' => 'Field 2 Description',
                                'label' => [
                                    'element1' => 'Field 2',
                                ],
                                'inPalette' => 1,
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette1',
                                ],
                                'order' => [
                                    'element1' => 1,
                                ],
                            ],
                            'header' => [
                                'coreField' => 1,
                                'key' => 'header',
                                'name' => 'string',
                                'inPalette' => 1,
                                'inlineParent' => [
                                    'element1' => 'tx_mask_palette1',
                                ],
                                'order' => [
                                    'element1' => 2,
                                ],
                                'label' => [
                                    'element1' => 'Core Header',
                                ],
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                            'tx_mask_field2' => [
                                'tt_content' => [
                                    'tx_mask_field2' => 'tinytext',
                                ],
                            ],
                        ],
                        'palettes' => [
                            'tx_mask_palette1' => [
                                'label' => 'Palette 1',
                                'showitem' => [
                                    'tx_mask_field2',
                                    'header',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'string',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                            ],
                        ],
                        [
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_palette1',
                            'label' => 'Palette 1',
                            'translatedLabel' => '',
                            'name' => 'palette',
                            'icon' => '',
                            'description' => '',
                            'tca' => [],
                            'fields' => [
                                [
                                    'fields' => [],
                                    'parent' => [
                                        'parent' => [],
                                        'newField' => false,
                                        'key' => 'tx_mask_palette1',
                                        'label' => 'Palette 1',
                                        'translatedLabel' => '',
                                        'name' => 'palette',
                                        'icon' => '',
                                        'description' => '',
                                        'fields' => [],
                                        'tca' => [],
                                    ],
                                    'newField' => false,
                                    'key' => 'tx_mask_field2',
                                    'label' => 'Field 2',
                                    'translatedLabel' => '',
                                    'sql' => 'tinytext',
                                    'name' => 'integer',
                                    'icon' => '',
                                    'description' => 'Field 2 Description',
                                    'tca' => [
                                        'l10n_mode' => '',
                                        'config.eval.null' => 0,
                                    ],
                                ],
                                [
                                    'fields' => [],
                                    'parent' => [
                                        'parent' => [],
                                        'newField' => false,
                                        'key' => 'tx_mask_palette1',
                                        'label' => 'Palette 1',
                                        'translatedLabel' => '',
                                        'name' => 'palette',
                                        'icon' => '',
                                        'description' => '',
                                        'fields' => [],
                                        'tca' => [],
                                    ],
                                    'newField' => false,
                                    'key' => 'header',
                                    'label' => 'Core Header',
                                    'translatedLabel' => '',
                                    'name' => 'string',
                                    'icon' => '',
                                    'description' => '',
                                    'tca' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Inline fields work' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_inline1',
                                ],
                                'labels' => [
                                    'Inline 1',
                                ],
                                'descriptions' => [
                                    '',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_inline1' => [
                                'config' => [
                                    'type' => 'inline',
                                ],
                                'name' => 'inline',
                                'key' => 'inline1',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_inline1' => [
                                'tt_content' => [
                                    'tx_mask_inline1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                    'tx_mask_inline1' => [
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description',
                                'label' => 'Field 1',
                                'inlineParent' => 'tx_mask_inline1',
                                'order' => 1,
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'eval' => 'int',
                                    'type' => 'input',
                                ],
                                'key' => 'field2',
                                'name' => 'integer',
                                'description' => 'Field 2 Description',
                                'label' => 'Field 2',
                                'inlineParent' => 'tx_mask_inline1',
                                'order' => 1,
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tx_mask_inline1' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                            'tx_mask_field2' => [
                                'tx_mask_inline1' => [
                                    'tx_mask_field2' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_inline1',
                            'label' => 'Inline 1',
                            'translatedLabel' => '',
                            'name' => 'inline',
                            'icon' => '',
                            'description' => '',
                            'sql' => 'tinytext',
                            'tca' => [
                                'config.appearance.collapseAll' => 1,
                                'config.appearance.levelLinksPosition' => 'top',
                                'config.appearance.showPossibleLocalizationRecords' => 1,
                                'config.appearance.showAllLocalizationLink' => 1,
                                'config.appearance.showRemovedLocalizationRecords' => 1,
                                'ctrl.iconfile' => '',
                                'ctrl.label' => '',
                                'l10n_mode' => '',
                            ],
                            'fields' => [
                                [
                                    'fields' => [],
                                    'parent' => [
                                        'fields' => [],
                                        'parent' => [],
                                        'newField' => false,
                                        'key' => 'tx_mask_inline1',
                                        'label' => 'Inline 1',
                                        'translatedLabel' => '',
                                        'name' => 'inline',
                                        'icon' => '',
                                        'description' => '',
                                        'sql' => 'tinytext',
                                        'tca' => [
                                            'config.appearance.collapseAll' => 1,
                                            'config.appearance.levelLinksPosition' => 'top',
                                            'config.appearance.showPossibleLocalizationRecords' => 1,
                                            'config.appearance.showAllLocalizationLink' => 1,
                                            'config.appearance.showRemovedLocalizationRecords' => 1,
                                            'ctrl.iconfile' => '',
                                            'ctrl.label' => '',
                                            'l10n_mode' => '',
                                        ],
                                    ],
                                    'newField' => false,
                                    'key' => 'tx_mask_field1',
                                    'label' => 'Field 1',
                                    'translatedLabel' => '',
                                    'sql' => 'tinytext',
                                    'name' => 'string',
                                    'icon' => '',
                                    'description' => 'Field 1 Description',
                                    'tca' => [
                                        'l10n_mode' => '',
                                        'config.eval.null' => 0,
                                    ],
                                ],
                                [
                                    'fields' => [],
                                    'parent' => [
                                        'fields' => [],
                                        'parent' => [],
                                        'newField' => false,
                                        'key' => 'tx_mask_inline1',
                                        'label' => 'Inline 1',
                                        'translatedLabel' => '',
                                        'name' => 'inline',
                                        'icon' => '',
                                        'description' => '',
                                        'sql' => 'tinytext',
                                        'tca' => [
                                            'config.appearance.collapseAll' => 1,
                                            'config.appearance.levelLinksPosition' => 'top',
                                            'config.appearance.showPossibleLocalizationRecords' => 1,
                                            'config.appearance.showAllLocalizationLink' => 1,
                                            'config.appearance.showRemovedLocalizationRecords' => 1,
                                            'ctrl.iconfile' => '',
                                            'ctrl.label' => '',
                                            'l10n_mode' => '',
                                        ],
                                    ],
                                    'newField' => false,
                                    'key' => 'tx_mask_field2',
                                    'label' => 'Field 2',
                                    'translatedLabel' => '',
                                    'sql' => 'tinytext',
                                    'name' => 'integer',
                                    'icon' => '',
                                    'description' => 'Field 2 Description',
                                    'tca' => [
                                        'l10n_mode' => '',
                                        'config.eval.null' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Old allowedFileExtensions path works and imageoverlaypalette default 1' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                ],
                                'labels' => [
                                    'Field 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'filter' => [
                                        [
                                            'parameters' => [
                                                'allowedFileExtensions' => 'jpg',
                                            ],
                                        ],
                                    ],
                                ],
                                'options' => 'file',
                                'key' => 'field1',
                                'name' => 'file',
                                'description' => 'Field 1 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'file',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'allowedFileExtensions' => 'jpg',
                                'config.appearance.fileUploadAllowed' => 1,
                                'imageoverlayPalette' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            'CTypes loaded' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                ],
                                'labels' => [
                                    'Field 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'cTypes' => ['a', 'b'],
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'tt_content',
                                ],
                                'key' => 'field1',
                                'name' => 'content',
                                'description' => 'Field 1 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'content',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'cTypes' => [
                                    'a',
                                    'b',
                                ],
                                'config.appearance.levelLinksPosition' => 'top',
                            ],
                        ],
                    ],
                ],
            ],
            'CTypes defaults to empty array' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                ],
                                'labels' => [
                                    'Field 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'inline',
                                    'foreign_table' => 'tt_content',
                                ],
                                'key' => 'field1',
                                'name' => 'content',
                                'description' => 'Field 1 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'content',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'cTypes' => [],
                                'config.appearance.levelLinksPosition' => 'top',
                            ],
                        ],
                    ],
                ],
            ],
            'Old date formats converted to new' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                    'tx_mask_field2',
                                ],
                                'labels' => [
                                    'Field 1',
                                    'Field 2',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                    'Field 2 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'dbType' => 'date',
                                    'eval' => 'date',
                                    'renderType' => 'inputDateTime',
                                    'range' => [
                                        'lower' => '2021-01-01',
                                    ],
                                ],
                                'key' => 'field1',
                                'name' => 'date',
                                'description' => 'Field 1 Description',
                            ],
                            'tx_mask_field2' => [
                                'config' => [
                                    'type' => 'input',
                                    'dbType' => 'datetime',
                                    'eval' => 'date',
                                    'renderType' => 'inputDateTime',
                                    'range' => [
                                        'lower' => '2021-01-01 10:10',
                                    ],
                                ],
                                'key' => 'field2',
                                'name' => 'datetime',
                                'description' => 'Field 2 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                            'tx_mask_field2' => [
                                'tt_content' => [
                                    'tx_mask_field2' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'date',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                                'config.range.lower' => '01-01-2021',
                            ],
                        ],
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field2',
                            'label' => 'Field 2',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'datetime',
                            'icon' => '',
                            'description' => 'Field 2 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                                'config.range.lower' => '10:10 01-01-2021',
                            ],
                        ],
                    ],
                ],
            ],
            'Timestamp fields converted to date format' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                ],
                                'labels' => [
                                    'Field 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'eval' => 'int,date',
                                    'renderType' => 'inputDateTime',
                                    'default' => 1623081120,
                                    'range' => [
                                        'lower' => 1623081120,
                                        'upper' => 1623081120,
                                    ],
                                ],
                                'key' => 'field1',
                                'name' => 'timestamp',
                                'description' => 'Field 1 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'timestamp',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                                'config.default' => date('d-m-Y', 1623081120),
                                'config.range.lower' => date('d-m-Y', 1623081120),
                                'config.range.upper' => date('d-m-Y', 1623081120),
                                'config.eval' => 'date',
                            ],
                        ],
                    ],
                ],
            ],
            'Unknown config options removed' => [
                [
                    'tt_content' => [
                        'elements' => [
                            'element1' => [
                                'color' => '#000000',
                                'icon' => 'fa-icon',
                                'key' => 'element1',
                                'label' => 'Element 1',
                                'description' => 'Element 1 Description',
                                'columns' => [
                                    'tx_mask_field1',
                                ],
                                'labels' => [
                                    'Field 1',
                                ],
                                'descriptions' => [
                                    'Field 1 Description',
                                ],
                            ],
                        ],
                        'tca' => [
                            'tx_mask_field1' => [
                                'config' => [
                                    'type' => 'input',
                                    'foo' => 'bar',
                                    'baz' => [
                                        'fizz' => 'boo',
                                    ],
                                ],
                                'key' => 'field1',
                                'name' => 'string',
                                'description' => 'Field 1 Description',
                            ],
                        ],
                        'sql' => [
                            'tx_mask_field1' => [
                                'tt_content' => [
                                    'tx_mask_field1' => 'tinytext',
                                ],
                            ],
                        ],
                    ],
                ],
                'tt_content',
                'element1',
                [
                    'fields' => [
                        [
                            'fields' => [],
                            'parent' => [],
                            'newField' => false,
                            'key' => 'tx_mask_field1',
                            'label' => 'Field 1',
                            'translatedLabel' => '',
                            'sql' => 'tinytext',
                            'name' => 'string',
                            'icon' => '',
                            'description' => 'Field 1 Description',
                            'tca' => [
                                'l10n_mode' => '',
                                'config.eval.null' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider loadElementDataProvider
     */
    public function loadElement(array $json, string $table, string $elementKey, array $expected): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['header'] = [
            'config' => [
                'type' => 'input',
            ],
        ];

        $this->registerPackageManager();

        $iconFactory = $this->prophesize(IconFactory::class);
        $icon = new Icon();
        $icon->setMarkup('');
        $iconFactory->getIcon(Argument::cetera())->willReturn($icon);

        $configurationLoader = new FakeConfigurationLoader();

        $fieldsController = new FieldsController(TableDefinitionCollection::createFromArray($json), $iconFactory->reveal(), $configurationLoader);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getQueryParams()->willReturn(['type' => $table, 'key' => $elementKey]);

        self::assertEquals($expected, json_decode($fieldsController->loadElement($request->reveal())->getBody()->getContents(), true));
    }
}
