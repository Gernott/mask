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
    }

    public function load(): TableDefinitionCollection
    {
        $maskJsonFilePath = $this->validateGetJsonFilePath();
        if ($this->tableDefinitionCollection === null) {
            $this->tableDefinitionCollection = new TableDefinitionCollection();
            // The file might not exist yet. Will be created as soon as write() is called.
            if (file_exists($maskJsonFilePath)) {
                $json = json_decode(file_get_contents($maskJsonFilePath), true, 512, 4194304); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
                $this->tableDefinitionCollection = TableDefinitionCollection::createFromArray($json);
            }
        }

        return $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tableDefinitionCollection): void
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $maskJsonFilePath = $this->validateGetJsonFilePath();
        $result = GeneralUtility::writeFile($maskJsonFilePath, json_encode($tableDefinitionCollection->toArray(), 4194304 | JSON_PRETTY_PRINT)); // @todo replace with JSON_THROW_ON_ERROR in Mask v8.0
        if (!$result) {
            throw new \InvalidArgumentException('The Mask JSON file "' . $this->maskExtensionConfiguration['json'] . '" could not be written. Check your file permissions.', 1639169283);
        }
    }

    protected function validateGetJsonFilePath(): string
    {
        $maskJsonPath = $this->getJsonFilePath();
        if ($maskJsonPath === '' && isset($this->maskExtensionConfiguration['json'])) {
            throw new \InvalidArgumentException('The path to the Mask JSON file "' . $this->maskExtensionConfiguration['json'] . '" is not a correct path in the file system.', 1639220370);
        }

        return $maskJsonPath;
    }

    protected function getJsonFilePath(): string
    {
        if (($this->maskExtensionConfiguration['json'] ?? '') === '') {
           return '';
        }

        return MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['json']);
    }
}
