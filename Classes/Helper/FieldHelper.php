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

namespace MASK\Mask\Helper;

use MASK\Mask\Domain\Repository\StorageRepository;

/**
 * Methods for types of fields in mask (string, rte, repeating, ...)
 * @deprecated will be removed in Mask 8.0.
 */
class FieldHelper
{
    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @param StorageRepository $storageRepository
     */
    public function __construct(StorageRepository $storageRepository)
    {
        trigger_error(
            'MASK\Mask\Helper\FieldHelper will be removed in Mask v8.0. Use MASK\Mask\Definition\TableDefinitionCollection instead.',
            E_USER_DEPRECATED
        );

        $this->storageRepository = $storageRepository;
    }

    /**
     * Returns the label of a field in an element
     */
    public function getLabel(string $elementKey, string $fieldKey, string $type = 'tt_content'): string
    {
        return $this->storageRepository->getLabel($elementKey, $fieldKey, $type);
    }

    /**
     * Returns type of field (tt_content or pages)
     */
    public function getFieldType(string $fieldKey, string $elementKey = '', bool $excludeInlineFields = false): string
    {
        return $this->storageRepository->getFieldType($fieldKey, $elementKey, $excludeInlineFields);
    }
}
