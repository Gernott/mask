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

/**
 * There was a bug in older Mask versions, where inline fields weren't removed
 * completely from the configuration. This trait searches and removes such
 * orphans, so they won't trash the storage and cause problems in other areas.
 *
 * See this issue for more details: https://github.com/Gernott/mask/issues/265
 */
class OrphanRemoverMigration implements MigrationInterface
{
    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        $candidatesForRemoval = [];
        // Find all tables with no tca nor sql definition
        foreach ($tableDefinitionCollection as $tableDefinition) {
            if (
                $tableDefinition->tca->count() === 0
                && $tableDefinition->sql->count() === 0
            ) {
                $candidatesForRemoval[] = $tableDefinition;
            }
        }

        // If there are no candidates, return the definition as is.
        if (empty($candidatesForRemoval)) {
            return $tableDefinitionCollection;
        }

        $tablesToRemove = [];
        // Check if the candidates are in use in at least one element
        foreach ($candidatesForRemoval as $candidate) {
            if ($tableDefinitionCollection->getElementsWhichUseField($candidate->table)->count() > 0) {
                continue;
            }
            $tablesToRemove[] = $candidate;
        }

        // If there are no tables to remove, return the definition as is.
        if (empty($tablesToRemove)) {
            return $tableDefinitionCollection;
        }

        // Create a new TableDefinitionCollection and exclude orphan tables
        $newTableDefinitionCollection = new TableDefinitionCollection();
        foreach ($tableDefinitionCollection as $tableDefinition) {
            if (!in_array($tableDefinition, $tablesToRemove, true)) {
                $newTableDefinitionCollection->addTable($tableDefinition);
            }
        }

        return $newTableDefinitionCollection;
    }

    public function forVersionBelow(): string
    {
        return '7.2.0';
    }
}
