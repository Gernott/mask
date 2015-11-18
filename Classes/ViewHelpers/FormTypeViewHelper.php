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
class FormTypeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Utility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * Returns the label of a field in an element
	 *
	 * @param string $elementKey Key of Element
	 * @param string $fieldKey Key if Field
	 * @param string $type table
	 * @return string formType
	 * @author Benjamin Butschell bb@webprofil.at>
	 */
	public function render($elementKey, $fieldKey, $type = "tt_content") {

		$this->utility = new \MASK\Mask\Utility\MaskUtility($this->objectManager);
		$formType = $this->utility->getFormType($fieldKey, $elementKey, $type);
		return $formType;
	}

}
