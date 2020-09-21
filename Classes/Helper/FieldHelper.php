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
        $this->storageRepository = $storageRepository;
    }

    /**
     * Returns the label of a field in an element
     *
     * @param string $elementKey Key of Element
     * @param string $fieldKey Key if Field
     * @param string $type elementtype
     * @return string Label
     */
    public function getLabel($elementKey, $fieldKey, $type = 'tt_content'): string
    {
        $json = $this->storageRepository->load();
        $label = '';
        $columns = $json[$type]['elements'][$elementKey]['columns'] ?? false;
        $maskField = isset($fieldKey) && strpos($fieldKey, 'tx_mask_') === 0;
        if ($maskField && $columns && count($columns) > 0) {
            $fieldIndex = array_search($fieldKey, $columns);
            if ($fieldIndex !== false) {
                $label = $json[$type]['elements'][$elementKey]['labels'][$fieldIndex];
            }
        } else {
            $label = $json[$type]['tca'][$fieldKey]['label'][$elementKey] ?? '';
        }
        return $label;
    }

    /**
     * Returns type of field (tt_content or pages)
     *
     * @param string $fieldKey key of field
     * @param string $elementKey key of element
     * @param bool $excludeInlineFields
     * @return string $fieldType returns fieldType or empty string if not found
     */
    public function getFieldType($fieldKey, $elementKey = '', $excludeInlineFields = false): string
    {
        $storage = $this->storageRepository->load();

        if (!$storage) {
            return '';
        }

        // get all possible types (tables)
        if ($excludeInlineFields) {
            $types = ['pages', 'tt_content'];
        } else {
            $types = array_keys($storage);
        }

        foreach ($types as $type) {
            foreach ($storage[$type]['elements'] ?? [] as $element) {
                // if this is the element we search for, or no special element was given,
                // and the element has columns and the fieldType wasn't found yet
                if (($element['key'] === $elementKey || $elementKey === '') && ($element['columns'] ?? false)) {
                    foreach ($element['columns'] as $column) {
                        if ($column === $fieldKey) {
                            return $type;
                        }
                    }
                }
            }
            if (($storage[$type]['tca'][$fieldKey] ?? false) && is_array($storage[$type]['tca'][$fieldKey])) {
                return $type;
            }
        }

        return '';
    }

    /**
     * Returns all fields of a type from a table
     *
     * @param string $tcaType TCA Type
     * @param string $table elementtype
     * @return array fields
     */
    public function getFieldsByType($tcaType, $table): array
    {
        $storage = $this->storageRepository->load();
        $tcaType = strtolower($tcaType);

        if (empty($storage[$table]) || empty($storage[$table]['tca'])) {
            return [];
        }

        $fields = [];
        foreach ($storage[$table]['tca'] as $field => $config) {
            if ($config['config']['type'] !== $tcaType) {
                continue;
            }

            $elements = $this->storageRepository->getElementsWhichUseField($field, $table);
            if ($elements) {
                $fields[] = [
                    'field' => $field,
                    'label' => $this->getLabel($elements[0]['key'], $field, $table),
                ];
            }
        }

        return $fields;
    }

    /**
     * @return StorageRepository
     */
    public function getStorageRepository()
    {
        return $this->storageRepository;
    }
}
