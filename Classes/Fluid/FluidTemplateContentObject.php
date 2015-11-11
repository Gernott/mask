<?php

namespace MASK\Mask\Fluid;

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

class FluidTemplateContentObject extends \TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject {

	/**
	 * MaskUtility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * ObjectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Change variables for view, called by TYPO3 7
	 *
	 * @param array $conf Configuration
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return void
	 */
	protected function getContentObjectVariables(array $conf = array()) {
		// Call Parent Function to maintain core functions
		$variables = parent::getContentObjectVariables($conf);

		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->utility = $this->objectManager->get("MASK\Mask\Utility\MaskUtility");

		// Make some enhancements to data
		$data = $variables['data'];
		$this->utility->addFilesToData($data, "pages");
		$this->utility->addIrreToData($data, "pages");
		$variables['data'] = $data;

		return $variables;
	}

	/**
	 * Assign content object renderer data and current to view, called by TYPO3 6.2
	 *
	 * @param array $conf Configuration
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return void
	 */
	protected function assignContentObjectDataAndCurrent(array $conf = array()) {
		// Call Parent Function to maintain core functions
		parent::assignContentObjectDataAndCurrent($conf);

		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->utility = $this->objectManager->get("MASK\Mask\Utility\MaskUtility");

		// Make some enhancements to data
		$data = $this->cObj->data;
		$this->utility->addFilesToData($data, "pages");
		$this->utility->addIrreToData($data, "pages");

		$this->view->assign('data', $data);
	}

}
