<?php

namespace MASK\Mask\ViewHelpers;

/**
 * Compatibility Viewhelper for TYPO3 6.2
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class IconViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Prints icon html for $identifier key
	 *
	 * @param string $identifier
	 * @param string $size
	 * @param string $overlay
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return string
	 */
	public function render($identifier, $size = NULL, $overlay = NULL) {
		// backwards compatibility for typo3 6.2
		$version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version();
		$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);

		// Since TYPO3 7.5 use IconViewHelper from core
		if ($versionNumber >= 7005000) {
			$iconViewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\ViewHelpers\IconViewHelper");
			if ($size == NULL) {
				$size = \TYPO3\CMS\Core\Imaging\IconFactory::SIZE_SMALL;
			}
			$iconViewHelper->setRenderingContext($this->renderingContext);
			return $iconViewHelper->render($identifier, $size, $overlay);
		} else {
			// For everything else use old simple preview icon
			return '<img src="/' . $extConf["preview"] . 'ce_' . str_replace("mask-ce-", "", $identifier) . '.png" alt="" />';
		}
	}

}
