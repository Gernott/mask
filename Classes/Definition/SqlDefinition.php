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

namespace MASK\Mask\Definition;

final class SqlDefinition implements \IteratorAggregate
{
    /**
     * @var string
     */
    public $table = '';

    /**
     * @var array<SqlColumnDefinition>
     */
    private $definitions = [];

    public function __clone()
    {
        $this->definitions = array_map(function (SqlColumnDefinition $sqlColumnDefinition) {
            return clone $sqlColumnDefinition;
        }, $this->definitions);
    }

    public function addColumn(SqlColumnDefinition $columnDefinition): void
    {
        if (!$this->hasColumn($columnDefinition->column)) {
            $this->definitions[$columnDefinition->column] = $columnDefinition;
        }
    }

    public function hasColumn(string $column): bool
    {
        return isset($this->definitions[$column]);
    }

    public function getColumn(string $column): SqlColumnDefinition
    {
        if ($this->hasColumn($column)) {
            return $this->definitions[$column];
        }

        throw new \OutOfBoundsException(sprintf('The column "%s" does not exist in table "%s".', $column, $this->table), 1629276302);
    }

    public static function createFromArray(array $array, string $table): SqlDefinition
    {
        $sqlDefinition = new self();
        $sqlDefinition->table = $table;
        foreach ($array as $column => $sql) {
            $columnDefinition = $sql[$table][$column];
            $sqlDefinition->addColumn(new SqlColumnDefinition($column, $columnDefinition));
        }
        return $sqlDefinition;
    }

    /**
     * @return \Traversable|SqlColumnDefinition[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->definitions);
    }

    public function toArray(): array
    {
        $sqlColumnDefinitions = [];
        foreach ($this->definitions as $sqlColumnDefinition) {
            $sqlColumnDefinitions[$sqlColumnDefinition->column][$this->table] = $sqlColumnDefinition->toArray();
        }
        return $sqlColumnDefinitions;
    }

    public function count(): int
    {
        return count($this->definitions);
    }
}
