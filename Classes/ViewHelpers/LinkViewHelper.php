<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
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
    public function render()
    {
        $this->extSettings = $this->settingsService->get();
        $url = \MASK\Mask\Utility\GeneralUtility::getTemplatePath($this->extSettings, $this->arguments['data']);
        $content = '';
        if (!file_exists($url) || !is_file($url)) {
            $content = '<div class="typo3-message message-error"><strong>' .
                LocalizationUtility::translate('tx_mask.content.error', 'mask') .
                '</strong> ' . LocalizationUtility::translate('tx_mask.content.htmlmissing',
                    'mask') .
                ': <span style="text-decoration:underline;">' . $url .
                '</span></div>';
        }
        return $content;
    }
}
