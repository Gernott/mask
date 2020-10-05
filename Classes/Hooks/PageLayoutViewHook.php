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

namespace MASK\Mask\Hooks;

use TYPO3\CMS\Backend\View\PageLayoutView;

class PageLayoutViewHook
{
    /**
     * Allow the usage of records in colpos 999 for mask nested content elements
     *
     * @param array $params
     * @param PageLayoutView $parentObject
     * @return bool
     */
    public function contentIsUsed(array $params, PageLayoutView $parentObject): bool
    {
        if ($params['used']) {
            return true;
        }
        $record = $params['record'];
        return $record['colPos'] === 999;
    }
}
