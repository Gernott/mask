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
        FieldType::LINEBREAK,
    ];

    /**
     * @param TableDefinitionCollection $tableDefinitionCollection
     * @return TableDefinitionCollection
     */
    public static function restructureTcaDefinitions(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        $table = 'tt_content';
        if (!$tableDefinitionCollection->hasTable($table)) {
            return $tableDefinitionCollection;
        }

        $ttContentDefinition = $tableDefinitionCollection->getTable($table);
        $tcaDefinition = $ttContentDefinition->tca;
        $paletteDefinitions = $ttContentDefinition->palettes;
        foreach ($ttContentDefinition->elements as $element) {
            // ignore content elements with no fields
            if (count($element->columns) <= 0) {
                continue;
            }

            foreach ($element->columns as $fieldKey) {
                $fieldTypeTca = $tcaDefinition->getField($fieldKey);
                if ($fieldTypeTca->isCoreField) {
                    continue;
                }
                $fieldType = $fieldTypeTca->getFieldType();
                if (!self::fieldTypeIsAllowedToBeReused($fieldType) && !$fieldType->equals(FieldType::PALETTE)) {
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

                        $columnsOverride = self::getOverrideTcaConfig($fieldTypeTca->toArray(), $table);
                        $element->addColumnsOverrideForField($childFieldKey, $columnsOverride);
                    }
                    continue;
                }

                $columnsOverride = self::getOverrideTcaConfig($fieldTypeTca->toArray(), $table);
                if (!empty($columnsOverride)) {
                    $element->addColumnsOverrideForField($fieldKey, $columnsOverride);
                }
            }
        }

        foreach ($tcaDefinition->getKeys() as $fieldKey) {
            $fieldTypeTca = $tcaDefinition->getField($fieldKey);
            if (!self::fieldTypeIsAllowedToBeReused($fieldTypeTca->getFieldType())) {
                continue;
            }

            $minimalTca = self::getRealTcaConfig($fieldTypeTca->realTca, $table);
            $fieldTypeTca->overrideTca($minimalTca);
        }

        $tableDefinitionCollection->setRestructuringDone(true);
        return $tableDefinitionCollection;
    }

    /**
     * @param array $fieldConfig complete field configuration that should be changed to minified field config
     * @param string $table the table the field is used in (we only support tt_content, no inline tables
     * @return array minified field configuration
     */
    public static function getRealTcaConfig(array $fieldConfig, string $table): array
    {
        $minimalFieldTca = $fieldConfig;
        if (!isset($minimalFieldTca['config']) || !is_array($minimalFieldTca['config'])
            || (isset($fieldConfig['coreField']) && $fieldConfig['coreField'] === 1)
            || $table !== 'tt_content') {
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

        if (isset($minimalFieldTca['allowedFileExtensions'])) {
            unset($minimalFieldTca['allowedFileExtensions']);
        }

        if (isset($minimalFieldTca['onlineMedia'])) {
            unset($minimalFieldTca['onlineMedia']);
        }

        // if no config is left (eg. media or file field we have to create at least an array with a single value)
        if (empty($minimalFieldTca['config'])) {
            $minimalFieldTca['config'] = [
                'maskReusingField' => 'true',
            ];
        }

        return $minimalFieldTca;
    }

    /**
     * @param array $fieldConfig tca field configuration that should be cleaned up to be used as columnsOverride
     * @param string $table the table the field is used in (we only support tt_content, no inline tables
     * @return array cleaned tca field config that can be used as columnOverride
     */
    public static function getOverrideTcaConfig(array $fieldConfig, string $table): array
    {
        if (!isset($fieldConfig['config']) || !is_array($fieldConfig['config'])) {
            return $fieldConfig;
        }

        if (isset($fieldConfig['coreField']) && $fieldConfig['coreField'] === 1) {
            return [];
        }

        if ($table !== 'tt_content') {
            return [];
        }

        $overrideTcaConfig = $fieldConfig['config'];
        foreach (array_keys($overrideTcaConfig) as $configKey) {
            if (in_array($configKey, self::NON_OVERRIDEABLE_OPTIONS, true)
                && isset($overrideTcaConfig[$configKey])) {
                unset($overrideTcaConfig[$configKey]);
            }
        }

        $overrideTca = [];
        if (!empty($overrideTcaConfig)) {
            $overrideTca = [
                'config' => $overrideTcaConfig,
            ];
        }

        if (isset($fieldConfig['inPalette'])) {
            $overrideTca['inPalette'] = $fieldConfig['inPalette'];
        }

        if (isset($fieldConfig['allowedFileExtensions'])) {
            $overrideTca['allowedFileExtensions'] = $fieldConfig['allowedFileExtensions'];
        }

        if (isset($fieldConfig['onlineMedia'])) {
            $overrideTca['onlineMedia'] = $fieldConfig['onlineMedia'];
        }

        // TODO move label and description also to this override section and remove from parent

        return $overrideTca;
    }

    /**
     * @param FieldType $fieldType
     * @param bool $isMaskField
     * @return bool
     */
    public static function fieldTypeIsAllowedToBeReused(FieldType $fieldType, bool $isMaskField = true): bool
    {
        if (!$isMaskField) {
            return false;
        }

        foreach (self::NON_OVERRIDEABLE_FIELD_TYPES as $FIELD_TYPE) {
            if ($fieldType->equals($FIELD_TYPE)) {
                return false;
            }
        }

        return true;
    }
}
