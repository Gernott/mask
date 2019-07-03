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

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
     * @param string[] $sql
     * @return array
     * @throws DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     * @throws \TYPO3\CMS\Core\Database\Schema\Exception\StatementException
     * @throws \TYPO3\CMS\Core\Database\Schema\Exception\UnexpectedSignalReturnValueTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function performDbUpdates(array $sqlStatements)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $schemaMigrator = GeneralUtility::makeInstance(SchemaMigrator::class);

        $sqlUpdateSuggestions = $schemaMigrator->getUpdateSuggestions($sqlStatements);
        $hasErrors = false;

        foreach ($sqlUpdateSuggestions as $connectionName => $updateConnection) {
            $connection = $connectionPool->getConnectionByName($connectionName);
            foreach ($updateConnection as $updateStatements) {
                foreach ($updateStatements as $statement) {
                    try {
                        $connection->exec($statement);
                    } catch (DBALException $exception) {
                        $hasErrors = true;
                        GeneralUtility::devlog(
                            'SQL error',
                            'mask',
                            0,
                            [
                                'statement' => $statement,
                                'error' => $exception->getMessage()
                            ]);
                    }
                }
            }
        }

        if ($hasErrors) {
            return [
                'error' => 'Database could not be updated. Please check it in the update wizard of the install tool'
            ];
        }

        return ['success' => 'Database was successfully updated'];
    }

    /**
     * Updates the database if necessary
     *
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return array
     */
    public function updateDatabase()
    {
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        $json = $storageRepository->load();
        $sqlStatements = $this->getSqlByConfiguration($json);
        if (count($sqlStatements) > 0) {
            $response = $this->performDbUpdates($sqlStatements);
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
                    if (array_search($type, $nonIrreTables) === false) {
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

                                        // if this field is a content field, also add parent columns
                                        $fieldType = $fieldHelper->getFormType($field, "", $table);
                                        if ($fieldType == "Content") {
                                            $sql_content[] = "CREATE TABLE tt_content (\n\t" . $field . "_parent" . " " . $definition . ",\n\t" . "KEY " . $field . " (" . $field . "_parent,pid,deleted)" . "\n);\n";
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
