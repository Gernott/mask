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

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\FieldTypeUtility;

final class TcaFieldDefinition
{
    public $key = '';
    public $fullKey = '';
    /** @var FieldType */
    public $type;
    public $isCoreField = false;
    public $inPalette = false;
    public $inlineParent = '';
    public $inlineParentByElement = [];
    public $label = '';
    public $labelByElement = [];
    public $description = '';
    public $descriptionByElement = [];
    public $order = 0;
    public $orderByElement = [];
    public $cTypes = [];
    public $imageoverlayPalette = false;
    public $allowedFileExtensions = '';
    public $inlineIcon = '';
    public $inlineLabel = '';
    public $realTca = [];
    /**
     * Array of direct child tca field definitions.
     * Not filled by default.
     *
     * @var array<TcaFieldDefinition>
     */
    public $inlineFields = [];

    public static function createFromFieldArray(array $definition): TcaFieldDefinition
    {
        $key = ($definition['key'] ?? '');
        if ($key === '') {
            throw new \InvalidArgumentException('The key for a FieldDefinition must not be empty', 1629277138);
        }

        $tcaFieldDefinition = new self();

        // Prior to v6, core fields were determined by empty config array.
        $tcaFieldDefinition->isCoreField = isset($definition['coreField']) || !isset($definition['config']);

        $tcaFieldDefinition->key = $key;
        // Set full key with mask prefix if not core field
        $tcaFieldDefinition->fullKey = $definition['fullKey'] ?? '';
        if ($tcaFieldDefinition->fullKey === '') {
            if ($tcaFieldDefinition->isCoreField) {
                $tcaFieldDefinition->fullKey = $tcaFieldDefinition->key;
            } else {
                $tcaFieldDefinition->fullKey = AffixUtility::addMaskPrefix($tcaFieldDefinition->key);
            }
        }

        $tcaFieldDefinition->inPalette = (bool)($definition['inPalette'] ?? false);
        $tcaFieldDefinition->cTypes = $definition['cTypes'] ?? [];

        // "inlineIcon" and "inlineLabel" renamed to ctrl.iconfile and ctrl.label in Mask v7.0.
        $tcaFieldDefinition->inlineIcon = $definition['ctrl']['iconfile'] ?? $definition['inlineIcon'] ?? '';
        $tcaFieldDefinition->inlineLabel = $definition['ctrl']['label'] ?? $definition['inlineLabel'] ?? '';

        // Since mask v7.0.0 the path for allowedFileExtensions has changed to root level. Keep this as fallback.
        $tcaFieldDefinition->allowedFileExtensions = $definition['allowedFileExtensions'] ?? $definition['config']['filter']['0']['parameters']['allowedFileExtensions'] ?? '';
        unset($definition['config']['filter']['0']['parameters']['allowedFileExtensions']);

        // Migration for Link "allowedExtensions"
        if (isset($definition['config']['wizards']['link']['params']['allowedExtensions'])) {
            $definition['config']['fieldControl']['linkPopup']['options']['allowedExtensions'] = $definition['config']['wizards']['link']['params']['allowedExtensions'];
            unset($definition['config']['wizards']);
        }

        // Now config is clean. Extract real TCA.
        $tcaFieldDefinition->realTca = self::extractRealTca($definition);

        // Type resolving.
        // "options" was used for identifying file fields prior to v6.
        // "name" was renamed to "type" in mask 7.1.
        $fieldType = $definition['type'] ?? $definition['name'] ?? $definition['options'] ?? null;
        if ($fieldType !== null) {
            $tcaFieldDefinition->type = FieldType::cast($fieldType);
        }
        // "rte" was used to identify RTE fields prior to v6.
        if (!$tcaFieldDefinition->type && !empty($definition['rte'])) {
            $tcaFieldDefinition->type = FieldType::cast(FieldType::RICHTEXT);
        }
        // If the field is not a core field and the field type couldn't be resolved by now, resolve type by tca config.
        if (!$tcaFieldDefinition->type && !$tcaFieldDefinition->isCoreField) {
            $tcaFieldDefinition->type = FieldType::cast(FieldTypeUtility::getFieldType($tcaFieldDefinition->toArray(), $tcaFieldDefinition->fullKey));
        }

        // If imageoverlayPalette is not set (because of updates to newer version), fallback to default behaviour.
        if (isset($definition['imageoverlayPalette'])) {
            $tcaFieldDefinition->imageoverlayPalette = (bool)$definition['imageoverlayPalette'];
        } elseif ($tcaFieldDefinition->type && $tcaFieldDefinition->type->equals(FieldType::FILE)) {
            $tcaFieldDefinition->imageoverlayPalette = true;
        }

        if (isset($definition['inlineParent'])) {
            if (is_array($definition['inlineParent'])) {
                $tcaFieldDefinition->inlineParentByElement = $definition['inlineParent'];
            } else {
                $tcaFieldDefinition->inlineParent = $definition['inlineParent'];
            }
        }

        if (isset($definition['label'])) {
            if (is_array($definition['label'])) {
                $tcaFieldDefinition->labelByElement = $definition['label'];
            } else {
                $tcaFieldDefinition->label = $definition['label'];
            }
        }

        if (isset($definition['description'])) {
            if (is_array($definition['description'])) {
                $tcaFieldDefinition->descriptionByElement = $definition['description'];
            } else {
                $tcaFieldDefinition->description = $definition['description'];
            }
        }

        if (isset($definition['order'])) {
            if (is_array($definition['order'])) {
                foreach ($definition['order'] as $orderKey => $order) {
                    $tcaFieldDefinition->orderByElement[$orderKey] = (int)$order;
                }
            } else {
                $tcaFieldDefinition->order = (int)$definition['order'];
            }
        }

        return $tcaFieldDefinition;
    }

    private static function extractRealTca(array $definition): array
    {
        // Unset some values that are not needed in TCA
        unset(
            $definition['options'],
            $definition['key'],
            $definition['fullKey'],
            $definition['rte'],
            $definition['inlineParent'],
            $definition['inPalette'],
            $definition['order'],
            $definition['inlineIcon'],
            $definition['inlineLabel'],
            $definition['imageoverlayPalette'],
            $definition['cTypes'],
            $definition['allowedFileExtensions'],
            $definition['ctrl']
        );

        // Unset label if it is from palette fields
        if (is_array($definition['label'] ?? false)) {
            unset($definition['label']);
        }
        // Unset label if it is from palette fields
        if (is_array($definition['description'] ?? false)) {
            unset($definition['description']);
        }

        $definition = self::removeBlankOptions($definition);

        return $definition;
    }

    public function hasInlineParent($elementKey = ''): bool
    {
        if ($this->inlineParent !== '') {
            return true;
        }

        if ($elementKey === '') {
            return $this->inlineParentByElement !== [];
        }

        return isset($this->inlineParentByElement[$elementKey]);
    }

    public function getInlineParent(string $elementKey = ''): string
    {
        // if inlineParent is an array, it's in a palette on default table
        if (!empty($this->inlineParentByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->inlineParentByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->inlineParentByElement[$elementKey];
        }

        return $this->inlineParent;
    }

    public function hasOrder(): bool
    {
        return $this->order !== 0 || $this->orderByElement !== [];
    }

    public function getOrder(string $elementKey = ''): int
    {
        if (!empty($this->orderByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->orderByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->orderByElement[$elementKey];
        }

        return $this->order;
    }

    public function getLabel(string $elementKey = ''): string
    {
        if (!empty($this->labelByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->labelByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->labelByElement[$elementKey];
        }

        return $this->label;
    }

    public function getDescription(string $elementKey = ''): string
    {
        if (!empty($this->descriptionByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->descriptionByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->descriptionByElement[$elementKey];
        }

        return $this->description;
    }

    public function toArray(bool $withBackwardsCompatibility = false): array
    {
        $field = $this->realTca;
        $field += [
            'key' => $this->key,
            'fullKey' => $this->fullKey,
            'type' => $this->type ? (string)$this->type : null,
            'coreField' => $this->isCoreField ? 1 : null,
            'inPalette' => $this->inPalette ? 1 : null,
            'cTypes' => !empty($this->cTypes) ? $this->cTypes : null,
            'allowedFileExtensions' => $this->allowedFileExtensions !== '' ? $this->allowedFileExtensions : null,
        ];

        // Backwards compatibility for loadInlineFields
        if ($withBackwardsCompatibility) {
            $field['maskKey'] = $this->fullKey;
        }

        if ($this->type && $this->type->equals(FieldType::FILE)) {
            $field['imageoverlayPalette'] = $this->imageoverlayPalette ? 1 : 0;
        }

        if ($this->inlineIcon !== '') {
            $field['ctrl']['iconfile'] = $this->inlineIcon;
        }

        if ($this->inlineLabel !== '') {
            $field['ctrl']['label'] = $this->inlineLabel;
        }

        if (!empty($this->inlineParentByElement)) {
            $field['inlineParent'] = $this->inlineParentByElement;
        } elseif ($this->inlineParent !== '') {
            $field['inlineParent'] = $this->inlineParent;
        }

        if (!empty($this->labelByElement)) {
           $field['label'] = $this->labelByElement;
        } elseif ($this->label !== '') {
            $field['label'] = $this->label;
        }

        if (!empty($this->descriptionByElement)) {
            $field['description'] = $this->descriptionByElement;
        } elseif ($this->description !== '') {
            $field['description'] = $this->description;
        }

        if (!empty($this->orderByElement)) {
            $field['order'] = $this->orderByElement;
        } elseif ($this->order > 0) {
            $field['order'] = $this->order;
        }

        $field = array_filter($field, static function ($item) {
            return $item !== null;
        });

        if (!empty($this->inlineFields)) {
            foreach ($this->inlineFields as $inlineField) {
                $field['inlineFields'][] = $inlineField->toArray(true);
            }
        }

        return $field;
    }

    public function addInlineField(TcaFieldDefinition $definition): void
    {
        $this->inlineFields[] = $definition;
    }

    /**
     * Removes all the blank options from the tca
     */
    protected static function removeBlankOptions(array $haystack): array
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = self::removeBlankOptions($value);
            }
            if ((is_array($haystack[$key]) && empty($haystack[$key])) || (is_string($haystack[$key]) && $haystack[$key] === '')) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }
}
