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

class DateUtility
{
    /**
     * @var string
     */
    protected static $oldDatePattern = '/^[0-9]{4}/';

    /**
     * @param string $date
     * @return bool
     */
    public static function isOldDateFormat(string $date): bool
    {
        return (bool)preg_match(self::$oldDatePattern, $date);
    }

    /**
     * @param string $dbType
     * @param string $date
     * @return string
     */
    public static function convertOldToNewFormat(string $dbType, string $date): string
    {
        $format = ($dbType === 'date') ? 'd-m-Y' : 'H:i d-m-Y';
        return (new \DateTime($date))->format($format);
    }
}
