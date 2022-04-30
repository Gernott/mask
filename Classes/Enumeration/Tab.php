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

final class Tab extends Enumeration
{
    public const GENERAL = 'general';
    public const APPEARANCE = 'appearance';
    public const DATABASE = 'database';
    public const EXTENDED = 'extended';
    public const FIELD_CONTROL = 'fieldControl';
    public const FILES = 'files';
    public const LOCALIZATION = 'localization';
    public const VALIDATION = 'validation';
    public const WIZARDS = 'wizards';
    public const GENERATOR = 'generator';
    public const ITEM_GROUP_SORTING = 'itemGroupSorting';
    public const VALUE_PICKER = 'valuePicker';
    public const ENABLED_CONTROLS = 'enabledControls';
}
