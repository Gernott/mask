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

final class ElementTcaDefinition
{
    /**
     * @var ElementDefinition
     */
    public $elementDefinition;

    /**
     * @var TcaDefinition
     */
    public $tcaDefinition;

    public function __construct(ElementDefinition $elementDefinition, TcaDefinition $tcaDefinition)
    {
        $this->elementDefinition = $elementDefinition;
        $this->tcaDefinition = $tcaDefinition;
    }

    public function getRootTcaFields(): TcaDefinition
    {
        $tcaDefinition = new TcaDefinition();
        foreach ($this->elementDefinition->columns as $column) {
            if ($this->tcaDefinition->hasField($column)) {
                $tcaDefinition->addField($this->tcaDefinition->getField($column));
            }
        }
        return $tcaDefinition;
    }

    public function toArray(): array
    {
        $array = $this->elementDefinition->toArray();
        $array['tca'] = [];
        foreach ($this->getRootTcaFields() as $field) {
            $array['tca'][$field->fullKey] = $field->toArray();
        }
        return $array;
    }
}
