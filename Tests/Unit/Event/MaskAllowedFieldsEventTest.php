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

namespace MASK\Mask\Tests\Unit\Event;

use MASK\Mask\Event\MaskAllowedFieldsEvent;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MaskAllowedFieldsEventTest extends UnitTestCase
{
    /**
     * @test
     */
    public function fieldsCanBeFetched(): void
    {
        $allowedFields = [
            'tt_content' => [
                'foo',
                'bar',
            ],
        ];

        $event = new MaskAllowedFieldsEvent($allowedFields);

        self::assertSame($allowedFields, $event->getAllowedFields());
    }

    /**
     * @test
     */
    public function fieldsCanBeReplaced(): void
    {
        $allowedFields = [
            'tt_content' => [
                'foo',
                'bar',
            ],
        ];

        $expected = [
            'tt_content' => [
                'oof',
                'rab',
            ],
        ];

        $event = new MaskAllowedFieldsEvent($allowedFields);
        $event->setAllowedFields($expected);

        self::assertSame($expected, $event->getAllowedFields());
    }

    /**
     * @test
     */
    public function fieldCanBeAdded(): void
    {
        $allowedFields = [
            'tt_content' => [
                'foo',
                'bar',
            ],
        ];

        $expected = [
            'tt_content' => [
                'foo',
                'bar',
                'baz',
            ],
        ];

        $event = new MaskAllowedFieldsEvent($allowedFields);
        $event->addField('baz');

        self::assertSame($expected, $event->getAllowedFields());
    }

    /**
     * @test
     */
    public function fieldCanBeRemoved(): void
    {
        $allowedFields = [
            'tt_content' => [
                'foo',
                'bar',
                'baz',
            ],
        ];

        $expected = [
            'tt_content' => [
                'foo',
                'baz',
            ],
        ];

        $event = new MaskAllowedFieldsEvent($allowedFields);
        $event->removeField('bar');

        self::assertSame($expected, $event->getAllowedFields());
    }
}
