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

enum Tab: string
{
    case GENERAL = 'general';
    case APPEARANCE = 'appearance';
    case DATABASE = 'database';
    case EXTENDED = 'extended';
    case FIELD_CONTROL = 'fieldControl';
    case FILES = 'files';
    case LOCALIZATION = 'localization';
    case VALIDATION = 'validation';
    case WIZARDS = 'wizards';
    case GENERATOR = 'generator';
    case ITEM_GROUP_SORTING = 'itemGroupSorting';
    case VALUE_PICKER = 'valuePicker';
    case ENABLED_CONTROLS = 'enabledControls';
}
