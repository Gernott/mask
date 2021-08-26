<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace MASK\Mask\CodeGenerator;

use Doctrine\DBAL\Exception;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates all the sql needed for mask content elements
 * @internal
 */
class SqlCodeGenerator
{
    /**
     * @var SchemaMigrator
     */
    protected $schemaMigrator;

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, SchemaMigrator $schemaMigrator)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->schemaMigrator = $schemaMigrator;
    }

    /**
     * Updates the database if necessary
     */
    public function updateDatabase(): array
    {
        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $sqlStatements = $sqlReader->getCreateTableStatementArray($sqlReader->getTablesDefinitionString());

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $schemaMigrator = GeneralUtility::makeInstance(SchemaMigrator::class);

        $sqlUpdateSuggestionsPerConnection = $schemaMigrator->getUpdateSuggestions($sqlStatements);
        foreach ($sqlUpdateSuggestionsPerConnection as $connectionName => $updateSuggestions) {
            unset($updateSuggestions['tables_count'], $updateSuggestions['change_currentValue']);
            $updateSuggestions = array_merge(...array_values($updateSuggestions));
            $connection = $connectionPool->getConnectionByName($connectionName);
            foreach ($updateSuggestions as $statement) {
                try {
                    $connection->executeStatement($statement);
                } catch (Exception $exception) {
                    return [
                        'error' => $exception->getPrevious()->getMessage()
                    ];
                }
            }
        }

        return ['success' => 'Database was successfully updated.'];
    }

    /**
     * returns sql statements of all elements and pages and irre
     */
    protected function getSqlByConfiguration(): array
    {
        $sql = [];

        // Generate SQL-Statements
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            if (empty($tableDefinition->sql)) {
                continue;
            }

            foreach ($tableDefinition->sql as $column) {
                $table = $this->tableDefinitionCollection->getTableByField($column->column);
                $fieldType = $this->tableDefinitionCollection->getFieldType($column->column, $table);
                if ($fieldType->equals(FieldType::INLINE) && !$this->tableDefinitionCollection->hasTable($column->column)) {
                    continue;
                }
                $sql[] = 'CREATE TABLE ' . $tableDefinition->table . " (\n\t" . $column->column . ' ' . $column->sqlDefinition . "\n);\n";
                // if this field is a content field, also add parent columns
                if ($fieldType->equals(FieldType::CONTENT)) {
                    $parentField = AffixUtility::addMaskParentSuffix($column->column);
                    $sql[] = "CREATE TABLE tt_content (\n\t" . $parentField . ' ' . $column->sqlDefinition . ",\n\t" . 'KEY ' . $column->column . ' (' . $parentField . ', deleted, hidden, sorting)' . "\n);\n";
                }
            }

            // If type/table is an irre table, then create table for it
            if (AffixUtility::hasMaskPrefix($tableDefinition->table)) {
                $sql[] = "CREATE TABLE {$tableDefinition->table} (
                         parentid int(11) DEFAULT '0' NOT NULL,
                         parenttable varchar(255) DEFAULT '',
                     );";
            }
        }

        return $sql;
    }

    /**
     * Adds the SQL for all elements to the psr-14 AlterTableDefinitionStatementsEvent event.
     *
     * @param AlterTableDefinitionStatementsEvent $event
     */
    public function addDatabaseTablesDefinition(AlterTableDefinitionStatementsEvent $event): void
    {
        $event->setSqlData(array_merge($event->getSqlData(), $this->getSqlByConfiguration()));
    }
}
