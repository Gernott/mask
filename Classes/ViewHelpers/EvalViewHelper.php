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
class EvalViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Utility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * Checks if a $evalValue is set in a field
	 *
	 * @param string $fieldKey TCA Type
	 * @param string $elementKey TCA Type
	 * @param string $evalValue value to search for
	 * @param array $field
	 * @return boolean $evalValue is set
	 * @author Benjamin Butschell bb@webprofil.at>
	 */
	public function render($fieldKey, $elementKey, $evalValue, $field = NULL) {
		$this->utility = new \MASK\Mask\Utility\MaskUtility($this->objectManager);
		if ($field) {
			if ($field["inlineParent"]) {
				$type = $field["inlineParent"];
				$fieldKey = "tx_mask_".$field["key"];
			} else {
				$type = $this->utility->getFieldType($fieldKey, $elementKey);
			}
		} else {
			$type = $this->utility->getFieldType($fieldKey, $elementKey);
		}
		return $this->utility->isEvalValueSet($fieldKey, $evalValue, $type);
	}
}