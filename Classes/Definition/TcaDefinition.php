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

final class TcaDefinition implements \IteratorAggregate
{
    /**
     * @var array<TcaFieldDefinition>
     */
    private $definitions = [];

    /**
     * @var string
     */
    public $table = '';

    public function __clone()
    {
        $this->definitions = array_map(function (TcaFieldDefinition $tcaFieldDefinition) {
            return clone $tcaFieldDefinition;
        }, $this->definitions);
    }

    public function addField(TcaFieldDefinition $definition): void
    {
        if (!$this->hasField($definition->fullKey)) {
            $this->definitions[$definition->fullKey] = $definition;
        }
    }

    public function hasField(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function getField(string $key): TcaFieldDefinition
    {
        if ($this->hasField($key)) {
            return $this->definitions[$key];
        }

        throw new \OutOfBoundsException(sprintf('A field with the key "%s" does not exist in table "%s".', $key, $this->table), 1629276302);
    }

    public static function createFromArray(array $tca, string $table): TcaDefinition
    {
        $tcaDefinition = new self();
        $tcaDefinition->table = $table;
        foreach ($tca as $definition) {
            $tcaDefinition->addField(TcaFieldDefinition::createFromFieldArray($definition));
        }
        return $tcaDefinition;
    }

    public function getKeys(): array
    {
        return array_keys($this->definitions);
    }

    private function getOrderedFields(): array
    {
        $fields = $this->definitions;
        usort($fields, static function (TcaFieldDefinition $fieldA, TcaFieldDefinition $fieldB) {
            return $fieldA->order <=> $fieldB->order;
        });
        return $fields;
    }

    /**
     * @return \Traversable|TcaFieldDefinition[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getOrderedFields());
    }

    public function toArray(): array
    {
        $fields = [];
        foreach ($this->definitions as $definition) {
            $fields[$definition->fullKey] = $definition->toArray();
        }
        return $fields;
    }

    public function count(): int
    {
        return count($this->definitions);
    }
}
