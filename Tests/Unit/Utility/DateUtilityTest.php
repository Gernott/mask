<?php

namespace MASK\Mask\Test\Utility;

use MASK\Mask\Utility\DateUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class DateUtilityTest extends BaseTestCase
{
    /**
     * @test
     */
    public function isOldDateFormat()
    {
        self::assertSame(true, DateUtility::isOldDateFormat('2020-05-01'));
        self::assertSame(false, DateUtility::isOldDateFormat('10-12-2021'));
    }

    /**
     * @test
     */
    public function convertOldToNewFormat()
    {
        self::assertSame('01-01-2010', DateUtility::convertOldToNewFormat('date', '2010-01-01'));
        self::assertSame('10:10 01-01-2010', DateUtility::convertOldToNewFormat('datetime', '10:10 2010-01-01'));
    }

    /**
     * @test
     */
    public function convertStringToTimestampByDbType()
    {
        self::assertSame((new \DateTime('2021-02-12'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('date', '12-02-2021'));
        self::assertSame((new \DateTime('2021-02-12'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('date', '2021-02-12'));
        self::assertSame((new \DateTime('2021-02-12 10:10'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('datetime', '10:10 12-02-2021'));
        self::assertSame((new \DateTime('2021-02-12 10:10'))->getTimestamp(), DateUtility::convertStringToTimestampByDbType('datetime', '2021-02-12 10:10'));
    }

    /**
     * @test
     */
    public function convertTimestampToDate()
    {
        self::assertSame('13-02-2021', DateUtility::convertTimestampToDate('date', (new \DateTime('2021-02-13'))->getTimestamp()));
        self::assertSame('10:10 13-02-2021', DateUtility::convertTimestampToDate('datetime', (new \DateTime('2021-02-13 10:10'))->getTimestamp()));
        self::assertSame('10:10', DateUtility::convertTimestampToDate('time', (new \DateTime('10:10'))->getTimestamp()));
        self::assertSame('10:10:30', DateUtility::convertTimestampToDate('timesec', (new \DateTime('10:10:30'))->getTimestamp()));
    }
}
