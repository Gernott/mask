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

namespace MASK\Mask\Updates;

use Doctrine\DBAL\Exception\InvalidFieldNameException;
use MASK\Mask\Definition\TableDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class MigrateContentFields implements UpgradeWizardInterface
{
    protected TableDefinitionCollection $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

    public function getIdentifier(): string
    {
        return 'migrateContentFields';
    }

    public function getTitle(): string
    {
        return 'Migrate Mask Content fields';
    }

    public function getDescription(): string
    {
        return 'Migrates fields of type "Content" to new persistence structure.';
    }

    public function executeUpdate(): bool
    {
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca ?? [] as $tcaDefinition) {
                if (!$tcaDefinition->hasFieldType()) {
                    continue;
                }
                if ($tcaDefinition->getFieldType()->equals(FieldType::CONTENT)) {
                    $this->migrateField($tcaDefinition, $tableDefinition);
                }
            }
        }
        return true;
    }

    public function migrateField(TcaFieldDefinition $tcaFieldDefinition, TableDefinition $tableDefinition): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        $legacyParentColumnName = AffixUtility::addMaskParentSuffix($tcaFieldDefinition->fullKey);

        $queryBuilder
            ->update('tt_content')
            ->set('tx_mask_content_parent_uid', $queryBuilder->quoteIdentifier($legacyParentColumnName), false)
            ->set('tx_mask_content_tablenames', $tableDefinition->table)
            ->set('tx_mask_content_role', $tcaFieldDefinition->fullKey)
            ->where($queryBuilder->expr()->neq($legacyParentColumnName, $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)))
            ->executeStatement();
    }

    public function updateNecessary(): bool
    {
        foreach ($this->tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca ?? [] as $tcaFieldDefinition) {
                if (!$tcaFieldDefinition->hasFieldType()) {
                    continue;
                }
                if ($tcaFieldDefinition->getFieldType()->equals(FieldType::CONTENT)) {
                    $legacyParentColumnName = AffixUtility::addMaskParentSuffix($tcaFieldDefinition->fullKey);
                    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getQueryBuilderForTable('tt_content');
                    try {
                        $queryBuilder
                            ->select($legacyParentColumnName)
                            ->from('tt_content')
                            ->executeQuery();
                    } catch (InvalidFieldNameException $e) {
                        // The legacy field does not exist, no update necessary.
                        continue;
                    }

                    return true;
                }
            }
        }
        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
