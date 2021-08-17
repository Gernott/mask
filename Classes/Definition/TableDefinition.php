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
    public $elements = [];
    public $sql = [];
    public $tca = [];
    public $palettes = [];

    public function __construct(string $table, array $tca = [], array $sql = [], array $elements = [], array $palettes = [])
    {
        if ($table === '') {
            throw new \InvalidArgumentException('The name of the table must not be empty.', 1628672227);
        }
        $this->table = $table;
        $this->elements = $elements;
        $this->sql = $sql;
        $this->tca = $tca;
        $this->palettes = $palettes;
    }

    public function toArray(): array
    {
        $definitionArray = [];
        if ($this->elements) {
            $definitionArray['elements'] = $this->elements;
        }
        if ($this->sql) {
            $definitionArray['sql'] = $this->sql;
        }
        if ($this->tca) {
            $definitionArray['tca'] = $this->tca;
        }
        if ($this->palettes) {
            $definitionArray['palettes'] = $this->palettes;
        }

        return $definitionArray;
    }

    public function getTcaFieldKeys(): array
    {
        return array_keys($this->tca);
    }
}
