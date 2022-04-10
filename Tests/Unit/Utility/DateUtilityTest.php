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

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Utility\DateUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class DateUtilityTest extends BaseTestCase
{
    /**
     * @test
     */
    public function isOldDateFormat(): void
    {
        self::assertTrue(DateUtility::isOldDateFormat('2020-05-01'));
        self::assertFalse(DateUtility::isOldDateFormat('10-12-2021'));
    }

    /**
     * @test
     */
    public function convertOldToNewFormat(): void
    {
        self::assertSame('01-01-2010', DateUtility::convertOldToNewFormat('date', '2010-01-01'));
        self::assertSame('10:10 01-01-2010', DateUtility::convertOldToNewFormat('datetime', '10:10 2010-01-01'));
    }

    /**
     * @test
     */
    public function convertStringToTimestampByDbType(): void
    {
        self::assertSame((new \DateTime('2021-02-12'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('date', '12-02-2021'));
        self::assertSame((new \DateTime('2021-02-12'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('date', '2021-02-12'));
        self::assertSame((new \DateTime('2021-02-12 10:10'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('datetime', '10:10 12-02-2021'));
        self::assertSame((new \DateTime('2021-02-12 10:10'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('datetime', '2021-02-12 10:10'));
    }

    /**
     * @test
     */
    public function convertTimestampToDate(): void
    {
        self::assertSame('13-02-2021', DateUtility::convertTimestampToDate('date', (new \DateTime('2021-02-13'))->getTimestamp()));
        self::assertSame('10:10 13-02-2021', DateUtility::convertTimestampToDate('datetime', (new \DateTime('2021-02-13 10:10'))->getTimestamp()));
        self::assertSame('10:10', DateUtility::convertTimestampToDate('time', (new \DateTime('10:10'))->getTimestamp()));
        self::assertSame('10:10:30', DateUtility::convertTimestampToDate('timesec', (new \DateTime('10:10:30'))->getTimestamp()));
    }
}
