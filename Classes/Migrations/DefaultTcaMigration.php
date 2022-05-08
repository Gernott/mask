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

use MASK\Mask\ConfigurationLoader\ConfigurationLoaderInterface;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\TcaConverter;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class DefaultTcaMigration implements RepeatableMigrationInterface
{
    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(ConfigurationLoaderInterface $configurationLoader)
    {
        $this->configurationLoader = $configurationLoader;
    }

    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        // Add defaults, if missing. This can happen on updates or when the defaults change.
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca as $tcaFieldDefinition) {
                if (!$tcaFieldDefinition->hasFieldType() || $tcaFieldDefinition->isCoreField) {
                    continue;
                }
                $tcaDefaults = $this->configurationLoader->loadDefaults()[(string)$tcaFieldDefinition->getFieldType()];
                $tcaDefaults = $tcaDefaults['tca_out'] ?? [];
                $flatTcaFieldDefinition = TcaConverter::convertTcaArrayToFlat($tcaFieldDefinition->realTca);

                // If the defaults are a subset of the current tca, continue.
                if (array_diff_assoc($tcaDefaults, $flatTcaFieldDefinition) === []) {
                    continue;
                }

                ArrayUtility::mergeRecursiveWithOverrule($flatTcaFieldDefinition, $tcaDefaults);
                $tcaFieldDefinition->realTca = TcaConverter::convertFlatTcaToArray($flatTcaFieldDefinition);
            }
        }

        return $tableDefinitionCollection;
    }

    public function forVersionBelow(): string
    {
        return '';
    }
}
