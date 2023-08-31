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

namespace MASK\Mask\Utility;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Enumeration\FieldType;

/**
 * Helper Utility to handle reusing fields (restructuring and tca config cleanups)
 *
 * @internal
 */
class OverrideFieldsUtility
{
    public static function restructureTcaDefinitions(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        $table = 'tt_content';
        if (!$tableDefinitionCollection->hasTable($table)) {
            return $tableDefinitionCollection;
        }

        $ttContentDefinition = $tableDefinitionCollection->getTable($table);
        $tcaDefinition = $ttContentDefinition->tca;
        foreach ($ttContentDefinition->elements as $element) {
            if ($element->columns === []) {
                continue;
            }

            foreach ($element->columns as $fieldKey) {
                if ($element->hasColumnsOverride($fieldKey)) {
                    continue;
                }

                $fieldTypeTca = $tcaDefinition->getField($fieldKey);
                $fieldType = $fieldTypeTca->getFieldType($element->key);

                if ($fieldType->equals(FieldType::PALETTE)) {
                    $paletteDefinition = $ttContentDefinition->palettes->getPalette($fieldKey);
                    foreach ($paletteDefinition->showitem as $paletteFieldKey) {
                        $paletteTcaDefinition = $tcaDefinition->getField($paletteFieldKey);
                        $paletteFieldType = $paletteTcaDefinition->getFieldType($element->key);
                        if (!$paletteFieldType->canBeShared()) {
                            continue;
                        }
                        $element->addColumnsOverride($paletteFieldKey, $paletteTcaDefinition);
                    }
                    continue;
                }

                if (!$fieldType->canBeShared()) {
                    continue;
                }

                $element->addColumnsOverride($fieldKey, $fieldTypeTca);
            }
        }

        $tableDefinitionCollection->setRestructuringDone();
        return $tableDefinitionCollection;
    }
}
