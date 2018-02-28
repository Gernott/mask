<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper that translates a language label, if the result is empty, the label will be returned.
 *
 * Example:
 * {namespace mask=MASK\Mask\ViewHelpers}
 * <mask:translateLabel key="{key}" />
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Fabian Galinski <fabian@sgalinski.de>
 *
 */
class TranslateLabelViewHelper extends AbstractViewHelper
{

    /**
     * The given key will be translated. If the result is empty, the key will be returned.
     *
     * @param string $key
     * @param string $extensionName
     * @return string
     */
    public function render($key = NULL, $extensionName = NULL)
    {
        if (empty($key) || strpos($key, 'LLL') > 0) {
            return $key;
        }

        $request = $this->renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName === NULL ? $request->getControllerExtensionName() : $extensionName;
        $result = LocalizationUtility::translate($key, $extensionName);
        return (empty($result) ? $key : $result);
    }
}
