<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class SubstrViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @param string $string String to search in
     * @param string $search String to search
     * @param int $from Integer Startpoint
     * @param int $length Integer Length
     * @return string the rendered string
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render($string, $search, $from, $length)
    {
        return (substr($string, $from, $length) === $search);
    }
}
