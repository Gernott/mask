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

final class ElementDefinition
{
    public string $key = '';
    public string $label = '';
    public string $description = '';
    public string $shortLabel = '';
    public string $color = '';
    public string $colorOverlay = '';
    public string $icon = '';
    public string $iconOverlay = '';
    public array $columns = [];
    /** @var array<string, TcaFieldDefinition> */
    public array $columnsOverride = [];
    public array $labels = [];
    public array $descriptions = [];
    /**
     * Options were used prior to v6 to identify fields like "rte"
     */
    public array $options = [];
    public bool $hidden = false;
    public int $sorting = 0;
    public bool $saveAndClose = false;

    public static function createFromArray(array $elementArray, string $table): ElementDefinition
    {
        $elementDefinition = new self();

        if (!isset($elementArray['key']) || $elementArray['key'] === '') {
            throw new \InvalidArgumentException('Element key must not be empty', 1629292395);
        }

        if ($table === 'tt_content' && (!isset($elementArray['label']) || $elementArray['label'] === '')) {
            throw new \InvalidArgumentException('Element label must not be empty', 1629292453);
        }

        $elementDefinition->key = (string)$elementArray['key'];
        $elementDefinition->label = $elementArray['label'] ?? '';
        $elementDefinition->description = $elementArray['description'] ?? '';
        $elementDefinition->shortLabel = $elementArray['shortLabel'] ?? '';
        $elementDefinition->color = $elementArray['color'] ?? '';
        $elementDefinition->colorOverlay = $elementArray['colorOverlay'] ?? '';
        $elementDefinition->icon = $elementArray['icon'] ?? '';
        $elementDefinition->iconOverlay = $elementArray['iconOverlay'] ?? '';
        $elementDefinition->columns = $elementArray['columns'] ?? [];
        $elementDefinition->labels = $elementArray['labels'] ?? [];
        $elementDefinition->descriptions = $elementArray['descriptions'] ?? [];
        $elementDefinition->options = $elementArray['options'] ?? [];
        $elementDefinition->hidden = !empty($elementArray['hidden']);
        $elementDefinition->sorting = (int)($elementArray['sorting'] ?? 0);
        $elementDefinition->saveAndClose = !empty($elementArray['saveAndClose']);

        foreach ($elementArray['columnsOverride'] ?? [] as $key => $columnOverride) {
            $elementDefinition->addColumnsOverride(
                $key,
                TcaFieldDefinition::createFromFieldArray($columnOverride)
            );
        }

        return $elementDefinition;
    }

    public function toArray(): array
    {
        $element = [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'shortLabel' => $this->shortLabel,
            'color' => $this->color,
            'colorOverlay' => $this->colorOverlay,
            'icon' => $this->icon,
            'iconOverlay' => $this->iconOverlay,
            'columns' => $this->columns,
            'labels' => $this->labels,
            'descriptions' => $this->descriptions,
            'sorting' => $this->sorting,
        ];

        if ($this->hidden) {
            $element['hidden'] = 1;
        }

        if ($this->saveAndClose) {
            $element['saveAndClose'] = 1;
        }

        if (!empty($this->options)) {
            $element['options'] = $this->options;
        }

        foreach ($this->columnsOverride as $key => $column) {
            $element['columnsOverride'][$key] = $column->getOverridesDefinition();
        }

        return $element;
    }

    public function hasColumnsOverride(string $key): bool
    {
        return isset($this->columnsOverride[$key]);
    }

    public function getColumnsOverride(string $key): TcaFieldDefinition
    {
        if (!$this->hasColumnsOverride($key)) {
            throw new \OutOfBoundsException(
                'The element "' . $this->key . '" does not have an override field for the key "' . $key . '".',
                1685697116
            );
        }
        return $this->columnsOverride[$key];
    }

    public function addColumnsOverride(string $key, TcaFieldDefinition $columnsOverride): void
    {
        $this->columnsOverride[$key] = $columnsOverride;
    }
}
