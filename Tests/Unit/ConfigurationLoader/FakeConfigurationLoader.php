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

namespace MASK\Mask\Tests\Unit\ConfigurationLoader;

use MASK\Mask\ConfigurationLoader\ConfigurationLoaderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FakeConfigurationLoader implements ConfigurationLoaderInterface
{

    /**
     * @param string $tab
     * @return array
     */
    public function loadTab(string $tab): array
    {
        return require GeneralUtility::getFileAbsFileName('EXT:mask/Tests/Unit/Fixtures/Tabs/' . $tab . '.php');
    }

    /**
     * @return array
     */
    public function loadFieldGroups(): array
    {
        return require GeneralUtility::getFileAbsFileName('EXT:mask/Tests/Unit/Fixtures/FieldGroups.php');
    }

    /**
     * @return array
     */
    public function loadTcaFields(): array
    {
        return require GeneralUtility::getFileAbsFileName('EXT:mask/Tests/Unit/Fixtures/TcaFields.php');
    }

    /**
     * @return array
     */
    public function loadDefaults(): array
    {
        return require GeneralUtility::getFileAbsFileName('EXT:mask/Tests/Unit/Fixtures/Defaults.php');
    }
}
