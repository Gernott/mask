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

namespace MASK\Mask\Enumeration;

use TYPO3\CMS\Core\Type\Enumeration;

final class FieldType extends Enumeration
{
    public const STRING = 'string';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';
    public const LINK = 'link';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const TIMESTAMP = 'timestamp';
    public const TEXT = 'text';
    public const RICHTEXT = 'richtext';
    public const CHECK = 'check';
    public const RADIO = 'radio';
    public const SELECT = 'select';
    public const GROUP = 'group';
    public const FILE = 'file';
    public const INLINE = 'inline';
    public const CONTENT = 'content';
    public const TAB = 'tab';
    public const PALETTE = 'palette';
    public const LINEBREAK = 'linebreak';

    public function isGroupingField()
    {
        return in_array($this->value, [self::TAB, self::PALETTE, self::LINEBREAK]);
    }

    public function isParentField()
    {
        return in_array($this->value, [self::INLINE, self::PALETTE]);
    }
}
