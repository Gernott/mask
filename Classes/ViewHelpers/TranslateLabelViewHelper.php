<?php
declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', '', true);
        $this->registerArgument('extensionName', 'string', '');
    }

    /**
     * The given key will be translated. If the result is empty, the key will be returned.
     *
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    public function render(): string
    {
        $key = $this->arguments['key'];
        $extensionName = $this->arguments['extensionName'];

        if (empty($key) || strpos($key, 'LLL') > 0) {
            return $key;
        }

        $request = $this->renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName ?? $request->getControllerExtensionName();
        $result = LocalizationUtility::translate($key, $extensionName);
        return (empty($result) ? $key : $result);
    }
}
