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

use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonLoader implements LoaderInterface
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var string
     */
    protected $path = '';

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $path = $extensionConfiguration->get('mask')['json'];
        if ($path === '') {
            throw new \InvalidArgumentException('The path to the Mask JSON file must not be empty.', 1628599913);
        }
        $this->path = $path;
    }

    public function load(): TableDefinitionCollection
    {
        if ($this->tableDefinitionCollection === null) {
            $this->tableDefinitionCollection = new TableDefinitionCollection();
            $file = MaskUtility::getFileAbsFileName($this->path);
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not exsist.', $file), 1628599433);
            }
            $json = json_decode(file_get_contents($file), true, 512, 4194304);
            $this->tableDefinitionCollection = TableDefinitionCollection::createFromInternalArray($json);
        }
        return $this->tableDefinitionCollection;
    }

    public function write(TableDefinitionCollection $tcaDefinition): void
    {
        $file = MaskUtility::getFileAbsFileName($this->path);
        GeneralUtility::writeFile($file, json_encode($tcaDefinition->toArray(), 4194304 | JSON_PRETTY_PRINT));
        $this->tableDefinitionCollection = $tcaDefinition;
    }
}
