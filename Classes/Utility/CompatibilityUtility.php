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

namespace MASK\Mask\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility as CoreUtility;

class CompatibilityUtility
{
    public static function isFirstPartOfStr(string $part, string $string): bool
    {
        return function_exists('str_starts_with') ? str_starts_with($part, $string) : CoreUtility::isFirstPartOfStr($part, $string);
    }
}
