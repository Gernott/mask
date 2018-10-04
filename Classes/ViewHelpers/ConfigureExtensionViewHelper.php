<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class ConfigureExtensionViewHelper extends AbstractViewHelper
{

    /**
     * We must not return encoded html
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Renders link tag to extension manager configuration
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render()
    {
        $url = BackendUtility::getModuleUrl('tools_toolssettings');
        return '<a href="' . (string)$url . '">' . $this->renderChildren() . '</a>';
    }
}
