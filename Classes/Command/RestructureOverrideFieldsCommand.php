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

use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\OverrideFieldsUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Features;

class RestructureOverrideFieldsCommand extends Command
{
    protected Features $features;
    protected LoaderInterface $loader;

    public function injectLoader(LoaderInterface $loader): void
    {
        $this->loader = $loader;
    }

    public function injectFeatures(Features $features): void
    {
        $this->features = $features;
    }

    protected function configure(): void
    {
        $this->setHelp(
            'Migrates all content elements from shared to override fields.' . LF .
            'This action can not be undone!' . LF .
            'This command will only be executed if you already have enabled override fields in the Mask configuration.' . LF . LF .
            'Usage: mask:restructureOverrideFields'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reusingFieldsEnabled = $this->features->isFeatureEnabled('overrideSharedFields');
        if (!$reusingFieldsEnabled) {
            return Command::SUCCESS;
        }

        $tableDefinitionCollection = $this->loader->load();
        $restructuredTableDefinitionCollection = OverrideFieldsUtility::restructureTcaDefinitions($tableDefinitionCollection);
        $this->loader->write($restructuredTableDefinitionCollection);
        return Command::SUCCESS;
    }
}
