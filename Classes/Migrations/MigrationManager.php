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

namespace MASK\Mask\Migrations;

use MASK\Mask\Definition\TableDefinitionCollection;

class MigrationManager
{
    /**
     * @var array<class-string, MigrationInterface>
     */
    protected $migrations = [];

    /**
     * @param iterable<MigrationInterface> $migrations
     */
    public function __construct(iterable $migrations)
    {
        foreach ($migrations as $migration) {
            $this->migrations[get_class($migration)] = $migration;
        }
    }

    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        foreach ($this->migrations as $migration) {
            if (!$this->needsMigration($migration, $tableDefinitionCollection)) {
                continue;
            }
            $tableDefinitionCollection = $migration->migrate($tableDefinitionCollection);
            $tableDefinitionCollection->migrationDone();
        }

        $tableDefinitionCollection->setToCurrentVersion();
        return $tableDefinitionCollection;
    }

    protected function needsMigration(MigrationInterface $migration, TableDefinitionCollection $tableDefinitionCollection): bool
    {
        $currentVersion = (new TableDefinitionCollection())->getVersion();
        // Skip repeatable migrations, if the definition version equals the most recent version.
        if (
            $migration instanceof RepeatableMigrationInterface
            && version_compare($tableDefinitionCollection->getVersion(), $currentVersion) === 0
        ) {
            return false;
        }

        // Skip normal migrations, if the definition version is equal or above the migration version.
        if (
            !$migration instanceof RepeatableMigrationInterface
            && version_compare($tableDefinitionCollection->getVersion(), $migration->forVersionBelow()) >= 0
        ) {
            return false;
        }

        return true;
    }
}
