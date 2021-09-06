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
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonLoader implements LoaderInterface
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    public function __construct(array $maskExtensionConfiguration)
    {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        if ($this->maskExtensionConfiguration['json'] === '') {
            throw new \InvalidArgumentException('The path to the Mask JSON file must not be empty.', 1628599913);
        }
    }

    public function load(): TableDefinitionCollection
    {
        if ($this->tableDefinitionCollection === null) {
            $this->tableDefinitionCollection = new TableDefinitionCollection();
            $file = MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['json']);
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $file), 1628599433);
            }
            $json = json_decode(file_get_contents($file), true, 512, 4194304); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
            $this->tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
        }
        return $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tableDefinitionCollection): void
    {
        $file = MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['json']);
        GeneralUtility::writeFile($file, json_encode($tableDefinitionCollection->toArray(), 4194304 | JSON_PRETTY_PRINT)); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }
}
