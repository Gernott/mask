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
class LabelViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

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
	 * @param array $field
	 * @return string Label
	 * @author Benjamin Butschell bb@webprofil.at>
	 */
	public function render($elementKey, $fieldKey, $field = NULL) {
		$this->utility = new \MASK\Mask\Utility\MaskUtility($this->objectManager);
		$type = $this->utility->getFieldType($fieldKey, $elementKey);
		if ($field) {
			if ($field["inlineParent"]) {
				$label = $field["label"];
			} else {
				$label = $this->utility->getLabel($elementKey, $fieldKey, $type);
			}
		} else {
			$label = $this->utility->getLabel($elementKey, $fieldKey, $type);
		}
		return $label;
	}

}
