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
use MASK\Mask\Utility\ReusingFieldsUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RestructureReusingFieldsCommand extends Command
{
    /**
     * @var array<string, string>
     */
    protected array $maskExtensionConfiguration;
    protected LoaderRegistry $loaderRegistry;

    public function injectLoaderRegistry(LoaderRegistry $loaderRegistry): void
    {
        $this->loaderRegistry = $loaderRegistry;
    }

    public function injectMaskExtensionConfiguration(array $maskExtensionConfiguration): void
    {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
    }

    protected function configure(): void
    {
        $this->setHelp(
            'Migrates all content elements from shared field to reusable fields.' . LF .
            'This action can not be undone!' . LF .
            'This command will only be executed if you already have enabled reusable fields in the mask configuration.' . LF . LF .
            'Usage: mask:restructureReusingFields'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reusingFieldsEnabled = $this->maskExtensionConfiguration['reuse_fields'] == 1;
        if (!$reusingFieldsEnabled) {
            return 0;
        }

        $loader = $this->loaderRegistry->getActiveLoader();
        $tableDefinitionCollection = $loader->load();
        $restructuredTableDefinition = ReusingFieldsUtility::restructureTcaDefinitions($tableDefinitionCollection);
        $loader->write($restructuredTableDefinition);

        // @todo Return error code, if write was not successfully executed.
        return 0;
    }
}
