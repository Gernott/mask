<?php

namespace MASK\Mask\Controller;

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
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * ^
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class WizardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var bool
	 */
	protected $dbUpdateNeeded = FALSE;

	/**
	 * @var string
	 */
	protected $extConf = "";

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
	 * MaskUtility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * Generates all the necessary files
	 * @author Gernot Ploiner <gp@webprofil.at>
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @todo clear typoscript cache after generating
	 */
	public function generateAction() {
		// Update Database
		$this->updateDatabase();
	}

	/**
	 * Checks for DB-Updates, adjusted function from extension_builder
	 *
	 * @param string $extensionKey
	 * @param string $sqlContent
	 * @return void
	 */
	protected function checkForDbUpdate($extensionKey, $sqlContent) {
		$this->dbUpdateNeeded = FALSE;
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extensionKey)) {
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			if (class_exists('TYPO3\\CMS\\Install\\Service\\SqlSchemaMigrationService')) {
				/* @var \TYPO3\CMS\Install\Service\SqlSchemaMigrationService $sqlHandler */
				$sqlHandler = $this->objectManager->get('TYPO3\\CMS\\Install\\Service\\SqlSchemaMigrationService');
			} else {
				/* @var \TYPO3\CMS\Install\Sql\SchemaMigrator $sqlHandler */
				$sqlHandler = $this->objectManager->get('TYPO3\\CMS\\Install\\Sql\\SchemaMigrator');
			}
			/** @var $cacheManager \TYPO3\CMS\Core\Cache\CacheManager */
			\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
//			$sqlFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'ext_tables.sql';
//			$sqlContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($sqlFile);
//			$sqlContent = implode(";", $this->storageRepository->loadSql());
//			var_dump(implode(";", $sqlContent));
//			exit;
			$fieldDefinitionsFromFile = $sqlHandler->getFieldDefinitions_fileContent($sqlContent);
//
//			var_dump($fieldDefinitionsFromFile);
//			exit;
			if (count($fieldDefinitionsFromFile)) {
				$fieldDefinitionsFromCurrentDatabase = $sqlHandler->getFieldDefinitions_database();
				$updateTableDefinition = $sqlHandler->getDatabaseExtra($fieldDefinitionsFromFile, $fieldDefinitionsFromCurrentDatabase);
				$this->updateStatements = $sqlHandler->getUpdateSuggestions($updateTableDefinition);
				if (!empty($updateTableDefinition['extra']) || !empty($updateTableDefinition['diff']) || !empty($updateTableDefinition['diff_currentValues'])) {
					$this->dbUpdateNeeded = TRUE;
				}
			}
		}
	}

	/**
	 * Performs updates, adjusted function from extension_builder
	 *
	 * @param array $params
	 * @return type
	 */
	protected function performDbUpdates($params, $sql) {

		$hasErrors = FALSE;
		if (!empty($params['extensionKey'])) {
			$this->checkForDbUpdate($params['extensionKey'], $sql);
			if ($this->dbUpdateNeeded) {
				foreach ($this->updateStatements as $type => $statements) {

					foreach ($statements as $statement) {
						if (in_array($type, array('change', 'add', 'create_table'))) {
							$res = $this->getDatabaseConnection()->admin_query($statement);


							if ($res === FALSE) {
								$hasErrors = TRUE;
								\TYPO3\CMS\Core\Utility\GeneralUtility::devlog('SQL error', 'mask', 0, array('statement' => $statement, 'error' => $this->getDatabaseConnection()->sql_error()));
							} elseif (is_resource($res) || is_a($res, '\\mysqli_result')) {
								$this->getDatabaseConnection()->sql_free_result($res);
							}
						}
					}
				}
			}
		}
		if ($hasErrors) {
			return array('error' => 'Database could not be updated. Please check it in the update wizard of the install tool');
		} else {
			return array('success' => 'Database was successfully updated');
		}
	}

	/**
	 * function from extension_builder
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Updates the database if necessary
	 *
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return array
	 */
	protected function updateDatabase() {
		$params["extensionKey"] = "mask";
		$sqlStatements = $this->storageRepository->loadSql();
		if (count($sqlStatements) > 0) {
			$response = $this->performDbUpdates($params, implode(" ", $sqlStatements));
		}
		return $response;
	}

	/**
	 * Prepares the storage array for fluid view
	 *
	 * @param array $storage
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	protected function prepareStorage(&$storage) {
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
	protected function showHtmlAction($key) {
		$html = $this->generateHtml($key);
		$this->view->assign('html', $html);
	}

	/**
	 * Generates Fluid HTML for Contentelements
	 *
	 * @param string $key
	 * @return string $html
	 * @author Gernot Ploiner <gp@webprofil.at>
	 *
	 */
	protected function generateHtml($key, $table = "tt_content") {
		$storage = $this->storageRepository->loadElement('tt_content', $key);
		$html = "";
		if ($storage["tca"]) {
			foreach ($storage["tca"] as $fieldKey => $fieldConfig) {
				$html .= $this->generateFieldHtml($fieldKey, $key);
			}
		}
		return $html;
	}

	/**
	 * Generates HTML for a field
	 * @param string $fieldKey
	 * @param string $elementKey
	 * @param string $table
	 * @param string $datafield
	 * @return string $html
	 * @author Gernot Ploiner <gp@webprofil.at>
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function generateFieldHtml($fieldKey, $elementKey, $table = "tt_content", $datafield = "data") {
		$html = "";
		switch ($this->utility->getFormType($fieldKey, $elementKey, $table)) {
			case "Check":
				$html .= "{f:if(condition: " . $datafield . "." . $fieldKey . ", then: 'On', else: 'Off')}<br />\n\n";
				break;
			case "Content": // TODO: Benjamin, Fluid-Vorlage für Feld "Content Verbindung":
				$html .= '{' . $datafield . '.' . $fieldKey . '}<br />' . "\n\n";
				break;
			case "Date":
				$html .= '<f:format.date format="d.m.Y">{' . $datafield . '.' . $fieldKey . '}</f:format.date><br />' . "\n\n";
				break;
			case "Datetime":
				$html .= '<f:format.date format="d.m.Y - H:i:s">{' . $datafield . '.' . $fieldKey . '}</f:format.date><br />' . "\n\n";
				break;
			case "File":
				$html .= '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="file">
  <f:image src="{file.uid}" alt="{file.alternative}" title="{file.title}" treatIdAsReference="1" width="200" /><br />
  {file.description} / {file.identifier}<br />
</f:for>' . "\n\n";
				break;
			case "Float":
				$html .= '<f:format.number decimals="2" decimalSeparator="," thousandsSeparator=".">{' . $datafield . '.' . $fieldKey . '}</f:format.number><br />' . "\n\n";
				break;
			case "Inline":
				$html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
				$html .= "<ul>\n";
				$html .= "<f:for each=\"{" . $datafield . "." . $fieldKey . "}\" as=\"" . $datafield . "_item" . "\">\n<li>";
				$inlineFields = $this->storageRepository->loadInlineFields($fieldKey);
				if ($inlineFields) {
					foreach ($inlineFields as $inlineField) {
						$html .= $this->generateFieldHtml($inlineField["maskKey"], $elementKey, $fieldKey, $datafield . "_item") . "\n";
					}
				}
				$html .= "</li>\n</f:for>" . "\n";
				$html .= "</ul>\n";
				$html .= "</f:if>\n\n";
				break;
			case "Integer":
				$html .= '{' . $datafield . '.' . $fieldKey . '}<br />' . "\n\n";
				break;
			case "Link":
				$html .= '<f:link.page pageUid="{' . $datafield . '.' . $fieldKey . '}">{data.' . $fieldKey . '}</f:link.page><br />' . "\n\n";
				break;
			case "Radio":
				$html .= '<f:switch expression="{' . $datafield . '.' . $fieldKey . '}">
  <f:case value="1">Value is: 1</f:case>
  <f:case value="2">Value is: 2</f:case>
  <f:case value="3">Value is: 3</f:case>
</f:switch><br />' . "\n\n";
				break;
			case "Richtext":
				$html .= '<f:format.html parseFuncTSPath="lib.parseFunc_RTE">{' . $datafield . '.' . $fieldKey . '}</f:format.html><br />' . "\n\n";
				break;
			case "Select":
				$html .= '<f:switch expression="{' . $datafield . '.' . $fieldKey . '}">
  <f:case value="1">Value is: 1</f:case>
  <f:case value="2">Value is: 2</f:case>
  <f:case value="3">Value is: 3</f:case>
</f:switch><br />' . "\n\n";
				break;
			case "String":
				$html .= '{' . $datafield . '.' . $fieldKey . '}<br />' . "\n\n";
				break;
			case "Text":
				$html .= '<f:format.nl2br>{' . $datafield . '.' . $fieldKey . '}</f:format.nl2br><br />' . "\n\n";
				break;
		}
		return $html;
	}

	/**
	 * Saves Fluid HTML for Contentelements, if File not exists
	 *
	 * @param string $key
	 * @param string $html
	 * @author Gernot Ploiner <gp@webprofil.at>
	 */
	protected function saveHtml($key, $html) {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		if (file_exists(PATH_site . $extConf["content"] . $key . ".html")) {
			return false;
		} else {
			$handle = fopen(PATH_site . $extConf["content"] . $key . ".html", "w");
			fwrite($handle, $html);
			return true;
		}
	}

	/**
	 * Saves preview image for Contentelements, if File not exists
	 *
	 * @param string $key
	 * @author Gernot Ploiner <gp@webprofil.at>
	 */
	protected function savePreviewImage($key) {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		if (file_exists(PATH_site . $extConf["preview"] . "ce_" . $key . ".png")) {
			return false;
		} else {
			$source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mask') . "Resources/Private/Images/preview.png";
			$target = PATH_site . $extConf["preview"] . "ce_" . $key . ".png";
			if (copy($source, $target)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Check, if folders from extensionmanager-settings are existing
	 *
	 * @author Gernot Ploiner <gp@webprofil.at>
	 */
	protected function checkFolders() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		if (!file_exists(PATH_site . $extConf["content"])) {
			$message[] = $extConf["content"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder', 'mask');
		}
		if (!file_exists(PATH_site . $extConf["preview"])) {
			$message[] = $extConf["preview"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder', 'mask');
		}
		return $message;
	}

	/**
	 * Checks if a key for a field is available
	 *
	 * @param array $params Array of parameters from the AJAX interface, not
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj Object of type AjaxRequestHandler
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return void
	 */
	public function checkFieldKey($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
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
	public function checkElementKey($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = NULL) {
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
	protected function redirectByAction() {
		$params = $this->request->getArguments();
		$formAction = $params["formAction"];
		$arguments = array();
		if ($params["storage"]["type"] == "pages") {
			$arguments["layout"] = $this->backendLayoutRepository->findByUid($params["storage"]["elements"]["key"]);
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
	 * Creates missing folders that are needed for the use of mask
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return bool $success
	 */
	protected function createMissingFolders() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		$success = TRUE;
		if (!file_exists(PATH_site . $extConf["content"])) {
			$success = $success && mkdir(PATH_site . $extConf["content"], 0755, true);
		}
		if (!file_exists(PATH_site . $extConf["preview"])) {
			$success = $success && mkdir(PATH_site . $extConf["preview"], 0755, true);
		}
		return $success;
	}

	/**
	 * action creates missing folders
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function createMissingFoldersAction() {
		if ($this->createMissingFolders()) {
			$this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.createdmissingfolders', 'mask'));
		}
		$this->redirect("list");
	}

}
