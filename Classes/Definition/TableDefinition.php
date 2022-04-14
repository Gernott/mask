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

final class TableDefinition
{
    /**
     * @var string
     */
    public $table = '';

    /**
     * @var ElementDefinitionCollection|null
     */
    public $elements;

    /**
     * @var SqlDefinition|null
     */
    public $sql;

    /**
     * @var TcaDefinition|null
     */
    public $tca;

    /**
     * @var PaletteDefinitionCollection|null
     */
    public $palettes;

    public function __clone()
    {
        if ($this->elements instanceof ElementDefinitionCollection) {
            $this->elements = clone $this->elements;
        }
        if ($this->sql instanceof SqlDefinition) {
            $this->sql = clone $this->sql;
        }
        if ($this->tca instanceof TcaDefinition) {
            $this->tca = clone $this->tca;
        }
        if ($this->palettes instanceof PaletteDefinitionCollection) {
            $this->palettes = clone $this->palettes;
        }
    }

    public static function createFromTableArray(string $table, array $definition): TableDefinition
    {
        if ($table === '') {
            throw new \InvalidArgumentException('The name of the table must not be empty.', 1628672227);
        }

        $tableDefinition = new self();
        $tableDefinition->table = $table;

        $tableDefinition->tca = TcaDefinition::createFromArray($definition['tca'] ?? [], $table);
        $tableDefinition->sql = SqlDefinition::createFromArray($definition['sql'] ?? [], $table);
        $tableDefinition->elements = ElementDefinitionCollection::createFromArray($definition['elements'] ?? [], $table);
        $tableDefinition->palettes = PaletteDefinitionCollection::createFromArray($definition['palettes'] ?? [], $table);

        // Add core fields (compatibility layer).
        foreach ($tableDefinition->elements as $element) {
            foreach ($element->columns as $column) {
                if (!$tableDefinition->tca->hasField($column)) {
                    $tcaFieldDefinition = new TcaFieldDefinition();
                    $tcaFieldDefinition->key = $column;
                    $tcaFieldDefinition->fullKey = $column;
                    $tcaFieldDefinition->isCoreField = true;
                    $tableDefinition->tca->addField($tcaFieldDefinition);
                }
            }
        }

        return $tableDefinition;
    }

    public function toArray(): array
    {
        $definitionArray = [];
        if ($this->elements instanceof ElementDefinitionCollection && $this->elements->count() > 0) {
            $definitionArray['elements'] = $this->elements->toArray();
        }
        if ($this->sql instanceof SqlDefinition && $this->sql->count() > 0) {
            $definitionArray['sql'] = $this->sql->toArray();
        }
        if ($this->tca instanceof TcaDefinition && $this->tca->count() > 0) {
            $definitionArray['tca'] = $this->tca->toArray();
        }
        if ($this->palettes instanceof PaletteDefinitionCollection && $this->palettes->count() > 0) {
            $definitionArray['palettes'] = $this->palettes->toArray();
        }

        return $definitionArray;
    }
}
