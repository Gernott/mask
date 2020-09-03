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
     * Returns all elements that use this field
     *
     * @param string $key TCA Type
     * @param string $type elementtype
     * @return array elements in use
     */
    public function getElementsWhichUseField($key, $type = 'tt_content'): array
    {
        $storage = $this->storageRepository->load();

        $elementsInUse = [];
        if ($storage[$type]['elements']) {
            foreach ($storage[$type]['elements'] as $element) {
                if ($element['columns']) {
                    foreach ($element['columns'] as $column) {
                        if ($column === $key) {
                            $elementsInUse[] = $element;
                        }
                    }
                }
            }
        }
        return $elementsInUse;
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
        if ($columns && count($columns) > 0) {
            $fieldIndex = array_search($fieldKey, $columns);
            if ($fieldIndex !== false) {
                $label = $json[$type]['elements'][$elementKey]['labels'][$fieldIndex];
            }
        }
        return $label;
    }

    /**
     * Returns type of field (tt_content or pages)
     *
     * @param string $fieldKey key of field
     * @param string $elementKey key of element
     * @param bool $excludeInlineFields
     * @return string $fieldType returns fieldType or null if not found
     */
    public function getFieldType($fieldKey, $elementKey = '', $excludeInlineFields = false): string
    {
        $storage = $this->storageRepository->load();

        // get all possible types (tables)
        if ($storage && !$excludeInlineFields) {
            $types = array_keys($storage);
        } else {
            $types = [];
        }
        $types[] = 'pages';
        $types[] = 'tt_content';
        $types = array_unique($types);

        $fieldType = '';
        $found = false;
        foreach ($types as $type) {
            if ($storage[$type]['elements'] && !$found) {
                foreach ($storage[$type]['elements'] as $element) {

                    // if this is the element we search for, or no special element was given,
                    // and the element has columns and the fieldType wasn't found yet
                    if (($element['key'] === $elementKey || $elementKey === '') && $element['columns'] && !$found) {
                        foreach ($element['columns'] as $column) {
                            if ($column === $fieldKey && !$found) {
                                $fieldType = $type;
                                $found = true;
                            }
                        }
                    }
                }
            } else {
                if (is_array($storage[$type]['tca'][$fieldKey])) {
                    $fieldType = $type;
                    $found = true;
                }
            }
        }
        return $fieldType;
    }

    /**
     * Returns all fields of a type from a table
     *
     * @param string $key TCA Type
     * @param string $type elementtype
     * @return array fields
     */
    public function getFieldsByType($key, $type): array
    {
        $storage = $this->storageRepository->load();
        if (empty($storage[$type]) || empty($storage[$type]['tca'])) {
            return [];
        }

        $fields = [];
        foreach ($storage[$type]['tca'] as $field => $config) {
            if ($config['config']['type'] !== strtolower($key)) {
                continue;
            }

            $elements = $this->getElementsWhichUseField($field, $type);
            if (empty($elements)) {
                continue;
            }

            $fields[] = [
                'field' => $field,
                'label' => $this->getLabel($elements[0]['key'], $field, $type),
            ];
        }

        return $fields;
    }
}
