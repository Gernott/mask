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

namespace MASK\Mask\Command;

use MASK\Mask\Loader\LoaderRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConvertFormatCommand extends Command
{
    /**
     * @var LoaderRegistry
     */
    protected $loaderRegistry;

    /**
     * @param LoaderRegistry $loaderRegistry
     */
    public function injectLoaderRegistry(LoaderRegistry $loaderRegistry)
    {
        $this->loaderRegistry = $loaderRegistry;
    }

    protected function configure()
    {
        $loaderRegistry = GeneralUtility::makeInstance(LoaderRegistry::class);
        $availableLoaders = implode(', ', array_keys($loaderRegistry->getLoaders()));
        $this->setHelp(
            'Converts a Mask storage format into another.' . LF .
            'The paths configured in the extension configuration are used and will override existing files!' . LF .
            'First argument is the source format and second argument is the target format.' . LF .  LF .
            'Usage: mask:convert [source] [target]' . LF .  LF .
            'Not providing the second argument leads to persisting the first format. This can be used for updating old configuration.' . LF .  LF .
            'Available formats are: ' . $availableLoaders
        );

        $this->addArgument(
            'source',
            InputArgument::REQUIRED,
            'The source format'
        );

        $this->addArgument(
            'target',
            InputArgument::OPTIONAL,
            'The target format'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceLoader = $this->loaderRegistry->getLoader($input->getArgument('source'));

        if ($input->getArgument('target')) {
            $targetLoader = $this->loaderRegistry->getLoader($input->getArgument('target'));
        } else {
            $targetLoader = $sourceLoader;
        }

        $targetLoader->write($sourceLoader->load());

        return 1;
    }
}
