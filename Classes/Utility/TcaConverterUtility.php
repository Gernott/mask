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

class TcaConverterUtility
{
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
     *
     */
    public static function convertTcaArrayToFlat(array $config, array $path = ['config']): array
    {
        $tca = [];
        foreach ($config as $key => $value) {
            $path[] = $key;
            $fullPath = implode('.', $path);
            if ($key === 'items') {
                $items = $value;
                $itemText = '';
                foreach ($items as $item) {
                    $itemText .= implode(',', $item) . "\n";
                }
                $tca[] = [$fullPath => trim($itemText)];
            } elseif (is_array($value)) {
                $tca[] = self::convertTcaArrayToFlat($value, $path);
            } elseif ($key === 'eval' || $key === 'blindLinkOptions') {
                if ($value !== '') {
                    $keys = explode(',', $value);

                    // Special handling for timestamp field, as the dateType is in the key "config.eval"
                    $dateTypesInKeys = array_values(array_intersect($keys, ['date', 'datetime', 'time', 'timesec']));
                    if (count($dateTypesInKeys) > 0) {
                        $tca[] = [$fullPath => $dateTypesInKeys[0]];
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
            if ($key === 'config.items') {
                $items = [];
                foreach (explode("\n", $value) as $line) {
                    $items[] = explode(',', $line);
                }
                $value = $items;
            }
            // This is for timestamps as it has a fake tca property for eval date, datetime, ...
            if ($key === 'config.eval' && in_array($value, ['date', 'datetime', 'time', 'timesec'])) {
                $key = 'config.eval.' . $value;
                $value = 1;
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

        if (isset($tcaArray['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'])) {
            $tcaArray['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] = self::mergeCommaSeperatedOptions($tcaArray['config']['fieldControl']['linkPopup']['options']['blindLinkOptions']);
        }

        return $tcaArray;
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
