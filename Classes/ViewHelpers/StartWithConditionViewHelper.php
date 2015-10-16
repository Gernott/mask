<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 * <mask:irre data="{data}" name="tx_mask_slider"/>
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class StartWithConditionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * renders <f:then> child if $condition is true, otherwise renders <f:else> child.
	 *
	 * @param string $string String to search in
	 * @param string $search String to search
	 * @param int $from Integer Startpoint
	 * @param int $length Integer Length
	 * @return string the rendered string
	 * @author Gernot Ploiner <gp@webprofil.at>
	 */
	public function render($string, $search, $from, $length) {
		if (substr($string, $from, $length) == $search) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

}
