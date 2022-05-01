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

use Symfony\Component\PropertyAccess\PropertyAccess;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreUtility;

/**
 * Back and forth converting for flat vs array TCA structure.
 *
 * @internal
 */
class TcaConverter
{
    /**
     * @var string[]
     */
    protected static $commaSeparatedLists = [
        'config.fieldControl.linkPopup.options.blindLinkOptions',
    ];

    /**
     * @var string[]
     */
    protected static $itemListFields = [
        'config.items',
        'config.valuePicker.items',
    ];

    /**
     * @var string[]
     */
    protected static $keyValueFields = [
        'config.itemGroups',
        'config.sortItems',
        'config.generatorOptions.replacements',
    ];

    /**
     * Converts the content of TCA config to a flat array, where each nesting is seperated with a period.
     *
     * Inside config:
     * [
     *     'type' => 'input',
     *     'renderType' => 'inputLink'
     * ]
     *
     * Result:
     *
     * [
     *     'config.type' => 'input',
     *     'config.renderType' => 'inputLink'
     * ]
     */
    public static function convertTcaArrayToFlat(array $config, array $path = []): array
    {
        $tca = [];
        foreach ($config as $key => $value) {
            $path[] = $key;
            $fullPath = implode('.', $path);
            if ($fullPath === 'config.generatorOptions.fields') {
                $fields = [];
                foreach ($value as $field) {
                    if (is_array($field)) {
                        $field = implode('|', $field);
                    }
                    $fields[] = $field;
                }
                $tca[] = [$fullPath => implode(',', $fields)];
            } elseif (in_array($fullPath, self::$commaSeparatedLists, true)) {
                $tca[] = [$fullPath => CoreUtility::trimExplode(',', $value)];
            } elseif (in_array($fullPath, self::$keyValueFields, true)) {
                $tca[] = [$fullPath => self::convertAssociativeArrayToKeyValuePairs($value)];
            } elseif (is_array($value) && !in_array($fullPath, self::$itemListFields, true)) {
                $tca[] = self::convertTcaArrayToFlat($value, $path);
            } elseif ($fullPath === 'config.eval') {
                if ($value !== '') {
                    // The eval value of type slug is set in "config.eval.slug".
                    if (($config['type'] ?? '') === 'slug') {
                        $tca[] = ['config.eval.slug' => $value];
                        // No further checks needed.
                        array_pop($path);
                        continue;
                    }

                    $keys = explode(',', $value);

                    // Special handling for timestamp field, as the dateType is in the key "config.eval"
                    $dateTypesInKeys = array_values(array_intersect($keys, ['date', 'datetime', 'time', 'timesec']));
                    if (count($dateTypesInKeys) > 0) {
                        $tca[] = ['config.eval' => $dateTypesInKeys[0]];
                        // Remove dateType from normal eval array
                        $keys = array_filter($keys, static function ($a) use ($dateTypesInKeys) {
                            return $a !== $dateTypesInKeys[0];
                        });
                    }

                    // For each eval value create an entry with value set to 1
                    $evalArray = array_combine($keys, array_fill(0, count($keys), 1));
                    $tca[] = self::convertTcaArrayToFlat($evalArray, $path);
                }
            } else {
                $tca[] = [$fullPath => $value];
            }
            array_pop($path);
        }

        return array_merge([], ...$tca);
    }

    /**
     * Does the opposite of convertTcaArrayToFlat.
     * Converts flat TCA options to normal TCA array, which can be directly used by TYPO3.
     */
    public static function convertFlatTcaToArray(array $tca): array
    {
        $tcaArray = [];
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($tca as $key => $value) {
            if (in_array($key, self::$itemListFields, true)) {
                foreach ($value as $index => $properties) {
                    $value[$index] = array_map('trim', $properties);
                }
            }

            if (in_array($key, self::$commaSeparatedLists, true)) {
                $value = implode(',', $value);
            }

            if ($key === 'config.generatorOptions.fields') {
                $fields = [];
                foreach (explode(',', $value) as $field) {
                    if (strpos($field, '|') !== false) {
                        $field = explode('|', $field);
                    }
                    $fields[] = $field;
                }
                $value = $fields;
            }
            // This is for timestamps as it has a fake tca property for eval date, datetime, ...
            if ($key === 'config.eval' && in_array($value, ['date', 'datetime', 'time', 'timesec'])) {
                $key = 'config.eval.' . $value;
                $value = 1;
            }
            // This is for slug as it has a fake tca property for eval unique, uniqueInSite, ...
            if ($key === 'config.eval.slug') {
                $key = 'config.eval.' . $value;
                $value = 1;
            }
            // This is for key-value pair fields.
            if (in_array($key, self::$keyValueFields, true)) {
                $value = self::convertKeyValuePairsToAssociativeArray($value);
            }
            $explodedKey = explode('.', $key);
            $propertyPath = array_reduce($explodedKey, static function ($carry, $property) {
                return $carry . "[$property]";
            });
            $accessor->setValue($tcaArray, $propertyPath, $value);
        }

        if (isset($tcaArray['config']['eval'])) {
            $tcaArray['config']['eval'] = self::mergeCommaSeperatedOptions($tcaArray['config']['eval']);
        }

        return $tcaArray;
    }

    /**
     * @param array<int, array{key: string, value: string}> $keyValue
     * @return array<string, string>
     */
    protected static function convertKeyValuePairsToAssociativeArray(array $keyValue): array
    {
        $associativeArray = [];
        foreach ($keyValue as $pairs) {
            $associativeArray[$pairs['key']] = $pairs['value'];
        }
        return $associativeArray;
    }

    /**
     * @param array<string, string> $associativeArray
     * @return array<int, array{key: string, value: string}>
     */
    protected static function convertAssociativeArrayToKeyValuePairs(array $associativeArray): array
    {
        $keyValue = [];
        foreach ($associativeArray as $key => $value) {
            $keyValue[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $keyValue;
    }

    protected static function mergeCommaSeperatedOptions(array $tcaArray): string
    {
        $mergedTca = [];
        foreach ($tcaArray as $key => $evalValue) {
            if ($evalValue) {
                $mergedTca[] = $key;
            }
        }
        return implode(',', $mergedTca);
    }
}
