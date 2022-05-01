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

final class SqlColumnDefinition
{
    /**
     * @var string
     */
    public $column = '';

    /**
     * @var string
     */
    public $sqlDefinition = '';

    public function __construct(string $column, string $sqlDefinition)
    {
        if ($column === '') {
            throw new \InvalidArgumentException('Column name must not be empty.', 1629291834);
        }

        if ($sqlDefinition === '') {
            throw new \InvalidArgumentException('Sql definition must not be empty', 1629291856);
        }

        $this->column = $column;
        $this->sqlDefinition = $sqlDefinition;
    }

    public function toArray(): array
    {
        return [
            $this->column => $this->sqlDefinition,
        ];
    }

    public function setNull(): void
    {
        $this->sqlDefinition = trim(str_replace('NOT NULL', '', $this->sqlDefinition));
    }
}
