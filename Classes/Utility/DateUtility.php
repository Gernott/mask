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
    protected static $oldDatePattern = '/^\d{4}/';

    /**
     * @param string $date
     * @return bool
     */
    public static function isOldDateFormat(string $date): bool
    {
        return (bool)preg_match(self::$oldDatePattern, $date);
    }

    public static function convertOldToNewFormat(string $dbType, string $date): string
    {
        $format = self::getFormatByDbType($dbType);
        return (new \DateTime($date))->format($format);
    }

    protected static function getFormatByDbType(string $dbType): string
    {
        return ($dbType === 'date') ? 'd-m-Y' : 'H:i d-m-Y';
    }

    public static function convertStringToTimestampByDbType(string $dbType, string $dateString): int
    {
        $format = self::getFormatByDbType($dbType);
        if (self::isOldDateFormat($dateString)) {
            $dateString = self::convertOldToNewFormat($dbType, $dateString);
        }
        $date = \DateTime::createFromFormat($format, $dateString);
        if ($dbType === 'date') {
            $date->setTime(0, 0);
        }
        return $date->getTimestamp();
    }

    public static function convertTimestampToDate(string $evalDate, int $timestamp): string
    {
        $format = 'd-m-Y';
        switch ($evalDate) {
            case 'datetime':
                $format = 'H:i d-m-Y';
                break;
            case 'time':
                $format = 'H:i';
                break;
            case 'timesec':
                $format = 'H:i:s';
                break;
        }

        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        return $date->format($format);
    }
}
