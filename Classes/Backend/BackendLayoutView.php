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

namespace MASK\Mask\Backend;

use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;

/**
 * Backend layout for CMS
 */
class BackendLayoutView extends \TYPO3\CMS\Backend\View\BackendLayoutView
{

    /**
     * @return DataProviderContext
     */
    public function createDataProviderContext(): DataProviderContext
    {
        return parent::createDataProviderContext();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function determinePageId($tableName, array $data)
    {
        return parent::determinePageId($tableName, $data);
    }
}
