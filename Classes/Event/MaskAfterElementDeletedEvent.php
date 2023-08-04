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

namespace MASK\Mask\Event;

use MASK\Mask\Definition\TableDefinitionCollection;

final class MaskAfterElementDeletedEvent
{
    private string $elementKey;
    private TableDefinitionCollection $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, string $elementKey)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->elementKey = $elementKey;
    }

    public function getTableDefinitionCollection(): TableDefinitionCollection
    {
        return $this->tableDefinitionCollection;
    }

    public function getElementKey(): string
    {
        return $this->elementKey;
    }
}
