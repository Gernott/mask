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
    /**
     * @var string
     */
    public $key = '';

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $shortLabel = '';

    /**
     * @var string
     */
    public $color = '';

    /**
     * @var string
     */
    public $colorOverlay = '';

    /**
     * @var string
     */
    public $icon = '';

    /**
     * @var string
     */
    public $iconOverlay = '';

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var array
     */
    public $labels = [];

    /**
     * @var array
     */
    public $descriptions = [];

    /**
     * Options were used prior to v6 to identify fields like "rte"
     * @var array
     */
    public $options = [];

    /**
     * @var bool
     */
    public $hidden = false;

    /**
     * @var int
     */
    public $sorting = 0;

    /**
     * @var bool
     */
    public $saveAndClose = false;

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

        return $element;
    }
}
