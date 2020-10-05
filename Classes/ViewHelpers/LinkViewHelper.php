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

use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LinkViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * SettingsService
     *
     * @var SettingsService
     */
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('data', 'string', 'the parent object');
    }

    /**
     * Checks Links for BE-module
     *
     * @return string all irre elements of this attribut
     */
    public function render(): string
    {
        $templatePath = MaskUtility::getTemplatePath(
            $this->settingsService->get(),
            $this->arguments['data']
        );
        $content = '';

        if (!file_exists($templatePath) || !is_file($templatePath)) {
            $content = '<div class="alert alert-warning"><div class="media">
<div class="media-left"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i>
<i class="fa fa-exclamation fa-stack-1x"></i></span></div>
<div class="media-body"><h4 class="alert-title">' . LocalizationUtility::translate(
                'tx_mask.content.htmlmissing',
                'mask'
            ) . '</h4>      <p class="alert-message">' . $templatePath . '
				</p></div></div></div>';
        }
        return $content;
    }
}
