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

namespace MASK\Mask\DependencyInjection;

use MASK\Mask\Loader\LoaderRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
final class LoaderPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @param string $tagName
     */
    public function __construct(string $tagName)
    {
        $this->tagName = $tagName;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        $loaderFactoryDefinition = $container->findDefinition(LoaderRegistry::class);
        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
            $definition = $container->findDefinition($id);
            if (!$definition->isAutoconfigured() || $definition->isAbstract()) {
                continue;
            }

            $definition->setShared(true)->setPublic(true);
            foreach ($tags as $attributes) {
                if (!isset($attributes['identifier'])) {
                    throw new \InvalidArgumentException(
                        'Service tag "mask.loader" requires the attribute "identifier" to be set. Missing in "' . $id . '".',
                        1632644430
                    );
                }
                $loaderFactoryDefinition->addMethodCall('addLoader', [$definition, $attributes['identifier']]);
            }
        }
    }
}
