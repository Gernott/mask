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

final class MaskAllowedFieldsEvent
{
    /**
     * @var array<string, list<string>>
     */
    private array $allowedFields;

    /**
     * @param array<string, list<string>> $allowedFields
     */
    public function __construct(array $allowedFields)
    {
        $this->allowedFields = $allowedFields;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }

    /**
     * @param array<string, list<string>> $allowedFields
     */
    public function setAllowedFields(array $allowedFields): void
    {
        $this->allowedFields = $allowedFields;
    }

    public function addField(string $fieldName, string $table = 'tt_content'): void
    {
        $this->allowedFields[$table][] = $fieldName;
    }

    public function removeField(string $fieldName, string $table = 'tt_content'): void
    {
        $position = array_search($fieldName, $this->allowedFields[$table]);
        array_splice($this->allowedFields[$table], $position, 1);
    }
}
