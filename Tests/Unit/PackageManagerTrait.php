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

use Prophecy\Argument;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

trait PackageManagerTrait
{
    public function registerPackageManager(): void
    {
        $package = $this->prophesize(Package::class);
        $package->getPackagePath()->willReturn(realpath(__DIR__ . '/../../') . '/');
        $packageManager = $this->prophesize(PackageManager::class);
        $packageManager->isPackageActive('mask')->willReturn(true);
        $packageManager->getPackage('mask')->willReturn($package->reveal());
        // @todo Replace workaround for resolvePackagePath.
        if (method_exists(PackageManager::class, 'resolvePackagePath')) {
            $packageManager->resolvePackagePath(Argument::any())->will(function ($path) {
                return Environment::getProjectPath() . str_replace('EXT:mask', '', $path[0]);
            });
        }

        ExtensionManagementUtility::setPackageManager($packageManager->reveal());
    }
}
