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

enum FieldType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case LINK = 'link';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';
    case TEXT = 'text';
    case RICHTEXT = 'richtext';
    case CHECK = 'check';
    case RADIO = 'radio';
    case SELECT = 'select';
    case CATEGORY = 'category';
    case GROUP = 'group';
    case FILE = 'file';
    case MEDIA = 'media';
    case INLINE = 'inline';
    case CONTENT = 'content';
    case TAB = 'tab';
    case PALETTE = 'palette';
    case LINEBREAK = 'linebreak';
    case COLORPICKER = 'colorpicker';
    case SLUG = 'slug';
    case EMAIL = 'email';
    case FOLDER = 'folder';

    public function isGroupingField(): bool
    {
        return in_array($this, [self::TAB, self::PALETTE, self::LINEBREAK], true);
    }

    public function isParentField(): bool
    {
        return in_array($this, [self::INLINE, self::PALETTE], true);
    }

    public function isSearchable(): bool
    {
        return in_array($this, [self::STRING, self::TEXT, self::RICHTEXT, self::EMAIL], true);
    }

    public function isRenderable(): bool
    {
        return !in_array($this, [self::TAB, self::LINEBREAK], true);
    }

    public function canBeShared(): bool
    {
        return !in_array($this, [self::INLINE, self::PALETTE, self::TAB, self::LINEBREAK], true);
    }

    public function hasDescription(): bool
    {
        return $this === self::PALETTE || $this->canBeShared();
    }

    public function isFileReference(): bool
    {
        return in_array($this, [self::FILE, self::MEDIA], true);
    }

    public function isTextareaField(): bool
    {
        return in_array($this, [self::TEXT, self::RICHTEXT], true);
    }

    public function isRelationField(): bool
    {
        return in_array($this, [self::INLINE, self::SELECT, self::GROUP, self::CATEGORY, self::CONTENT], true);
    }
}
