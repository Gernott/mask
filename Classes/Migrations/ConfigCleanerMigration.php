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
use MASK\Mask\Utility\CompatibilityUtility;
use MASK\Mask\Utility\TcaConverter;

class ConfigCleanerMigration implements RepeatableMigrationInterface
{
    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(ConfigurationLoaderInterface $configurationLoader)
    {
        $this->configurationLoader = $configurationLoader;
    }

    /**
     * This method removes all tca options defined which aren't available in Mask.
     */
    public function migrate(TableDefinitionCollection $tableDefinitionCollection): TableDefinitionCollection
    {
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->tca as $tcaFieldDefinition) {
                if (!$tcaFieldDefinition->hasFieldType() || $tcaFieldDefinition->isCoreField) {
                    continue;
                }
                $tabConfig = $this->configurationLoader->loadTab((string)$tcaFieldDefinition->getFieldType());
                $defaultsOut = array_keys($this->configurationLoader->loadDefaults()[(string)$tcaFieldDefinition->getFieldType()]['tca_out'] ?? []);
                $tcaOptions = [];
                foreach ($tabConfig as $options) {
                    foreach ($options as $row) {
                        $tcaOptions[] = array_keys($row);
                    }
                }
                // These fields are not defined in the tabs files, but instead are hard-coded in the template.
                $fieldsToNotThrowAway = ['label', 'description'];
                $tcaOptions = array_merge([], $fieldsToNotThrowAway, $defaultsOut, ...$tcaOptions);

                $cleanedConfig = array_filter(TcaConverter::convertTcaArrayToFlat($tcaFieldDefinition->realTca), static function ($key) use ($tcaOptions) {
                    return in_array($key, $tcaOptions, true) || CompatibilityUtility::isFirstPartOfStr($key, 'config.eval');
                }, ARRAY_FILTER_USE_KEY);

                $tcaFieldDefinition->realTca = TcaConverter::convertFlatTcaToArray($cleanedConfig);
            }
        }

        return $tableDefinitionCollection;
    }

    public function forVersionBelow(): string
    {
        return '';
    }
}
