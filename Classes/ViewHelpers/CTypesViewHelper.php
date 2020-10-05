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

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CTypesViewHelper extends AbstractViewHelper
{

    /**
     * Returns an array with all content element cTypes
     *
     * @return array $items an array with all content element cTypes
     */
    public function render(): array
    {
        $items = [];
        $cTypes = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'];
        if ($cTypes) {
            foreach ($cTypes as $type) {
                if ($type[1] !== '--div--') {
                    if (GeneralUtility::isFirstPartOfStr($type[0], 'LLL:')) {
                        $items[$type[1]] = LocalizationUtility::translate($type[0], 'mask') . ' (' . $type[1] . ')';
                    } else {
                        $items[$type[1]] = $type[0] . ' (' . $type[1] . ')';
                    }
                }
            }
        }
        return $items;
    }
}
