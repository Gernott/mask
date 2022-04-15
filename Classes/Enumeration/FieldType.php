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
    public const CATEGORY = 'category';
    public const GROUP = 'group';
    public const FILE = 'file';
    public const MEDIA = 'media';
    public const INLINE = 'inline';
    public const CONTENT = 'content';
    public const TAB = 'tab';
    public const PALETTE = 'palette';
    public const LINEBREAK = 'linebreak';
    public const COLORPICKER = 'colorpicker';
    public const SLUG = 'slug';

    public function isGroupingField(): bool
    {
        return in_array($this->value, [self::TAB, self::PALETTE, self::LINEBREAK], true);
    }

    public function isParentField(): bool
    {
        return in_array($this->value, [self::INLINE, self::PALETTE], true);
    }

    public function isSearchable(): bool
    {
        return in_array($this->value, [self::STRING, self::TEXT, self::RICHTEXT], true);
    }

    public function isRenderable(): bool
    {
        return !in_array($this->value, [self::TAB, self::LINEBREAK], true);
    }

    public function canBeShared(): bool
    {
        return !in_array($this->value, [self::INLINE, self::CONTENT, self::PALETTE, self::TAB, self::LINEBREAK], true);
    }

    public function hasDescription(): bool
    {
        return $this->value === self::PALETTE || $this->canBeShared();
    }

    public function isFileReference(): bool
    {
        return in_array($this->value, [self::FILE, self::MEDIA], true);
    }

    public function isTextareaField(): bool
    {
        return in_array($this->value, [self::TEXT, self::RICHTEXT], true);
    }
}
