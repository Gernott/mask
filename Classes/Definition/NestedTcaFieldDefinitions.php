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

final class NestedTcaFieldDefinitions implements \IteratorAggregate
{
    /**
     * @var array<TcaFieldDefinition>
     */
    private $nestedFields = [];

    /**
     * @var string
     */
    private $elementKey;

    public function __construct(string $elementKey = '')
    {
        $this->elementKey = $elementKey;
    }

    public function toArray(): array
    {
        $nestedFields = [];
        foreach ($this->sortInlineFieldsByOrder($this->nestedFields) as $field) {
            $nestedFields[] = $field->toArray(true);
        }
        return $nestedFields;
    }

    public function addField(TcaFieldDefinition $fieldDefinition): void
    {
        $this->nestedFields[] = $fieldDefinition;
    }

    /**
     * @return \Traversable|TcaFieldDefinition[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->sortInlineFieldsByOrder($this->nestedFields));
    }

    /**
     * Sort inline fields recursively.
     * @param array<TcaFieldDefinition> $nestedFields
     */
    private function sortInlineFieldsByOrder(array $nestedFields = []): array
    {
        usort(
            $nestedFields,
            function (TcaFieldDefinition $fieldA, TcaFieldDefinition $fieldB) {
                return $fieldA->getOrder($this->elementKey) <=> $fieldB->getOrder($this->elementKey);
            }
        );

        foreach ($nestedFields as $field) {
            if (!empty($field->inlineFields)) {
                $field->inlineFields = $this->sortInlineFieldsByOrder($field->inlineFields);
            }
        }

        return $nestedFields;
    }
}
