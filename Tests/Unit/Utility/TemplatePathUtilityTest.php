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

namespace MASK\Mask\Tests\Unit;

use MASK\Mask\Utility\TemplatePathUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TemplatePathUtilityTest extends UnitTestCase
{
    public function getTemplatePathDataProvider(): iterable
    {
        return [
            'UpperCamelCase exists' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                false,
                null,
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html',
            ],
            'File does not exist' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'noelement',
                false,
                null,
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/Noelement.html',
            ],
            'under_scored exists' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'under_scored',
                false,
                null,
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/under_scored.html',
            ],
            'Uc_first exists' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'uc_first',
                false,
                null,
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/Uc_first.html',
            ],
            'Manually configured path works' => [
                ['content' => ''],
                'upper_exists',
                false,
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                false,
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html',
            ],
            'Manually configured absolute path works' => [
                ['content' => ''],
                'upper_exists',
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                false,
                Environment::getPublicPath() . '/typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/UpperExists.html',
            ],
            'Only template is returned' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                true,
                null,
                false,
                'UpperExists.html',
            ],
            'Only template without extension returned' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                true,
                null,
                true,
                'UpperExists',
            ],
            'Manually configured path and only template' => [
                ['content' => ''],
                'upper_exists',
                true,
                'typo3conf/ext/mask/Tests/Unit/Fixtures/Templates/',
                false,
                'UpperExists.html',
            ],
            'Null path returns empty string, if content is also empty' => [
                ['content' => ''],
                'does_not_exist',
                false,
                null,
                false,
                '',
            ],
            'Empty path always returns an empty string' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                'upper_exists',
                false,
                '',
                false,
                '',
            ],
            'Wrong path returns empty string' => [
                ['content' => '/does/not/exist'],
                'does_not_exist',
                false,
                null,
                false,
                '',
            ],
            'Empty element key returns empty string' => [
                ['content' => 'EXT:mask/Tests/Unit/Fixtures/Templates/'],
                '',
                false,
                null,
                false,
                '',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getTemplatePathDataProvider
     */
    public function getTemplatePath(array $settings, string $elementKey, bool $onlyTemplateName, ?string $path, bool $removeExtension, string $expectedPath): void
    {
        $packageManager = $this->prophesize(PackageManager::class);
        $packageManager->isPackageActive('mask')->willReturn(true);
        ExtensionManagementUtility::setPackageManager($packageManager->reveal());

        $this->resetSingletonInstances = true;
        $path = TemplatePathUtility::getTemplatePath($settings, $elementKey, $onlyTemplateName, $path, $removeExtension);
        self::assertSame($expectedPath, $path);
    }
}
