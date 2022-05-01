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

final class PaletteDefinition
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
     * @var array
     */
    public $showitem = [];

    public function __construct(string $key, string $label, string $description, array $showitem)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('Palette key must not be empty', 1629293639);
        }

        $this->key = $key;
        $this->label = $label;
        $this->description = $description;
        $this->showitem = $showitem;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'description' => $this->description,
            'showitem' => $this->showitem,
        ];
    }
}
