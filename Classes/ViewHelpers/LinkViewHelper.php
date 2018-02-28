<?php

namespace MASK\Mask\ViewHelpers;

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
class LinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
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
     * @inject
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    /**
     * Checks Links for BE-module
     *
     * @param string $data the parent object
     * @param string $irreName The name of the irre attribut
     * @return array all irre elements of this attribut
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    public function render($data)
    {
        $this->extSettings = $this->settingsService->get();
        $url = $this->extSettings['content'] . $data . '.html';
        if (!file_exists(PATH_site . $url) || !is_file(PATH_site . $url)) {
            $content = '<div class="typo3-message message-error"><strong>' .
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.error', 'mask') .
                '</strong> ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.htmlmissing', 'mask') .
                ': <span style="text-decoration:underline;">' . $url .
                '</span></div>';
        }
        return $content;
    }
}
