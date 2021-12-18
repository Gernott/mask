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

namespace MASK\Mask\Loader;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\TcaConverter;

trait ConfigCleanerTrait
{
    use WithConfigurationLoaderTrait;

    /**
     * This method removes all tca options defined which aren't available in Mask.
     */
    public function cleanUpConfig(TableDefinitionCollection $tableDefinitionCollection): void
    {
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca as $tcaFieldDefinition) {
                $tabConfig = $this->configurationLoader->loadTab((string)$tcaFieldDefinition->type);
                $tcaOptions = [];
                foreach ($tabConfig as $options) {
                    foreach ($options as $row) {
                        $tcaOptions[] = array_keys($row);
                    }
                }
                $tcaOptions = array_merge([], ...$tcaOptions);

                $tcaFieldDefinition->realTca = array_filter(TcaConverter::convertTcaArrayToFlat($tcaFieldDefinition->realTca), static function ($key) use ($tcaOptions) {
                    return in_array($key, $tcaOptions, true);
                }, ARRAY_FILTER_USE_KEY);
            }
        }
    }
}
