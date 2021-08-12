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

namespace MASK\Mask\Loader;

final class TableDefinitionCollection
{
    /**
     * @var array<TableDefinition>
     */
    protected $tableDefinitions = [];

    public function addTableDefinition(TableDefinition $tableDefinition): void
    {
        if (!isset($this->tableDefinitions[$tableDefinition->getTable()])) {
            $this->tableDefinitions[$tableDefinition->getTable()] = $tableDefinition;
        }
    }

    public function getTableDefinitonByTable(string $table): ?TableDefinition
    {
        return $this->tableDefinitions[$table] ?? null;
    }

    public function toArray(): array
    {
        return array_merge([], ...$this->getTableDefinitionsAsArray());
    }

    public function getTableDefinitionsAsArray(): iterable
    {
        foreach ($this->tableDefinitions as $definition) {
            yield [$definition->getTable() => $definition->toArray()];
        }
    }

    public static function createFromInternalArray(array $tableDefinitionArray): TableDefinitionCollection
    {
        $tcaDefinition = new self();
        foreach ($tableDefinitionArray as $table => $value) {
            $elements = $value['elements'] ?? [];
            $sql = $value['sql'] ?? [];
            $tca = $value['tca'] ?? [];
            $palettes = $value['palettes'] ?? [];
            $tableDefinition = new TableDefinition($table, $tca, $sql, $elements, $palettes);
            $tcaDefinition->addTableDefinition($tableDefinition);
        }

        return $tcaDefinition;
    }
}
