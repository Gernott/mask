<?php namespace MASK\Mask\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ^
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class WizardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

   /**
	* @var bool
	*/
   protected $dbUpdateNeeded = FALSE;

   /**
	* StorageRepository
	*
	* @var \MASK\Mask\Domain\Repository\StorageRepository
	* @inject
	*/
   protected $storageRepository;

   /**
	* BackendLayoutRepository
	*
	* @var \MASK\Mask\Domain\Repository\BackendLayoutRepository
	* @inject
	*/
   protected $backendLayoutRepository;

   /**
	* FieldHelper
	*
	* @var \MASK\Mask\Helper\FieldHelper
	* @inject
	*/
   protected $fieldHelper;

   /**
	* HtmlCodeGenerator
	*
	* @var \MASK\Mask\CodeGenerator\HtmlCodeGenerator
	* @inject
	*/
   protected $htmlCodeGenerator;

   /**
	* SqlCodeGenerator
	*
	* @var \MASK\Mask\CodeGenerator\SqlCodeGenerator
	* @inject
	*/
   protected $sqlCodeGenerator;

   /**
	* SettingsService
	*
	* @var \MASK\Mask\Domain\Service\SettingsService
	* @inject
	*/
   protected $settingsService;

   /**
	* settings
	*
	* @var array
	*/
   protected $extSettings;

   /**
	* is called before every action
	*/
   public function initializeAction()
   {
	  $this->extSettings = $this->settingsService->get();
   }

   /**
	* Generates all the necessary files
	* @author Gernot Ploiner <gp@webprofil.at>
	* @author Benjamin Butschell <bb@webprofil.at>
	* @todo clear typoscript cache after generating
	*/
   public function generateAction()
   {
	  // Update Database
	  $this->sqlCodeGenerator->updateDatabase();

	  // Clear system cache to force new TCA caching
	  $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
	  $cacheManager->flushCachesInGroup('system');
   }

   /**
	* Prepares the storage array for fluid view
	*
	* @param array $storage
	* @author Benjamin Butschell <bb@webprofil.at>
	*/
   protected function prepareStorage(&$storage)
   {
	  // Fill storage with additional data before assigning to view
	  if ($storage["tca"]) {
		 foreach ($storage["tca"] as $key => $field) {
			if (is_array($field)) {
			   if ($field["config"]["type"] == "inline") {
				  $storage["tca"][$key]["inlineFields"] = $this->storageRepository->loadInlineFields($key);
			   }
			}
		 }
	  }
   }

   /**
	* Generates Fluid HTML for Contentelements
	*
	* @param string $key
	* @author Gernot Ploiner <gp@webprofil.at>
	*/
   protected function showHtmlAction($key)
   {
	  $html = $this->htmlCodeGenerator->generateHtml($key);
	  $this->view->assign('html', $html);
   }

   /**
	* Saves Fluid HTML for Contentelements, if File not exists
	*
	* @param string $key
	* @param string $html
	* @author Gernot Ploiner <gp@webprofil.at>
	*/
   protected function saveHtml($key, $html)
   {
	  if (file_exists(PATH_site . $this->extSettings["content"] . $key . ".html")) {
		 return false;
	  } else {
		 \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $this->extSettings["content"] . $key . ".html", $html);
		 return true;
	  }
   }

   /**
	* Checks if a key for a field is available
	*
	* @param array $params Array of parameters from the AJAX interface, not
	* @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj Object of type AjaxRequestHandler
	* @author Benjamin Butschell <bb@webprofil.at>
	* @return void
	*/
   public function checkFieldKey($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
   {
	  $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	  $this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
	  // Get parameters, is there a better way? $params is not used yet
	  $fieldKey = $_GET["key"];
	  if ($_GET["table"]) {
		 $table = $_GET["table"];
	  } else {
		 $table = "tt_content";
	  }
	  // check if fieldKey is available for this table
	  $isAvailable = TRUE;
	  if ($this->storageRepository->loadField($table, $fieldKey)) {
		 $isAvailable = FALSE;
	  }
	  // return infos as json
	  $ajaxObj->setContentFormat("plain");
	  $ajaxObj->addContent("isAvailable", json_encode($isAvailable));
   }

   /**
	* Checks if a key for an element is available
	*
	* @param array $params Array of parameters from the AJAX interface, not
	* @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj Object of type AjaxRequestHandler
	* @author Benjamin Butschell <bb@webprofil.at>
	* @return void
	*/
   public function checkElementKey($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL)
   {
	  $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	  $this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
	  // Get parameters, is there a better way? $params is not used yet
	  $elementKey = $_GET["key"];
	  // check if elementKey is available
	  $isAvailable = TRUE;

	  if ($this->storageRepository->loadElement("tt_content", $elementKey)) {

		 $isAvailable = FALSE;
	  }
	  // return infos as json
	  $ajaxObj->setContentFormat("plain");
	  $ajaxObj->addContent("isAvailable", json_encode($isAvailable));
   }

   /**
	* Redirects the request to the correct view
	* @author Benjamin Butschell <bb@webprofil.at>
	*/
   protected function redirectByAction()
   {
	  $params = $this->request->getArguments();
	  $formAction = $params["formAction"];
	  $arguments = array();
	  if ($params["storage"]["type"] == "pages") {
		 $arguments["layoutIdentifier"] = $this->backendLayoutRepository->findByIdentifier($params["storage"]["elements"]["key"], explode(",", $this->extSettings['backendlayout_pids']))->getIdentifier();
	  } else {
		 $arguments["key"] = $params["storage"]["elements"]["key"];
		 $arguments["type"] = $params["storage"]["type"];
	  }
	  if (key_exists("save", $formAction)) {
		 $this->redirect('edit', NULL, NULL, $arguments);
	  } else if (key_exists("saveAndExit", $formAction)) {
		 $this->redirect('list');
	  }
   }

   /**
	* Check, if folders from extensionmanager-settings are existing
	*
	* @author Gernot Ploiner <gp@webprofil.at>
	* @return array $messages
	*/
   protected function checkFolders()
   {

	  $messages = [];

	  if (!file_exists(PATH_site . $this->extSettings["content"])) {
		 $messages[] = $this->extSettings["content"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder', 'mask');
	  }
	  if (!file_exists(PATH_site . $this->extSettings["preview"])) {
		 $messages[] = $this->extSettings["preview"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder', 'mask');
	  }
	  return $messages;
   }

   /**
	* Creates missing folders that are needed for the use of mask
	* @author Benjamin Butschell <bb@webprofil.at>
	* @return bool $success
	*/
   protected function createMissingFolders()
   {
	  $success = TRUE;
	  if (!file_exists(PATH_site . $this->extSettings["content"])) {
		 $success = $success && mkdir(PATH_site . $this->extSettings["content"], 0755, true);
	  }
	  if (!file_exists(PATH_site . $this->extSettings["preview"])) {
		 $success = $success && mkdir(PATH_site . $this->extSettings["preview"], 0755, true);
	  }
	  return $success;
   }

   /**
	* action creates missing folders
	* @author Benjamin Butschell <bb@webprofil.at>
	*/
   public function createMissingFoldersAction()
   {
	  if ($this->createMissingFolders()) {
		 $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.createdmissingfolders', 'mask'));
	  }
	  $this->redirect("list");
   }
}
