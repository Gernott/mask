<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell <bb@webprofil.at>
 *
 */
class ItemsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Returns all elements that use this field
     *
     * @param array $items TCA Type
     * @return string items as string
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render($items)
    {
        $itemArray = array();
        if ($items) {
            foreach ($items as $item) {
                $itemArray[] = implode(",", $item);
            }
        }
        return implode("\n", $itemArray);
    }
}
