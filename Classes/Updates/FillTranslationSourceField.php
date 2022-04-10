<?php

declare(strict_types=1);

namespace MASK\Mask\Updates;

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

use MASK\Mask\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Fill translation source field (l10n_source)
 */
class FillTranslationSourceField implements UpgradeWizardInterface
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

    /**
     * @var string
     */
    protected $title = 'Fill translation source field (l10n_source)';

    public function getIdentifier(): string
    {
        return 'fillTranslationSourceField';
    }

    public function getTitle(): string
    {
        return 'EXT:mask: Fill translation source field (l10n_source)';
    }

    public function getDescription(): string
    {
        return 'Fills the translation source for Mask tables.';
    }

    public function executeUpdate(): bool
    {
        foreach ($this->tableDefinitionCollection->getCustomTables() as $maskTable) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($maskTable->table);
            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder->update($maskTable->table, 't')
                ->set('t.l10n_source', 't.l10n_parent', false)
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('t.l10n_parent', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->eq('t.l10n_source', $queryBuilder->createNamedParameter(0))
                    )
                );
            $queryBuilder->execute();
        }

        return true;
    }

    public function updateNecessary(): bool
    {
        foreach ($this->tableDefinitionCollection->getCustomTables() as $maskTable) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($maskTable->table);
            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->getRestrictions()->removeAll();
            $query = $queryBuilder->count('uid')
                ->from($maskTable->table)
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt('l10n_parent', $queryBuilder->createNamedParameter(0)),
                        $queryBuilder->expr()->eq('l10n_source', $queryBuilder->createNamedParameter(0))
                    )
                );

            $result = $query->execute();
            if (method_exists($result, 'fetchOne')) {
                $count = (int)$result->fetchOne();
            } else {
                $count = (int)$result->fetchColumn();
            }
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
