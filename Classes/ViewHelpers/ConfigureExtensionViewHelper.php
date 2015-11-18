<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @author Benjamin Butschell bb@webprofil.at>
 *
 */
class ConfigureExtensionViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders link tag to extension manager configuration
	 * @author Benjamin Butschell bb@webprofil.at>
	 */
	public function render() {
		$urlParameters = array(
			 'tx_extensionmanager_tools_extensionmanagerextensionmanager[extension][key]' => 'mask',
			 'tx_extensionmanager_tools_extensionmanagerextensionmanager[action]' => 'showConfigurationForm',
			 'tx_extensionmanager_tools_extensionmanagerextensionmanager[controller]' => 'Configuration',
			 'returnUrl' => '/typo3/index.php?M=web_list&moduleToken=af60fa4a531eeec50e8d9890a4c278271d6c9c17&id=1&imagemode=1'
		);
		$url = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('tools_ExtensionmanagerExtensionmanager', $urlParameters);
		return'<a href="' . htmlspecialchars($url) . '">' . $this->renderChildren() . '</a>';
	}

}
