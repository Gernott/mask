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

final class PaletteDefinitionCollection implements \IteratorAggregate
{
    /**
     * @var array<PaletteDefinition>
     */
    private $definitions = [];

    /**
     * @var string
     */
    public $table = '';

    public function __clone()
    {
        $this->definitions = array_map(function (PaletteDefinition $paletteDefinition) {
            return clone $paletteDefinition;
        }, $this->definitions);
    }

    public function addPalette(PaletteDefinition $definition): void
    {
        if (!$this->hasPalette($definition->key)) {
            $this->definitions[$definition->key] = $definition;
        }
    }

    public function hasPalette(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    public function getPalette(string $key): PaletteDefinition
    {
        if ($this->hasPalette($key)) {
            return $this->definitions[$key];
        }

        throw new \OutOfBoundsException(sprintf('A palette with the key "%s" does not exist in table "%s".', $key, $this->table), 1629293912);
    }

    public static function createFromArray(array $array, string $table): PaletteDefinitionCollection
    {
        $paletteDefinitionCollection = new self();
        $paletteDefinitionCollection->table = $table;
        foreach ($array as $key => $palette) {
            $paletteDefinitionCollection->addPalette(
                new PaletteDefinition($key, $palette['label'] ?? '', $palette['description'] ?? '', $palette['showitem'] ?? [])
            );
        }
        return $paletteDefinitionCollection;
    }

    /**
     * @return \Traversable|PaletteDefinition[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->definitions);
    }

    public function toArray(): array
    {
        $palettes = [];
        foreach ($this->definitions as $palette) {
            $palettes[$palette->key] = $palette->toArray();
        }
        return $palettes;
    }

    public function count(): int
    {
        return count($this->definitions);
    }
}
