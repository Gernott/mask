<?php

declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 */
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
