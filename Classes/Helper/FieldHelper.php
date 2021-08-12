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
use MASK\Mask\Utility\AffixUtility;

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
     */
    public function getLabel(string $elementKey, string $fieldKey, string $type = 'tt_content'): string
    {
        $json = $this->storageRepository->load();

        // If this field is in a repeating field or palette, the label is in the field configuration.
        $field = $json[$type]['tca'][$fieldKey] ?? [];
        if (isset($field['inlineParent'])) {
            if (is_array($field['label'])) {
                if (isset($field['label'][$elementKey])) {
                    return $field['label'][$elementKey];
                }
            } else {
                return $field['label'];
            }
        }

        // Root level fields have their labels defined in element labels array.
        $columns = $json[$type]['elements'][$elementKey]['columns'] ?? false;
        if ($columns && count($columns) > 0) {
            $fieldIndex = array_search($fieldKey, $columns, true);
            if ($fieldIndex !== false) {
                return $json[$type]['elements'][$elementKey]['labels'][$fieldIndex];
            }
        }

        return '';
    }

    /**
     * Returns type of field (tt_content or pages)
     */
    public function getFieldType(string $fieldKey, string $elementKey = '', bool $excludeInlineFields = false): string
    {
        $storage = $this->storageRepository->load();

        if (!$storage) {
            return '';
        }

        // get all possible types (tables)
        if ($excludeInlineFields) {
            $tables = ['tt_content', 'pages'];
        } else {
            $tables = array_keys($storage);
        }

        foreach ($tables as $table) {
            if (isset($storage[$table]['tca'][$fieldKey]) && AffixUtility::hasMaskPrefix($table)) {
                return $table;
            }
            foreach ($storage[$table]['elements'] ?? [] as $element) {
                // If element key is set, ignore all other elements
                if ($elementKey !== '' && ($elementKey !== $element['key'])) {
                    continue;
                }
                if (isset($storage[$table]['tca'][$fieldKey]) || in_array($fieldKey, ($element['columns'] ?? []), true)) {
                    return $table;
                }
            }
        }

        return '';
    }

    /**
     * Returns all fields of a type from a table
     */
    public function getFieldsByType(string $tcaType, string $table): array
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
}
