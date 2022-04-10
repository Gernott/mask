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

class LoaderRegistry
{
    /**
     * @var array<string, LoaderInterface>
     */
    protected $loaders = [];

    /**
     * @var array
     */
    protected $maskExtensionConfiguration = [];

    public function __construct(array $maskExtensionConfiguration)
    {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
    }

    public function addLoader(LoaderInterface $loader, string $identifier): void
    {
        $this->loaders[$identifier] = $loader;
    }

    public function hasLoader(string $identifier): bool
    {
        return isset($this->loaders[$identifier]);
    }

    public function getLoader(string $identifier): LoaderInterface
    {
        if (!$this->hasLoader($identifier)) {
            throw new \InvalidArgumentException(
                sprintf('No loader registered for the identifier "%s".', $identifier),
                1632646257
            );
        }
        return $this->loaders[$identifier];
    }

    public function getLoaders(): array
    {
        return $this->loaders;
    }

    public function getActivateLoader(): LoaderInterface
    {
        $identifier = $this->maskExtensionConfiguration['loader_identifier'] ?? '';

        // Fallback to JsonLoader.
        if ($identifier === '') {
            return $this->loaders['json'];
        }

        if (!$this->hasLoader($identifier)) {
            throw new \InvalidArgumentException(
                sprintf('No loader registered for the identifier "%s".', $identifier),
                1632646256
            );
        }

        return $this->loaders[$identifier];
    }

    public function loadActiveDefinition(): TableDefinitionCollection
    {
        return $this->getActivateLoader()->load();
    }
}
