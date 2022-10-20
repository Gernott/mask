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

class AffixUtility
{
    public const MASK_PREFIX = 'tx_mask_';
    public const MASK_CTYPE_PREFIX = 'mask_';
    public const MASK_PARENT_SUFFIX = '_parent';

    public static function hasMaskPrefix(string $table): bool
    {
        return str_starts_with($table, self::MASK_PREFIX);
    }

    public static function addMaskPrefix(string $key): string
    {
        $key = self::removeMaskPrefix($key);
        return self::MASK_PREFIX . $key;
    }

    public static function removeMaskPrefix(string $maskKey): string
    {
        if (self::hasMaskPrefix($maskKey)) {
            return substr($maskKey, strlen(self::MASK_PREFIX));
        }
        return $maskKey;
    }

    public static function hasMaskCTypePrefix(string $cType): bool
    {
        return str_starts_with($cType, self::MASK_CTYPE_PREFIX);
    }

    public static function addMaskCTypePrefix(string $key): string
    {
        $key = self::removeCTypePrefix($key);
        return self::MASK_CTYPE_PREFIX . $key;
    }

    public static function removeCTypePrefix(string $maskKey): string
    {
        if (self::hasMaskCTypePrefix($maskKey)) {
            return substr($maskKey, strlen(self::MASK_CTYPE_PREFIX));
        }
        return $maskKey;
    }

    public static function hasMaskParentSuffix(string $key): bool
    {
        return substr($key, -(strlen(self::MASK_PARENT_SUFFIX))) === self::MASK_PARENT_SUFFIX;
    }

    public static function addMaskParentSuffix(string $key): string
    {
        return $key . self::MASK_PARENT_SUFFIX;
    }

    public static function removeMaskParentSuffix(string $key): string
    {
        if (self::hasMaskParentSuffix($key)) {
            return substr($key, 0, -(strlen(self::MASK_PARENT_SUFFIX)));
        }
        return $key;
    }
}
