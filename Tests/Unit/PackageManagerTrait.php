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

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

trait PackageManagerTrait
{
    public function registerPackageManager(): void
    {
        $packageMock = $this->createMock(Package::class);
        $packageMock->method('getPackagePath')->willReturn(realpath(__DIR__ . '/../../') . '/');
        $packageManagerMock = $this->createMock(PackageManager::class);
        $packageManagerMock->method('isPackageActive')->willReturn(true)->with('mask');
        $packageManagerMock->method('getPackage')->willReturn($packageMock)->with('mask');
        // @todo Replace workaround for resolvePackagePath.
        $packageManagerMock->method('resolvePackagePath')->willReturnCallback(function ($path) {
            return Environment::getProjectPath() . str_replace('EXT:mask', '', $path);
        });

        ExtensionManagementUtility::setPackageManager($packageManagerMock);
    }
}
