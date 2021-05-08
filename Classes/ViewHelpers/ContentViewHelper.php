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

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for rendering any content element
 */
class ContentViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer Object
     */
    protected $cObj;

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'integer', 'Uid of the content element', true);
    }

    /**
     * Parse content element
     *
     * @return string parsed content element
     */
    public function render(): string
    {
        $conf = [
            'tables' => 'tt_content',
            'source' => $this->arguments['uid'],
            'dontCheckPid' => 1
        ];
        return $this->cObj->cObjGetSingle('RECORDS', $conf);
    }

    /**
     * Injects Configuration Manager
     *
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(
        ConfigurationManagerInterface $configurationManager
    ): void {
        $this->configurationManager = $configurationManager;
        $this->cObj = $this->configurationManager->getContentObject();
    }
}
