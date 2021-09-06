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

/**
 * This interface enables to provide loaders for Mask.
 * The resource is abstracted away and can come from anywhere as long as the
 * defined Mask configuration has the correct structure.
 */
interface LoaderInterface
{
    /**
     * Loads the Mask configuration from any resource.
     *
     * @return TableDefinitionCollection
     */
    public function load(): TableDefinitionCollection;

    /**
     * Takes the table definition collection as input and writes it to the given resource.
     *
     * @param TableDefinitionCollection $tableDefinitionCollection
     */
    public function write(TableDefinitionCollection $tableDefinitionCollection): void;
}
