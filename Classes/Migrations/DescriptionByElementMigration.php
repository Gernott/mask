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

namespace MASK\Mask\Migrations;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;

class DescriptionByElementMigration implements MigrationInterface
{
    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        foreach ($tableDefinitionCollection as $tableDefinition) {
            // Fields on custom tables can't have descriptions by element.
            if (AffixUtility::hasMaskPrefix($tableDefinition->table)) {
                continue;
            }

            foreach ($tableDefinition->tca as $tcaFieldDefinition) {
                if ($tcaFieldDefinition->hasFieldType() && !$tcaFieldDefinition->getFieldType()->isRenderable()) {
                    continue;
                }

                // If a field exists, it must have at least one entry in descriptionByElement.
                // If this is not the case, the definition is from before Mask 7.1 and needs migration.
                if ($tcaFieldDefinition->descriptionByElement === []) {
                    $this->fillDescriptions($tableDefinitionCollection, $tcaFieldDefinition, $tableDefinition->table);
                }

                // Go through all palette fields on the root level.
                if ($tcaFieldDefinition->hasFieldType() && $tcaFieldDefinition->getFieldType()->equals(FieldType::PALETTE)) {
                    $paletteField = $tableDefinition->palettes->getPalette($tcaFieldDefinition->fullKey);
                    foreach ($paletteField->showitem as $item) {
                        $itemField = $tableDefinitionCollection->loadField($tableDefinition->table, $item);

                        if ($itemField instanceof TcaFieldDefinition) {
                            if ($itemField->hasFieldType() && !$itemField->getFieldType()->hasDescription()) {
                                continue;
                            }

                            if ($itemField->descriptionByElement === []) {
                                $this->fillDescriptions($tableDefinitionCollection, $itemField, $tableDefinition->table);
                            }
                        }
                    }
                }
            }
        }

        return $tableDefinitionCollection;
    }

    private function fillDescriptions(TableDefinitionCollection $tableDefinitionCollection, TcaFieldDefinition $tcaFieldDefinition, string $table): void
    {
        $elements = $tableDefinitionCollection->getElementsWhichUseField($tcaFieldDefinition->fullKey, $table);
        $descriptionToUse = '';
        if ($tcaFieldDefinition->description !== '') {
            $descriptionToUse = $tcaFieldDefinition->description;
            $tcaFieldDefinition->description = '';
            unset($tcaFieldDefinition->realTca['description']);
        }
        if ($tcaFieldDefinition->inPalette) {
            foreach ($elements as $element) {
                $tcaFieldDefinition->descriptionByElement[$element->key] = $descriptionToUse;
            }
        } else {
            foreach ($elements as $element) {
                $index = array_search($tcaFieldDefinition->fullKey, $element->columns, true);
                // Only, if the description is not already set.
                if (($element->descriptions[$index] ?? '') === '') {
                    $element->descriptions[$index] = $descriptionToUse;
                }
            }
        }

        // If palette, add it to the palette definition as well.
        if ($tcaFieldDefinition->hasFieldType() && $tcaFieldDefinition->getFieldType()->equals(FieldType::PALETTE)) {
            $paletteDefinition = $tableDefinitionCollection->getTable($table)->palettes->getPalette($tcaFieldDefinition->fullKey);
            if ($paletteDefinition->description === '') {
                $paletteDefinition->description = $descriptionToUse;
            }
        }
    }

    public function forVersionBelow(): string
    {
        return '7.2.0';
    }
}
