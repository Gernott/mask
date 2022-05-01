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

namespace MASK\Mask\Tests\Unit\CodeGenerator;

use MASK\Mask\CodeGenerator\TyposcriptCodeGenerator;
use MASK\Mask\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TypoScriptCodeGeneratorTest extends UnitTestCase
{
    public function setUp(): void
    {
        $packageManager = $this->prophesize(PackageManager::class);
        $packageManager->isPackageActive('sitepackage')->willReturn(true);
        if (method_exists(ExtensionManagementUtility::class, 'resolvePackagePath')) {
            $packageManager->resolvePackagePath('EXT:sitepackage/Resources/Private/Mask/Templates/')->willReturn(Environment::getPublicPath() . '/typo3conf/ext/sitepackage/Resources/Private/Mask/Templates');
        }
        ExtensionManagementUtility::setPackageManager($packageManager->reveal());
    }

    public function generateSetupTyposcriptDataProvider(): iterable
    {
        yield 'all configuration, 1 element' => [
            'json' => [
                'tt_content' => [
                    'elements' => [
                        'element1' => [
                            'label' => 'Element 1',
                            'key' => 'element1',
                        ],
                        'element2' => [
                            'label' => 'Element 2',
                            'key' => 'element2',
                            'hidden' => true,
                        ],
                    ],
                ],
            ],
            'configuration' => [
                'content' => 'EXT:sitepackage/Resources/Private/Mask/Templates',
                'layouts' => 'EXT:sitepackage/Resources/Private/Mask/Layouts',
                'partials' => 'EXT:sitepackage/Resources/Private/Mask/Partials',
            ],
            'expected' =>
'lib.maskContentElement {
	templateRootPaths {
		10 = EXT:sitepackage/Resources/Private/Mask/Templates
	}
	partialRootPaths {
		10 = EXT:sitepackage/Resources/Private/Mask/Partials
	}
	layoutRootPaths {
		10 = EXT:sitepackage/Resources/Private/Mask/Layouts
	}
}

tt_content.mask_element1 =< lib.maskContentElement
tt_content.mask_element1 {
	templateName = Element1
}

',
        ];

        yield 'only template, no element' => [
            'json' => [
                'tt_content' => [
                    'elements' => [],
                ],
            ],
            'configuration' => [
                'content' => 'EXT:sitepackage/Resources/Private/Mask/Templates',
            ],
            'expected' =>
'lib.maskContentElement {
	templateRootPaths {
		10 = EXT:sitepackage/Resources/Private/Mask/Templates
	}
}

',
        ];

        yield 'no tt_content definition' => [
            'json' => [
                'pages' => [
                    'elements' => [
                        '1' => [
                            'key' => '1',
                        ],
                    ],
                ],
            ],
            'configuration' => [
                'content' => 'EXT:sitepackage/Resources/Private/Mask/Templates',
            ],
            'expected' => '',
        ];
    }

    /**
     * @test
     * @dataProvider generateSetupTyposcriptDataProvider
     */
    public function generateSetupTyposcript(array $json, array $configuration, string $expected): void
    {
        $iconRegistryProphecy = $this->prophesize(IconRegistry::class);
        $tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
        $typoScriptCodeGenerator = new TyposcriptCodeGenerator($tableDefinitionCollection, $configuration, $iconRegistryProphecy->reveal());
        self::assertSame($expected, $typoScriptCodeGenerator->generateSetupTyposcript());
    }
}
