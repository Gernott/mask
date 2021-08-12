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

    /**
     * @param string $table
     * @return bool
     */
    public static function hasMaskPrefix(string $table): bool
    {
        return strpos($table, self::MASK_PREFIX) === 0;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function addMaskPrefix(string $key): string
    {
        $key = self::removeMaskPrefix($key);
        return self::MASK_PREFIX . $key;
    }

    /**
     * Removes the tx_mask_ prefix
     *
     * @param string $maskKey
     * @return string
     */
    public static function removeMaskPrefix(string $maskKey): string
    {
        if (self::hasMaskPrefix($maskKey)) {
            return substr($maskKey, strlen(self::MASK_PREFIX));
        }
        return $maskKey;
    }

    /**
     * @param string $cType
     * @return bool
     */
    public static function hasMaskCTypePrefix(string $cType): bool
    {
        return strpos($cType, self::MASK_CTYPE_PREFIX) === 0;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function addMaskCTypePrefix(string $key): string
    {
        $key = self::removeCTypePrefix($key);
        return self::MASK_CTYPE_PREFIX . $key;
    }

    /**
     * Removes the mask_ prefix used for cType
     *
     * @param string $maskKey
     * @return string
     */
    public static function removeCTypePrefix(string $maskKey): string
    {
        if (self::hasMaskCTypePrefix($maskKey)) {
            return substr($maskKey, strlen(self::MASK_CTYPE_PREFIX));
        }
        return $maskKey;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function hasMaskParentSuffix(string $key): bool
    {
        return substr($key, -(strlen(self::MASK_PARENT_SUFFIX))) === self::MASK_PARENT_SUFFIX;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function addMaskParentSuffix(string $key): string
    {
        return $key . self::MASK_PARENT_SUFFIX;
    }

    /**
     * @param string $key
     * @return string
     */
    public static function removeMaskParentSuffix(string $key): string
    {
        if (self::hasMaskParentSuffix($key)) {
            return substr($key, 0, -(strlen(self::MASK_PARENT_SUFFIX)));
        }
        return $key;
    }
}
