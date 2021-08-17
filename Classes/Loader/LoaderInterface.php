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
 * The ressource is abstracted away and can come from anywhere as long as the
 * defined Mask configuration has the correct structure.
 */
interface LoaderInterface
{
    /**
     * Loads the Mask configuration from any ressource.
     *
     * @return TableDefinitionCollection
     */
    public function load(): TableDefinitionCollection;

    /**
     * Takes the tca definition as input and writes it to the given ressource.
     *
     * @param TableDefinitionCollection $tcaDefinition
     */
    public function write(TableDefinitionCollection $tcaDefinition): void;
}
