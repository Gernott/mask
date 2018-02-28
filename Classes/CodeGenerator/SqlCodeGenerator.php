<?php

namespace MASK\Mask\CodeGenerator;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
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

/**
 * Generates all the sql needed for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class SqlCodeGenerator extends \MASK\Mask\CodeGenerator\AbstractCodeGenerator
{

    /**
     * Performs updates, adjusted function from extension_builder
     *
     * @param array $params
     * @return type
     */
    protected function performDbUpdates($params, $sql)
    {

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
     * Checks for DB-Updates, adjusted function from extension_builder
     *
     * @param string $extensionKey
     * @param string $sqlContent
     * @return void
     */
    protected function checkForDbUpdate($extensionKey, $sqlContent)
    {
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
//            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
            $fieldDefinitionsFromFile = $sqlHandler->getFieldDefinitions_fileContent($sqlContent);
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
     * function from extension_builder
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Updates the database if necessary
     *
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return array
     */
    public function updateDatabase()
    {
        $params["extensionKey"] = "mask";
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        $json = $storageRepository->load();
        $sqlStatements = $this->getSqlByConfiguration($json);
        if (count($sqlStatements) > 0) {
            $response = $this->performDbUpdates($params, implode(" ", $sqlStatements));
        }
        return $response;
    }

    /**
     * returns sql statements of all elements and pages and irre
     * @param array $json
     * @return string
     */
    public function getSqlByConfiguration($json)
    {
        $sql_content = array();
        $types = array_keys($json);
        $nonIrreTables = array("pages", "tt_content");
        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');

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
							 deleted SMALLINT unsigned DEFAULT '0' NOT NULL,
							 hidden SMALLINT unsigned DEFAULT '0' NOT NULL,
							 starttime int(11) unsigned DEFAULT '0' NOT NULL,
							 endtime int(11) unsigned DEFAULT '0' NOT NULL,

							 t3ver_oid int(11) DEFAULT '0' NOT NULL,
							 t3ver_id int(11) DEFAULT '0' NOT NULL,
							 t3ver_wsid int(11) DEFAULT '0' NOT NULL,
							 t3ver_label varchar(255) DEFAULT '' NOT NULL,
							 t3ver_state SMALLINT DEFAULT '0' NOT NULL,
							 t3ver_stage int(11) DEFAULT '0' NOT NULL,
							 t3ver_count int(11) DEFAULT '0' NOT NULL,
							 t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
							 t3ver_move_id int(11) DEFAULT '0' NOT NULL,

							 sys_language_uid int(11) DEFAULT '0' NOT NULL,
							 l10n_parent int(11) DEFAULT '0' NOT NULL,
							 l10n_diffsource mediumblob,
							 l10n_state text,

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

                                        // if this field is a content field, also add parent columns
                                        $fieldType = $fieldHelper->getFormType($field, "", $table);
                                        if ($fieldType == "Content") {
                                            $sql_content[] = "CREATE TABLE tt_content (\n\t" . $field . "_parent" . " " . $definition . "\n);\n";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $sql_content;
    }

    /**
     * Returns the SQL of all elements and merges it with already existing
     * sql statements for the signal slot dispatcher
     *
     * @param array $sqlString
     * @return array
     */
    public function addDatabaseTablesDefinition(array $sqlString)
    {
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        $json = $storageRepository->load();
        $sql = $this->getSqlByConfiguration($json);
        $mergedSqlString = array_merge($sqlString, $sql);
        return array('sqlString' => $mergedSqlString);
    }
}
