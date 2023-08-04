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

final class MaskAfterElementSavedEvent
{
    private string $elementKey;
    private bool $isNewElement;
    private TableDefinitionCollection $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, string $elementKey, bool $isNewElement)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->elementKey = $elementKey;
        $this->isNewElement = $isNewElement;
    }

    public function getTableDefinitionCollection(): TableDefinitionCollection
    {
        return $this->tableDefinitionCollection;
    }

    public function getElementKey(): string
    {
        return $this->elementKey;
    }

    public function isNewElement(): bool
    {
        return $this->isNewElement;
    }
}
