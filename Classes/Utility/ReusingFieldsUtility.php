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
class ReusingFieldsUtility
{
    private const NON_OVERRIDEABLE_OPTIONS = [
        'type',
        'relationship',
        'dbType',
        'nullable',
        'MM',
        'MM_opposite_field',
        'MM_hasUidField',
        'MM_oppositeUsage',
        'allowed',
        'foreign_table',
        'foreign_field',
        'foreign_table_field',
        'foreign_match_fields',
    ];

    private const NON_OVERRIDEABLE_FIELD_TYPES = [
        FieldType::INLINE,
        FieldType::PALETTE,
        FieldType::TAB,
        FieldType::LINEBREAK
    ];

    /**
     * @param TableDefinitionCollection $tableDefinitionCollection
     * @return TableDefinitionCollection
     */
    public static function restructureTcaDefinitions(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        if (!$tableDefinitionCollection->hasTable('tt_content')) {
            return $tableDefinitionCollection;
        }

        $ttContentDefinition = $tableDefinitionCollection->getTable('tt_content');
        $tcaDefinition = $ttContentDefinition->tca;
        $paletteDefinitions = $ttContentDefinition->palettes;
        foreach ($ttContentDefinition->elements as $element) {
            // ignore content elements with no fields
            if (count($element->columns) <= 0) {
                continue;
            }

            foreach ($element->columns as $fieldKey) {
                $fieldTypeTca = $tcaDefinition->getField($fieldKey);
                $fieldType = $fieldTypeTca->getFieldType();
                if (!self::fieldTypeIsAllowedToBeReused($fieldType) && !$fieldType->equals(FieldType::PALETTE) ) {
                    continue;
                }

                if ($fieldType->equals(FieldType::PALETTE) && isset($paletteDefinitions)) {
                    $paletteDefinition = $paletteDefinitions->getPalette($fieldKey);
                    foreach ($paletteDefinition->showitem as $childFieldKey) {
                        $fieldTypeTca = $tcaDefinition->getField($childFieldKey);
                        $fieldType = $fieldTypeTca->getFieldType();
                        if (!self::fieldTypeIsAllowedToBeReused($fieldType)) {
                            continue;
                        }

                        $columnsOverride = self::getOverrideTcaConfig($fieldTypeTca->toArray());
                        $element->addColumnsOverrideForField($childFieldKey, $columnsOverride);
                    }
                    continue;
                }

                $columnsOverride = self::getOverrideTcaConfig($fieldTypeTca->toArray());
                $element->addColumnsOverrideForField($fieldKey, $columnsOverride);
            }
        }

        foreach ($tcaDefinition->getKeys() as $fieldKey) {
            $fieldTypeTca = $tcaDefinition->getField($fieldKey);
            if (!self::fieldTypeIsAllowedToBeReused($fieldTypeTca->getFieldType())) {
                continue;
            }

            $minimalTca = self::getRealTcaConfig($fieldTypeTca->realTca);
            $fieldTypeTca->overrideTca($minimalTca);
        }

        $tableDefinitionCollection->setRestructuringDone(true);
        return $tableDefinitionCollection;
    }

    /**
     * @param array $fieldConfig complete field configuration that should be changed to minified field config
     * @return array minified field configuration
     */
    public static function getRealTcaConfig(array $fieldConfig): array
    {
        $minimalFieldTca = $fieldConfig;
        if (!is_array($minimalFieldTca['config'])) {
            return $minimalFieldTca;
        }


        foreach (array_keys($minimalFieldTca['config']) as $configKey) {
            if (!in_array($configKey, self::NON_OVERRIDEABLE_OPTIONS, true)
                && isset($minimalFieldTca['config'][$configKey])) {
                unset($minimalFieldTca['config'][$configKey]);
            }
        }

        // cleanup other options that are stored in override
        if (isset($minimalFieldTca['inPalette'])) {
            unset($minimalFieldTca['inPalette']);
        }

        return $minimalFieldTca;
    }

    /**
     * @param array $fieldConfig tca field configuration that should be cleaned up to be used as columnsOverride
     * @return array cleaned tca field config that can be used as columnOverride
     */
    public static function getOverrideTcaConfig(array $fieldConfig): array
    {
        if (!is_array($fieldConfig['config'])) {
            return $fieldConfig;
        }

        $overrideTcaConfig = $fieldConfig['config'];
        foreach (array_keys($overrideTcaConfig) as $configKey) {
            if (in_array($configKey, self::NON_OVERRIDEABLE_OPTIONS, true)
                && isset($overrideTcaConfig[$configKey])) {
                unset($overrideTcaConfig[$configKey]);
            }
        }

        $overrideTca = array(
            'config' => $overrideTcaConfig
        );

        if (isset($fieldConfig['inPalette'])) {
            $overrideTca['inPalette'] = $fieldConfig['inPalette'];
        }

        // TODO move label and description also to this override section and remove from parent

        return $overrideTca;
    }

    /**
     * @param FieldType $fieldType
     * @return bool
     */
    public static function fieldTypeIsAllowedToBeReused(FieldType $fieldType): bool
    {
        foreach (self::NON_OVERRIDEABLE_FIELD_TYPES as $FIELD_TYPE) {
            if ($fieldType->equals($FIELD_TYPE)) {
                return false;
            }
        }

        return true;
    }

}
