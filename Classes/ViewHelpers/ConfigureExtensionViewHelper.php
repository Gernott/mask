<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class ConfigureExtensionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
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
        $urlParameters = array(
            'tx_extensionmanager_tools_extensionmanagerextensionmanager[extension][key]' => 'mask',
            'tx_extensionmanager_tools_extensionmanagerextensionmanager[action]' => 'showConfigurationForm',
            'tx_extensionmanager_tools_extensionmanagerextensionmanager[controller]' => 'Configuration',
            'returnUrl' => BackendUtility::getModuleUrl('tools_MaskMask', array(
                'tx_mask_tools_maskmask[controller]' => $this->controllerContext->getRequest()->getControllerName(),
                'tx_mask_tools_maskmask[action]' => $this->controllerContext->getRequest()->getControllerActionName(),
            )),
        );
        $url = BackendUtility::getModuleUrl('tools_ExtensionmanagerExtensionmanager', $urlParameters);
        return '<a href="' . htmlspecialchars($url) . '">' . $this->renderChildren() . '</a>';
    }
}
