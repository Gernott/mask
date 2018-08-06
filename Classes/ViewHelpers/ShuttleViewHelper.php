<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 * <mask:shuttle data="{data}" name="tx_mask_slider"/>
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @todo Test if neccessary in selectbox-shuttle-frontend
 *
 */
class ShuttleViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Returns Shuttle-Elements of Data-Object
     *
     * @param string $table The name of the table
     * @param string $field The name of the field
     * @return array all irre elements of this attribut
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    public function render($table, $field)
    {
        $sql = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
            "*", $table, "uid IN(" . $field . ") AND deleted = '0'"
        );
        while ($element = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($sql)) {
            $elements[] = $element;
        }
        return $elements;
    }
}
