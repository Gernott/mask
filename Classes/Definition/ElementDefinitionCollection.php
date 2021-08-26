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

final class ElementDefinitionCollection implements \IteratorAggregate
{
    /**
     * @var array<ElementDefinition>
     */
    private $definitions = [];
    public $table = '';

    public function __construct(string $table = '')
    {
        $this->table = $table;
    }

    public function addElement(ElementDefinition $definition): void
    {
        if (!$this->hasElement($definition->key)) {
            $this->definitions[$definition->key] = $definition;
        }
    }

    public function hasElement(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function getElement(string $key): ElementDefinition
    {
        if ($this->hasElement($key)) {
            return $this->definitions[$key];
        }

        throw new \OutOfBoundsException(sprintf('An element with the key "%s" does not exist in table "%s".', $key, $this->table), 1629292879);
    }

    public static function createFromArray(array $array, string $table): ElementDefinitionCollection
    {
        $elementDefinitionCollection = new self();
        $elementDefinitionCollection->table = $table;
        foreach ($array as $element) {
            $elementDefinitionCollection->addElement(ElementDefinition::createFromArray($element, $table));
        }
        return $elementDefinitionCollection;
    }

    /**
     * @return iterable<ElementDefinition>
     */
    public function getIterator(): iterable
    {
        foreach ($this->definitions as $definition) {
            yield clone $definition;
        }
    }

    public function toArray(): array
    {
        $elements = [];
        foreach ($this->definitions as $element) {
            $elements[$element->key] = $element->toArray();
        }
        return $elements;
    }

    public function count(): int
    {
        return count($this->definitions);
    }
}
