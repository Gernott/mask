<?php

namespace MASK\Mask\Domain\Repository;

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
 * Repository for \TYPO3\CMS\Extbase\Domain\Model\Tca.
 *
 * @api
 */
class StorageRepository {

	/**
	 * MaskUtility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 */
	protected $utility;

	/**
	 * Load Storage
	 *
	 * @return array
	 */
	public function load() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		if (file_exists(PATH_site . $extConf["json"]) && is_file(PATH_site . $extConf["json"])) {
			return json_decode(file_get_contents(PATH_site . $extConf["json"]), true);
		}
	}

	/**
	 * Load Field
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return array
	 */
	public function loadField($type, $key) {
		$json = $this->load();
		return $json[$type]["tca"][$key];
	}

	/**
	 * Loads all the inline fields of an inline-field, recursively!
	 *
	 * @param string $parentKey key of the inline-field
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @return array
	 */
	public function loadInlineFields($parentKey) {
		$json = $this->load();
		$inlineFields = array();
		foreach ($json as $table) {
			if ($table["tca"]) {
				foreach ($table["tca"] as $key => $tca) {
					if ($tca["inlineParent"] == $parentKey) {
						if ($tca["config"]["type"] == "inline") {
							$tca["inlineFields"] = $this->loadInlineFields($key);
						}
						$tca["maskKey"] = "tx_mask_" . $tca["key"];
						$inlineFields[] = $tca;
					}
				}
			}
		}
		return $inlineFields;
	}

	/**
	 * Load Element with all the field configurations
	 *
	 * @return array
	 */
	public function loadElement($type, $key) {
		$json = $this->load();
		$fields = array();

		if (count($json[$type]["elements"][$key]["columns"]) > 0) {
			foreach ($json[$type]["elements"][$key]["columns"] as $fieldName) {
				$fields[$fieldName] = $json[$type]["tca"][$fieldName];
			}
		}
		if (count($fields) > 0) {
			$json[$type]["elements"][$key]["tca"] = $fields;
		}
		return $json[$type]["elements"][$key];
	}

	/**
	 * Adds new Content-Element
	 *
	 * @param array $content
	 */
	public function add($content) {
		// Load
		$json = $this->load();

		// Create JSON elements Array:
		foreach ($content["elements"] as $key => $value) {
			// delete columns and labels of irre-fields from elements
			if ($key == "columns" || $key == "labels") {
				foreach ($value as $index => $column) {
					if (!$content["tca"][$index]["inlineParent"]) {
						$contentColumns[] = $column;
					} else {
						unset($value[$index]);
						unset($value[$index]);
					}
				}
			}
			$json[$content["type"]]["elements"][$content["elements"]["key"]][$key] = $value;
		}

		$contentColumns = array();
		$columns = array();

		// delete columns and labels of irre-fields from elements
		if ($content["elements"]["columns"]) {
			foreach ($content["elements"]["columns"] as $index => $column) {
				if (!$content["tca"][$index]["inlineParent"]) {
					$contentColumns[] = $column;
				} else {
					unset($content["elements"]["columns"][$index]);
					unset($content["elements"]["labels"][$index]);
				}
				$columns[] = $column;
			}
		}

		// Create JSON sql Array:
		if (is_array($content["sql"])) {
			foreach ($content["sql"] as $table => $sqlArray) {
				foreach ($sqlArray as $index => $type) {
					$fieldname = "tx_mask_" . $columns[$index];
					$json[$table]["sql"][$fieldname][$table][$fieldname] = $type;
				}
			}
		}

		// Create JSON tca Array:
		if (is_array($content["tca"])) {


			foreach ($content["tca"] as $key => $value) {
				$inlineField = FALSE;

				// if this field is inline-field
				if ($value["inlineParent"]) {
					$type = $value["inlineParent"];
					$inlineField = TRUE;
				} else {
					$type = $content["type"];
				}

				$json[$type]["tca"][$columns[$key]] = $value;

				// add rte flag if inline and rte
				if ($inlineField) {
					if ($content["elements"]["options"][$key] == "rte") {
						$json[$type]["tca"][$columns[$key]]["rte"] = "1";
					}
				}

				// Only add columns to elements if it is no inlinefield
				if (!$inlineField) {
					$json[$type]["elements"][$content["elements"]["key"]]["columns"][$key] = "tx_mask_" . $columns[$key];
				}
				$json[$type]["tca"]["tx_mask_" . $columns[$key]] = $json[$type]["tca"][$columns[$key]];
				$json[$type]["tca"]["tx_mask_" . $columns[$key]]["key"] = $columns[$key];
				unset($json[$type]["tca"][$columns[$key]]);
			}
		}

		// Save
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		$handle = fopen(PATH_site . $extConf["json"], "w");
		$encodedJson = "";

		// Return JSON formatted in PHP 5.4.0 and higher
		if (version_compare(phpversion(), '5.4.0', '<')) {
			$encodedJson = json_encode($json);
		} else {
			$encodedJson = json_encode($json, JSON_PRETTY_PRINT);
		}
		fwrite($handle, $encodedJson);
	}

	/**
	 * Removes Content-Element
	 *
	 * @param string $type
	 * @param string $key
	 */
	public function remove($type, $key) {
		// Load
		$json = $this->load();

		// Remove
		$columns = $json[$type]["elements"][$key]["columns"];
		unset($json[$type]["elements"][$key]);
		if (is_array($columns)) {
			foreach ($columns as $id => $field) {
				$json = $this->removeField($type, $field, $json);
			}
		}
		// Save
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mask']);
		$handle = fopen(PATH_site . $extConf["json"], "w");
		fwrite($handle, json_encode($json));
	}

	/**
	 * Removes a field from the json, also recursively all inline-fields
	 * @author Benjamin Butschell <bb@webprofil.at>
	 *
	 * @param string $table
	 * @param string $field
	 * @param array $json
	 * @return array
	 */
	private function removeField($table, $field, $json) {

		// init utility
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->utility = new \MASK\Mask\Utility\MaskUtility($this->objectManager, $this);

		// and unset field only if not needed anymore
		$elementsInUse = array();
		if ($json["tt_content"]["elements"]) {
			foreach ($json["tt_content"]["elements"] as $element) {
				if ($element["columns"]) {
					foreach ($element["columns"] as $column) {
						if ($column == $field) {
							$elementsInUse[] = $element;
						}
					}
				}
			}
		}
		// Recursively delete all inline-fields
		if ($json[$table]["tca"][$field]["config"]["type"] == "inline") {
			$inlineFields = $this->loadInlineFields($field);
			if ($inlineFields) {
				foreach ($inlineFields as $inlineField) {
					$json = $this->removeField($inlineField["inlineParent"], "tx_mask_" . $inlineField["key"], $json);
				}
			}
		}

		if (count($elementsInUse) < 1) {

			unset($json[$table]["tca"][$field]);
			unset($json[$table]["sql"][$field]);

			// If field is of type file, also delete entry in sys_file_reference
			if ($this->utility->getFormType($field) == "File") {
				unset($json["sys_file_reference"]["sql"][$field]);
				$json = $this->cleanTable("sys_file_reference", $json);
			}
		}
		return $this->cleanTable($table, $json);
	}

	/**
	 * Deletes all the empty settings of a table
	 *
	 * @author Benjamin Butschell <bb@webprofil.at>
	 * @param string $table
	 * @param array $json
	 * @return array
	 */
	private function cleanTable($table, $json) {
		if (count($json[$table]["tca"]) < 1) {
			unset($json[$table]["tca"]);
		}
		if (count($json[$table]["sql"]) < 1) {
			unset($json[$table]["sql"]);
		}
		if (count($json[$table]) < 1) {
			unset($json[$table]);
		}
		return $json;
	}

	/**
	 * Updates Content-Element in Storage-Repository
	 *
	 * @param array $content
	 */
	public function update($content) {
		$this->remove($content["type"], $content["orgkey"]);
		$this->add($content);
	}

	/**
	 * returns sql statements of all elements and pages and irre
	 * @return array
	 */
	public function loadSql() {
		$json = $this->load();
		$sql_content = array();
		$types = array_keys($json);
		$nonIrreTables = array("pages", "tt_content");

		// Generate SQL-Statements
		if ($types) {
			foreach ($types as $type) {
				if ($json[$type]["sql"]) {

					// If type/table is an irre table, then create table for it
					if (array_search($type, $nonIrreTables) === FALSE) {
						$sql_content[] = "CREATE TABLE " . $type . " (

							 uid int(11) NOT NULL auto_increment,
							 pid int(11) DEFAULT '0' NOT NULL,

							 tstamp int(11) unsigned DEFAULT '0' NOT NULL,
							 crdate int(11) unsigned DEFAULT '0' NOT NULL,
							 cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
							 deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
							 hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
							 starttime int(11) unsigned DEFAULT '0' NOT NULL,
							 endtime int(11) unsigned DEFAULT '0' NOT NULL,

							 t3ver_oid int(11) DEFAULT '0' NOT NULL,
							 t3ver_id int(11) DEFAULT '0' NOT NULL,
							 t3ver_wsid int(11) DEFAULT '0' NOT NULL,
							 t3ver_label varchar(255) DEFAULT '' NOT NULL,
							 t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
							 t3ver_stage int(11) DEFAULT '0' NOT NULL,
							 t3ver_count int(11) DEFAULT '0' NOT NULL,
							 t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
							 t3ver_move_id int(11) DEFAULT '0' NOT NULL,

							 sys_language_uid int(11) DEFAULT '0' NOT NULL,
							 l10n_parent int(11) DEFAULT '0' NOT NULL,
							 l10n_diffsource mediumblob,

							 PRIMARY KEY (uid),
							 KEY parent (pid),
							 KEY t3ver_oid (t3ver_oid,t3ver_wsid),
							 KEY language (l10n_parent,sys_language_uid),

							 parentid int(11) DEFAULT '0' NOT NULL,
							 parenttable varchar(255) DEFAULT '',
							 sorting	int(11) DEFAULT '0' NOT NULL,

						 );\n";
					}

					foreach ($json[$type]["sql"] as $field) {
						if ($field) {
							foreach ($field as $table => $fields) {
								if ($fields) {
									foreach ($fields as $field => $definition) {
										$sql_content[] = "CREATE TABLE " . $table . " (\n\t" . $field . " " . $definition . "\n);\n";

										// every statement for pages, also for pages_language_overlay
										if ($table == "pages") {
											$sql_content[] = "CREATE TABLE pages_language_overlay (\n\t" . $field . " " . $definition . "\n);\n";
										}
									}
								}
							}
						}
					}
				}
			}
		}
		// Parentfield
		$sql_content[] = "CREATE TABLE tt_content (\n\ttx_mask_content_parent int(11) unsigned NOT NULL DEFAULT '0'\n);\n";
		return $sql_content;
	}

}
