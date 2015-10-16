<?php

namespace MASK\Mask\Hooks;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Renders the backend preview of mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 * @package MASK
 * @subpackage mask
 */
class PageLayoutViewDrawItem implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {

	protected $objectManager;
	protected $utility;
	protected $storageRepository;

	/**
	 * Preprocesses the preview rendering of a content element.
	 *
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
	 * @param boolean $drawItem Whether to draw the item using the default functionalities
	 * @param string $headerContent Header content
	 * @param string $itemContent Item content
	 * @param array $row Record row of tt_content
	 * @return void
	 */
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {

		// only render special backend preview if it is a mask element
		if (substr($row['CType'], 0, 4) === "mask") {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
			$elementKey = substr($row['CType'], 5);
			$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extConf["backend"]);
			$templatePathAndFilename = $templateRootPath . $elementKey . '.html';

			if (file_exists($templatePathAndFilename)) {
				// initialize some things we need
				$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\Object\\ObjectManager');
				$this->utility = $this->objectManager->get("MASK\Mask\Utility\MaskUtility");
				$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
				$view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

				// Load the backend template
				$view->setTemplatePathAndFilename($templatePathAndFilename);

				// Fetch and assign some useful variables
				$data = $this->getContentObject($row["uid"]);
				$element = $this->storageRepository->loadElement("tt_content", $elementKey);
				$view->assign("row", $row);
				$view->assign("data", $data);

				// Render everything
				$content = $view->render();
				$headerContent = '<strong>' . $element["label"] . '</strong><br>';
				$itemContent .= '<div style="display:block; padding: 10px 0 4px 0px;border-top: 1px solid #CACACA;margin-top: 6px;" class="content_preview_' . $elementKey . '">';
				$itemContent .= $content;
				$itemContent .= '</div>';
				$drawItem = FALSE;
			}
		}
	}

	/**
	 * Returns an array with properties of content element with given uid
	 *
	 * @param int $uid of content element to get
	 * @return array with all properties of given content element uid
	 */
	protected function getContentObject($uid) {
		$data = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', 'uid=' . $uid);
		$this->utility->addFilesToData($data, "tt_content");
		$this->utility->addIrreToData($data);
		return $data;
	}

}
