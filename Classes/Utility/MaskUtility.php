<?php

namespace MASK\Mask\Utility;

class MaskUtility {

	/**
	 * StorageRepository
	 *
	 * @var \MASK\Mask\Domain\Repository\StorageRepository
	 */
	protected $storageRepository;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager, \MASK\Mask\Domain\Repository\StorageRepository $storageRepository = NULL) {
		$this->objectManager = $objectManager;
		if (!$storageRepository) {
			$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		} else {
			$this->storageRepository = $storageRepository;
		}
	}

	/**
	 * Returns all elements that use this field
	 *
	 * @param string $key TCA Type
	 * @param string $type elementtype
	 * @return array elements in use
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function getElementsWhichUseField($key, $type = "tt_content") {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$storage = $this->storageRepository->load();

		$elementsInUse = array();
		if ($storage[$type]["elements"]) {
			foreach ($storage[$type]["elements"] as $element) {
				if ($element["columns"]) {
					foreach ($element["columns"] as $column) {
						if ($column == $key) {
							$elementsInUse[] = $element;
						}
					}
				}
			}
		}
		return $elementsInUse;
	}

	/**
	 * Returns the label of a field in an element
	 *
	 * @param string $elementKey Key of Element
	 * @param string $fieldKey Key if Field
	 * @param string $type elementtype
	 * @param boolean $isInlineField elementtype
	 * @return string Label
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function getLabel($elementKey, $fieldKey, $type = "tt_content", $isInlineField = FALSE) {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$storage = $this->storageRepository->load();
		$fieldIndex = -1;
		if (count($storage[$type]["elements"][$elementKey]["columns"]) > 0) {
			foreach ($storage[$type]["elements"][$elementKey]["columns"] as $index => $column) {
				if ($column == $fieldKey) {
					$fieldIndex = $index;
				}
			}
		}
		if ($fieldIndex >= 0) {
			$label = $storage[$type]["elements"][$elementKey]["labels"][$fieldIndex];
		} else {
			$label = "";
		}
		return $label;
	}

	/**
	 * Returns the formType of a field in an element
	 *
	 * @param string $fieldKey Key if Field
	 * @param string $elementKey Key of Element
	 * @param string $type elementtype
	 * @return string formType
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function getFormType($fieldKey, $elementKey = "", $type = "tt_content") {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$formType = "String";

		// Load element and TCA of field
		if ($elementKey) {
			$element = $this->storageRepository->loadElement($type, $elementKey);
		}

		// load tca for field from $GLOBALS
		$tca = $GLOBALS["TCA"][$type]["columns"][$fieldKey];
		if (!$tca["config"]) {
			$tca = $GLOBALS["TCA"][$type]["columns"]["tx_mask_" . $fieldKey];
		}
		if (!$tca["config"]) {
			$tca = $element["tca"][$fieldKey];
		}

		// if field is in inline table, load tca from json
		if (!in_array($type, array("tt_content", "pages"))) {
			$tca = $this->storageRepository->loadField($type, $fieldKey);
			if (!$tca["config"]) {
				$tca = $this->storageRepository->loadField($type, "tx_mask_" . $fieldKey);
			}
		}


		$tcaType = $tca["config"]["type"];
		$evals = explode(",", $tca["config"]["eval"]);

		if ($tca["options"] == "file") {
			$formType = "File";
		}

		// And decide via different tca settings which formType it is
		switch ($tcaType) {
			case "input":
				$formType = "String";
				if (array_search(strtolower("int"), $evals) !== FALSE) {
					$formType = "Integer";
				} else if (array_search(strtolower("double2"), $evals) !== FALSE) {
					$formType = "Float";
				} else if (array_search(strtolower("date"), $evals) !== FALSE) {
					$formType = "Date";
				} else if (array_search(strtolower("datetime"), $evals) !== FALSE) {
					$formType = "Datetime";
				} else {
					if (is_array($tca["config"]["wizards"]["link"])) {
						$formType = "Link";
					} else {
						$formType = "String";
					}
				}
				break;
			case "text":
				$formType = "Text";
				if (in_array($type, array("tt_content", "pages"))) {
					if ($elementKey) {
						$fieldNumberKey = -1;
						if (is_array($element["columns"])) {
							foreach ($element["columns"] as $numberKey => $column) {
								if ($column == $fieldKey) {
									$fieldNumberKey = $numberKey;
								}
							}
						}

						if ($fieldNumberKey >= 0) {
							$option = $element["options"][$fieldNumberKey];
							if ($option == "rte") {
								$formType = "Richtext";
							} else {
								$formType = "Text";
							}
						}
					} else {
						$formType = "Text";
					}
				} else {
					if ($tca["rte"]) {
						$formType = "Richtext";
					} else {
						$formType = "Text";
					}
				}
				break;
			case "check":
				$formType = "Check";
				break;
			case "radio":
				$formType = "Radio";
				break;
			case "select":
				$formType = "Select";
				break;
			case "group":
				break;
			case "none":
				break;
			case "passthrough":
				break;
			case "user":
				break;
			case "flex":
				break;
			case "inline":
				$formType = "Inline";
				if ($tca["config"]["foreign_table"] == "sys_file_reference") {
					$formType = "File";
				} else {
					$formType = "Inline";
				}
				break;
			default:
				break;
		}
		return $formType;
	}

	/**
	 * Checks if a $evalValue is set in a field
	 *
	 * @param string $fieldKey TCA Type
	 * @param string $evalValue value to search for
	 * @param string $type elementtype
	 * @return boolean $evalValue is set
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function isEvalValueSet($fieldKey, $evalValue, $type = "tt_content") {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$storage = $this->storageRepository->load();
		$found = FALSE;
		if ($storage[$type]["tca"][$fieldKey]["config"]["eval"] != "") {
			$evals = explode(",", $storage[$type]["tca"][$fieldKey]["config"]["eval"]);
			$found = array_search(strtolower($evalValue), $evals) !== FALSE;
		}
		return $found;
	}

	/**
	 * Checks if a $evalValue is set in a field
	 *
	 * @param string $fieldKey TCA Type
	 * @param string $evalValue value to search for
	 * @param string $type elementtype
	 * @return boolean $evalValue is set
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function isBlindLinkOptionSet($fieldKey, $evalValue, $type = "tt_content") {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$storage = $this->storageRepository->load();
		$found = FALSE;
		if ($storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["params"]["blindLinkOptions"] != "") {
			$evals = explode(",", $storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["params"]["blindLinkOptions"]);
			$found = array_search(strtolower($evalValue), $evals) !== FALSE;
		}
		return $found;
	}

	/**
	 * Returns type of field (tt_content or pages)
	 *
	 * @param string $fieldKey key of field
	 * @param string $elementKey key of element
	 * @return string $fieldType returns fieldType or null if not found
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function getFieldType($fieldKey, $elementKey = "") {
		$this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
		$storage = $this->storageRepository->load();

		// get all possible types (tables)
		if ($storage) {
			$types = array_keys($storage);
		} else {
			$types = array();
		}
		$types[] = "pages";
		$types[] = "tt_content";
		$types = array_unique($types);

		$fieldType = "";
		$found = FALSE;
		foreach ($types as $type) {
			if ($storage[$type]["elements"] && !$found) {
				foreach ($storage[$type]["elements"] as $element) {

					// if this is the element we search for, or no special element was given,
					// and the element has colums and the fieldType wasn't found yet
					if (($element["key"] == $elementKey || $elementKey == "") && $element["columns"] && !$found) {

						foreach ($element["columns"] as $column) {
							if ($column == $fieldKey && !$found) {
								$fieldType = $type;
								$found = TRUE;
							}
						}
					}
				}
			} else if (is_array($storage[$type]["tca"][$fieldKey])) {
				$fieldType = $type;
				$found = TRUE;
			}
		}
		return $fieldType;
	}

	/**
	 * replace keys
	 *
	 * @author Gernot Ploiner <gp@webprofil.at>
	 * @return array
	 */
	public function replaceKey($data, $replace_key, $key = "--key--") {
		foreach ($data as $elem_key => $elem) {
			if (is_array($elem)) {
				$data[$elem_key] = $this->replaceKey($elem, $replace_key);
			} else {
				if ($data[$elem_key] == $key) {
					$data[$elem_key] = $replace_key;
				}
			}
		}
		return $data;
	}

	/**
	 * Adds FAL-Files to the data-array if available
	 *
	 * @param array $data
	 * @param array $table
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function addFilesToData(&$data, $table = "tt_content") {
		$storage = $this->storageRepository->load();
		/* @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
		$fileRepository = $this->objectManager->get("TYPO3\CMS\Core\Resource\FileRepository");
		$contentFields = array("media", "image", "assets");
		if ($storage[$table]["tca"]) {
			foreach ($storage[$table]["tca"] as $fieldKey => $field) {
				$contentFields[] = $fieldKey;
			}
		}
		if ($contentFields) {
			foreach ($contentFields as $fieldKey) {
				if ($this->getFormType($fieldKey, "", $table) == "File") {
					$data[$fieldKey] = $fileRepository->findByRelation($table, $fieldKey, $data["uid"]);
				}
			}
		}
	}

	/**
	 * Adds FAL-Files to the data-array if available
	 *
	 * @param array $data
	 * @param array $table
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function addIrreToData(&$data, $table = "tt_content", $cType = "") {
		if ($cType == "") {
			$cType = $data["CType"];
		}
		$storage = $this->storageRepository->load();
		$contentFields = $storage[$table]["tca"];

		if ($contentFields) {
			foreach ($contentFields as $fieldname => $field) {
				if ($this->getFormType($field["key"], $cType, $table) == "Inline") {
					$elements = $this->getInlineElements($data, $fieldname, $cType, "parentid", $table);
					$data[$fieldname] = $elements;
				}
			}
		}
	}

	/**
	 * Generates and sets the correct tca for all the inline fields
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @param array $json
	 */
	public function setInlineTca($json) {
		// Generate TCA for IRRE Fields and Tables
		$notIrreTables = array("pages", "tt_content", "sys_file_reference");
		if ($json) {
			foreach ($json as $table => $subJson) {
				$fieldTCA = array();
				if (array_search($table, $notIrreTables) === FALSE) {
					// Generate Table TCA
					$this->generateTableTca($table, $subJson["tca"]);
					// Generate Field TCA
					$fieldTCA = $this->generateFieldsTca($subJson["tca"]);
					\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $fieldTCA);
					// hide table in list view
					$GLOBALS["TCA"][$table]['ctrl']['hideTable'] = TRUE;
				}
			}
		}
	}

	/**
	 * Generates and sets the tca for all the content-elements
	 *
	 * @param array $tca
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function setElementsTca($tca) {

		// backwards compatibility for typo3 6.2
		$version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version();
		$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);
		if ($versionNumber >= 7000000) {
			$defaultTabs = ",--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended,--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,categories";
		} else {
			$defaultTabs = ",--div--;LLL:EXT:cms/locallang_tca.xlf:pages.tabs.access,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility,--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended,--div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,categories";
		}

		// add gridelements fields, to make mask work with gridelements out of the box
		$gridelements = '';
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('gridelements')) {
			$gridelements = ', tx_gridelements_container, tx_gridelements_columns';
		}
		if ($tca) {
			foreach ($tca as $elementvalue) {
				$fields = "";
				$label = $elementvalue["shortLabel"]; // Optional shortLabel
				if ($label == "") {
					$label = $elementvalue["label"];
				}
				\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array($label, "mask_" . $elementvalue["key"]), "CType", "mask");
				if ($versionNumber < 7000000) {
					if (is_array($elementvalue["options"])) {
						foreach ($elementvalue["options"] as $optionkey => $optionvalue) {
							if ($optionvalue == "rte") {
								$elementvalue["columns"][$optionkey] .= ";;;richtext[]:rte_transform[mode=ts]";
							}
						}
					}
				}

				if (is_array($elementvalue["columns"])) {
					$fields .= implode(",", $elementvalue["columns"]);
				}
				if ($versionNumber >= 7000000) {
					$GLOBALS['TCA']["tt_content"]["types"]["mask_" . $elementvalue["key"]]["columnsOverrides"]["bodytext"]["defaultExtras"] = 'richtext:rte_transform[mode=ts_css]';
				}
				$GLOBALS['TCA']["tt_content"]["types"]["mask_" . $elementvalue["key"]]["showitem"] = "--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general," . $fields . $defaultTabs . $gridelements;
			}
		}
	}

	/**
	 * Generates the TCA for fields
	 *
	 * @param array $tca
	 * @return string
	 * @todo Delete Empty TCA-Values recursive
	 */
	public function generateFieldsTca($tca) {

		$columns = array();
		if ($tca) {
			foreach ($tca as $tcakey => $tcavalue) {
				if ($tcavalue) {
					foreach ($tcavalue as $fieldkey => $fieldvalue) {
						// Add File-Config for file-field
						if ($fieldkey == "options" && $fieldvalue == "file") {
							$fieldName = $tcakey;
							$customSettingOverride = array(
								 'foreign_types' => array(
									  '0' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
									  '1' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
									  '2' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
									  '3' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
									  '4' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
									  '5' => array(
											'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette, --palette--;;filePalette',
									  ),
								 ),
							);
							$allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
							$columns[$tcakey]["config"] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig($fieldName, $customSettingOverride, $allowedFileExtensions);
						}

						// Fill missing tablename in TCA-Config for inline-fields
						if ($fieldkey == "config" && $tcavalue[$fieldkey]["foreign_table"] == "--inlinetable--") {
							$tcavalue[$fieldkey]["foreign_table"] = $tcakey;
						}

						// merge user inputs with file array
						if (!is_array($columns[$tcakey])) {
							$columns[$tcakey] = array();
						} else {
							\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($columns[$tcakey], $tcavalue);
						}

						// Unset some values that are not needed in TCA
						unset($columns[$tcakey]["options"]);
						unset($columns[$tcakey]["key"]);
						unset($columns[$tcakey]["rte"]);
						unset($columns[$tcakey]["inlineParent"]);

						$columns[$tcakey] = $this->removeBlankOptions($columns[$tcakey]);
						$columns[$tcakey] = $this->replaceKey($columns[$tcakey], $tcakey);
					}
				}
			}
		}
		return $columns;
	}

	/**
	 * Generates the TCA for Inline-Tables
	 *
	 * @param array $tca
	 * @return string
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function generateTableTca($table, $tca) {

		$tcaTemplate = array(
			 'ctrl' => array(
				  'title' => 'IRRE-Table',
				  'label' => 'uid',
				  'tstamp' => 'tstamp',
				  'crdate' => 'crdate',
				  'cruser_id' => 'cruser_id',
				  'dividers2tabs' => TRUE,
				  'versioningWS' => 2,
				  'versioning_followPages' => TRUE,
				  'languageField' => 'sys_language_uid',
				  'transOrigPointerField' => 'l10n_parent',
				  'transOrigDiffSourceField' => 'l10n_diffsource',
				  'delete' => 'deleted',
				  'enablecolumns' => array(
						'disabled' => 'hidden',
						'starttime' => 'starttime',
						'endtime' => 'endtime',
				  ),
				  'searchFields' => '',
				  'dynamicConfigFile' => '',
				  'iconfile' => ''
			 ),
			 'interface' => array(
				  'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, ',
			 ),
			 'types' => array(
				  '1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
			 ),
			 'palettes' => array(
				  '1' => array('showitem' => ''),
			 ),
			 'columns' => array(
				  'sys_language_uid' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
						'config' => array(
							 'type' => 'select',
							 'renderType' => 'selectSingle',
							 'foreign_table' => 'sys_language',
							 'foreign_table_where' => 'ORDER BY sys_language.title',
							 'items' => array(
								  array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
								  array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
							 ),
						),
				  ),
				  'l10n_parent' => array(
						'displayCond' => 'FIELD:sys_language_uid:>:0',
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
						'config' => array(
							 'type' => 'select',
							 'renderType' => 'selectSingle',
							 'items' => array(
								  array('', 0),
							 ),
							 'foreign_table' => 'tx_test_domain_model_murph',
							 'foreign_table_where' => 'AND tx_test_domain_model_murph.pid=###CURRENT_PID### AND tx_test_domain_model_murph.sys_language_uid IN (-1,0)',
						),
				  ),
				  'l10n_diffsource' => array(
						'config' => array(
							 'type' => 'passthrough',
						),
				  ),
				  't3ver_label' => array(
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
						'config' => array(
							 'type' => 'input',
							 'size' => 30,
							 'max' => 255,
						)
				  ),
				  'hidden' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
						'config' => array(
							 'type' => 'check',
						),
				  ),
				  'starttime' => array(
						'exclude' => 1,
						'l10n_mode' => 'mergeIfNotBlank',
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
						'config' => array(
							 'type' => 'input',
							 'size' => 13,
							 'max' => 20,
							 'eval' => 'datetime',
							 'checkbox' => 0,
							 'default' => 0,
							 'range' => array(
								  'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
							 ),
						),
				  ),
				  'endtime' => array(
						'exclude' => 1,
						'l10n_mode' => 'mergeIfNotBlank',
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
						'config' => array(
							 'type' => 'input',
							 'size' => 13,
							 'max' => 20,
							 'eval' => 'datetime',
							 'checkbox' => 0,
							 'default' => 0,
							 'range' => array(
								  'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
							 ),
						),
				  ),
				  'parentid' => array(
						'config' => array(
							 'type' => 'select',
							 'renderType' => 'selectSingle',
							 'items' => array(
								  array('', 0),
							 ),
							 'foreign_table' => 'tt_content',
							 'foreign_table_where' =>
							 'AND tt_content.pid=###CURRENT_PID###
								AND tt_content.sys_language_uid IN (-1,###REC_FIELD_sys_language_uid###)',
						),
				  ),
				  'parenttable' => array(
						'config' => array(
							 'type' => 'passthrough',
						),
				  ),
				  'sorting' => array(
						'config' => array(
							 'type' => 'passthrough',
						),
				  ),
			 ),
		);

		// Create Fields-Array
		$fields = array_keys($tca);
		if ($fields) {
			$fieldsCopy = $fields;
			$firstField = array_pop($fieldsCopy);
		}

		// backwards compatibility for typo3 6.2
		$version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version();
		$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);

		// get fields with rte configuration
		$rteFields = array();
		foreach ($fields as $field) {
			if ($versionNumber >= 7000000) {
				$rteFields[] = $field;
			} else {
				$formType = $this->getFormType($field, "", $table);
				if ($formType == "Richtext") {
					$rteFields[] = $field.= ";;;richtext[]:rte_transform[mode=ts]";
				} else {
					$rteFields[] = $field;
				}
			}
		}

		// get parent table of this inline table
		$parentTable = $this->getFieldType($table);

		// Adjust TCA-Template
		$tableTca = $tcaTemplate;

		$tableTca["ctrl"]["title"] = $table;
		$tableTca["ctrl"]["label"] = $firstField;
		$tableTca["ctrl"]["searchFields"] = implode(",", $fields);
		$tableTca["ctrl"]["iconfile"] = "";
		$tableTca["interface"]["showRecordFieldList"] = "sys_language_uid, l10n_parent, l10n_diffsource, hidden, " . implode(", ", $rteFields);
		$tableTca["types"]["1"]["showitem"] = "sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, " . implode(", ", $rteFields) . ", --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime";

		$tableTca["columns"]["l10n_parent"]["config"]["foreign_table"] = $table;
		$tableTca["columns"]["l10n_parent"]["config"]["foreign_table_where"] = 'AND ' . $table . '.pid=###CURRENT_PID### AND ' . $table . '.sys_language_uid IN (-1,0)';

		$tableTca["columns"]["parentid"]["config"]["foreign_table"] = $parentTable;
		$tableTca["columns"]["parentid"]["config"]["foreign_table_where"] = 'AND ' . $parentTable . '.pid=###CURRENT_PID### AND ' . $parentTable . '.sys_language_uid IN (-1,###REC_FIELD_sys_language_uid###)';

		// Add some stuff we need to make irre work like it should
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($table);
		$GLOBALS["TCA"][$table] = $tableTca;
	}

	/**
	 * Removes all the blank options from the tca
	 * @param array $haystack
	 * @return array
	 */
	public function removeBlankOptions($haystack) {
		foreach ($haystack as $key => $value) {
			if (is_array($value)) {
				$haystack[$key] = $this->removeBlankOptions($haystack[$key]);
			}
			if (empty($haystack[$key])) {
				unset($haystack[$key]);
			}
		}
		return $haystack;
	}

	/**
	 * Returns Inline-Elements of Data-Object
	 *
	 * @param object $data the parent object
	 * @param string $name The name of the irre attribut
	 * @param string $cType The name of the irre attribut
	 * @param string $parentid The name of the irre parentid
	 * @param string $parenttable The table where the parent element is stored
	 * @return array all irre elements of this attribut
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function getInlineElements($data, $name, $cType, $parentid = "parentid", $parenttable = "tt_content") {
		// If this method is called in backend, there is no $GLOBALS['TSFE']
		if (isset($GLOBALS['TSFE']->sys_language_uid)) {
			$sysLangUid = $GLOBALS['TSFE']->sys_language_uid;
			$enableFields = $GLOBALS['TSFE']->cObj->enableFields($name);
		} else {
			$sysLangUid = 0;
			$enableFields = "";
		}

		// by default, the uid of the parent is $data["uid"]
		$parentUid = $data["uid"];

		/*
		 * but if the parent table is the pages, and it isn't the default language
		 * then pages_language_overlay becomes the parenttable
		 * and $data["_PAGES_OVERLAY_UID"] becomes the id of the parent
		 */
		if ($parenttable == "pages" && $GLOBALS['TSFE']->sys_language_uid != 0) {
			$parenttable = "pages_language_overlay";
			$parentUid = $data["_PAGES_OVERLAY_UID"];

			/**
			 * else if the parenttable is tt_content and we are looking for translated
			 * elements and the field _LOCALIZED_UID is available, then use this field
			 * Otherwise we have problems with gridelements and translation
			 */
		} else if ($parenttable == "tt_content" && $GLOBALS['TSFE']->sys_language_uid != 0 && $data["_LOCALIZED_UID"] != "") {
			$parentUid = $data["_LOCALIZED_UID"];
		}

		// fetching the inline elements
		$sql = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
				  "*", $name, $parentid . " = '" . $parentUid .
				  "' AND parenttable = '" . $parenttable .
				  "' AND sys_language_uid IN (-1," . $sysLangUid . ")"
				  . $enableFields, "", "sorting"
		);

		// and recursively add them to an array
		while ($element = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($sql)) {
			$this->addIrreToData($element, $name, $cType);
			$this->addFilesToData($element, $name);
			$elements[] = $element;
		}
		return $elements;
	}

	/**
	 * Generates and sets the tca for all the extended pages
	 *
	 * @param array $tca
	 * @author Benjamin Butschell <bb@webprofil.at>
	 */
	public function setPageTca($tca, &$confVarsFe) {

		// backwards compatibility for typo3 6.2
		$version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version();
		$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);

		// Load all Page-Fields for new Tab in Backend
		$pageFields = array();
		if ($tca) {
			foreach ($tca as $fieldKey => $value) {
				if ($versionNumber >= 7000000) {
					$fieldKeyTca = $fieldKey;
				} else {
					$element = array_pop($this->getElementsWhichUseField($fieldKey, "pages"));
					$type = $this->getFormType($fieldKey, $element["key"], "pages");

					$fieldKeyTca = $fieldKey;
					if ($type == "Richtext") {
						$fieldKeyTca .= ";;;richtext[]:rte_transform[mode=ts]";
					}
				}

				$pageFields[] = $fieldKeyTca;

				// Add addRootLineFields and pageOverlayFields for all pagefields
				$confVarsFe["addRootLineFields"] .= "," . $fieldKey;
				$confVarsFe["pageOverlayFields"] .= "," . $fieldKey;
			}
		}
		$pageFieldString = "--div--;Content-Fields," . implode(",", $pageFields);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', $pageFieldString);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages_language_overlay', $pageFieldString);
	}

}
