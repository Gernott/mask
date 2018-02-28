<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class CTypesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Returns an array with all content element cTypes
     *
     * @return array $items an array with all content element cTypes
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render()
    {
        $items = array();
        $cTypes = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'];
        if ($cTypes) {
            foreach ($cTypes as $type) {
                if ($type[1] !== "--div--") {
                    if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($type[0], 'LLL:')) {
                        $items[$type[1]] = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($type[0], "mask") . " (" . $type[1] . ")";
                    } else {
                        $items[$type[1]] = $type[0] . " (" . $type[1] . ")";
                    }
                }
            }
            return $items;
        }
    }
}
