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

class RteMigration implements MigrationInterface
{
    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        // Compatibility layer for old rte resolving
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->elements as $element) {
                if ($element->options === []) {
                    continue;
                }
                foreach ($element->options as $index => $option) {
                    if ($option === 'rte') {
                        $fieldKey = $element->columns[$index] ?? '';

                        // Sometimes these options are orphans and weren't removed..
                        if ($fieldKey === '') {
                            continue;
                        }

                        $field = $tableDefinitionCollection->loadField($tableDefinition->table, $fieldKey);
                        if ($field instanceof TcaFieldDefinition) {
                            $field->setFieldType(new FieldType(FieldType::RICHTEXT));
                        }
                    }
                }
            }
        }

        return $tableDefinitionCollection;
    }

    public function forVersionBelow(): string
    {
        return '7.2.0';
    }
}
