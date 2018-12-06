<?php

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
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
     * @var \MASK\Mask\Domain\Service\SettingsService
     * @Inject()
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    public function initializeArguments()
    {
        $this->registerArgument('data', 'string', 'the parent object');
    }

    /**
     * Checks Links for BE-module
     *
     * @return string all irre elements of this attribut
     * @author Gernot Ploiner <gp@webprofil.at>
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
<div class="media-body"><h4 class="alert-title">' . LocalizationUtility::translate('tx_mask.content.htmlmissing',
                    'mask') . '</h4>      <p class="alert-message">' . $templatePath . '
				</p></div></div></div>';
        }
        return $content;
    }
}
