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

namespace MASK\Mask\Updates;

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Loader\LoaderRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class MoveRteOptions implements UpgradeWizardInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    public function __construct()
    {
        $this->loader = GeneralUtility::makeInstance(LoaderRegistry::class)->getActivateLoader();
    }

    public function getIdentifier(): string
    {
        return 'moveRteOptions';
    }

    public function getTitle(): string
    {
        return 'Update Mask JSON file (RTE options)';
    }

    public function getDescription(): string
    {
        return 'This update moves the option "rte" from the elements option to tca as "enableRichtext".';
    }

    public function executeUpdate(): bool
    {
        $tableDefinitionCollection = $this->loader->load();
        $tableDefinitionArray = $tableDefinitionCollection->toArray(false);
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->elements as $element) {
                foreach ($element->options as $index => $option) {
                    if ($option === 'rte') {
                        // Always unset rte option.
                        unset($tableDefinitionArray[$tableDefinition->table]['elements'][$element->key]['options'][$index]);

                        // If the field does not exist (anymore) continue.
                        $field = $tableDefinitionArray[$tableDefinition->table]['elements'][$element->key]['columns'][$index] ?? '';
                        if ($field === '') {
                            continue;
                        }

                        // Load the field as TcaFieldDefinition and catch possible exception.
                        try {
                            $tcaDefinition = TcaFieldDefinition::createFromFieldArray($tableDefinitionArray[$tableDefinition->table]['tca'][$field] ?? []);
                        } catch (\InvalidArgumentException $e) {
                            continue;
                        }

                        // Add config.enableRichtext for non-core fields.
                        if (!$tcaDefinition->isCoreField) {
                            $tableDefinitionArray[$tableDefinition->table]['tca'][$field]['config']['enableRichtext'] = 1;
                        }

                        // Add the richtext type
                        $tableDefinitionArray[$tableDefinition->table]['tca'][$field]['type'] = FieldType::RICHTEXT;
                    }
                }
            }
        }
        $this->loader->write(TableDefinitionCollection::createFromArray($tableDefinitionArray));
        return true;
    }

    public function updateNecessary(): bool
    {
        $tableDefinitionCollection = $this->loader->load();
        foreach ($tableDefinitionCollection as $tableDefinition) {
            foreach ($tableDefinition->elements as $element) {
                foreach ($element->options as $option) {
                    if ($option === 'rte') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getPrerequisites(): array
    {
        return [];
    }
}
