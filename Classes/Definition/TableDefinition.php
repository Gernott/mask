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
    public $table = '';
    /**
     * @var ElementDefinitionCollection
     */
    public $elements;
    /**
     * @var SqlDefinition
     */
    public $sql;
    /**
     * @var TcaDefinition
     */
    public $tca;
    /**
     * @var PaletteDefinitionCollection
     */
    public $palettes;

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

        return $tableDefinition;
    }

    public function toArray(): array
    {
        $definitionArray = [];
        if ($this->elements) {
            $definitionArray['elements'] = $this->elements->toArray();
        }
        if ($this->sql) {
            $definitionArray['sql'] = $this->sql->toArray();
        }
        if ($this->tca) {
            $definitionArray['tca'] = $this->tca->toArray();
        }
        if ($this->palettes) {
            $definitionArray['palettes'] = $this->palettes->toArray();
        }

        return $definitionArray;
    }
}
