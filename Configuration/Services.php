<?php

declare(strict_types=1);

namespace MASK\Mask;

use MASK\Mask\DependencyInjection\LoaderPass;
use MASK\Mask\Migrations\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->addCompilerPass(new LoaderPass('mask.loader'));
    $containerBuilder->registerForAutoconfiguration(MigrationInterface::class)->addTag('mask.migration');
};
