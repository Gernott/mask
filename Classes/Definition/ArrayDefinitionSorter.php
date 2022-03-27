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

/**
 * @internal
 */
final class ArrayDefinitionSorter
{
    /**
     * @var string[]
     */
    private $excludedKeys = [];

    /**
     * @param string[] $keys
     */
    public function setExcludedKeys(array $keys): void
    {
        $this->excludedKeys = $keys;
    }

    /**
     * Sort the given array by keys recursively while taking care of exceptions.
     *
     * @param array<string|int, mixed> $array
     * @return array<string|int, mixed>
     */
    public function sort(array $array): array
    {
        ksort($array);
        foreach ($array as $key => $item) {
            if (in_array($key, $this->excludedKeys, true)) {
                continue;
            }
            if (is_array($item)) {
                $array[$key] = $this->sort($item);
            }
        }

        return $array;
    }
}
