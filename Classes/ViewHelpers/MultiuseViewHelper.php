<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class MultiuseViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Utility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * Returns all elements that use this field
	 *
	 * @param string $key TCA Type
	 * @param string $elementKey Key of the element
	 * @return array elements in use
	 * @author Benjamin Butschell bb@webprofil.at>
	 */
	public function render($key, $elementKey) {
		$this->utility = new \MASK\Mask\Utility\MaskUtility($this->objectManager);
		$type = $this->utility->getFieldType($key, $elementKey);
		return $this->utility->getElementsWhichUseField($key, $type);
	}

}
