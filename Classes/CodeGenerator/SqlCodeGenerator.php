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

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\SchemaException;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\Database\Schema\Exception\StatementException;
use TYPO3\CMS\Core\Database\Schema\Exception\UnexpectedSignalReturnValueTypeException;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates all the sql needed for mask content elements
 */
class SqlCodeGenerator
{
    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var SchemaMigrator
     */
    protected $schemaMigrator;

    public function __construct(StorageRepository $storageRepository, SchemaMigrator $schemaMigrator)
    {
        $this->storageRepository = $storageRepository;
        $this->schemaMigrator = $schemaMigrator;
    }

    /**
     * Updates the database if necessary
     *
     * @return array
     * @throws DBALException
     * @throws SchemaException
     * @throws StatementException
     * @throws UnexpectedSignalReturnValueTypeException
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
                    $connection->executeUpdate($statement);
                } catch (DBALException $exception) {
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
     * @param array $json
     * @return array
     */
    public function getSqlByConfiguration($json): array
    {
        $sql_content = [];

        // Generate SQL-Statements
        foreach ($json as $type => $_) {
            if (!$json[$type]['sql'] ?? false) {
                continue;
            }

            foreach ($json[$type]['sql'] as $field) {
                foreach ($field ?? [] as $table => $fields) {
                    foreach ($fields ?? [] as $fieldKey => $definition) {
                        $fieldType = $this->storageRepository->getFormType($fieldKey, '', $table);
                        if ($fieldType == FieldType::INLINE && !array_key_exists($fieldKey, $json)) {
                            continue;
                        }
                        $sql_content[] = 'CREATE TABLE ' . $table . " (\n\t" . $fieldKey . ' ' . $definition . "\n);\n";
                        // if this field is a content field, also add parent columns
                        if ($fieldType == FieldType::CONTENT) {
                            $parentField = AffixUtility::addMaskParentSuffix($fieldKey);
                            $sql_content[] = "CREATE TABLE tt_content (\n\t" . $parentField . ' ' . $definition . ",\n\t" . 'KEY ' . $fieldKey . ' (' . $parentField . ',pid)' . "\n);\n";
                        }
                    }
                }
            }

            // If type/table is an irre table, then create table for it
            if (AffixUtility::hasMaskPrefix($type)) {
                $sql_content[] = "CREATE TABLE $type (
                         parentid int(11) DEFAULT '0' NOT NULL,
                         parenttable varchar(255) DEFAULT '',
                     );";
            }
        }

        return $sql_content;
    }

    /**
     * Adds the SQL for all elements to the psr-14 AlterTableDefinitionStatementsEvent event.
     *
     * @param AlterTableDefinitionStatementsEvent $event
     */
    public function addDatabaseTablesDefinition(AlterTableDefinitionStatementsEvent $event): void
    {
        $json = $this->storageRepository->load();
        $sql = $this->getSqlByConfiguration($json);
        $event->setSqlData(array_merge($event->getSqlData(), $sql));
    }
}
