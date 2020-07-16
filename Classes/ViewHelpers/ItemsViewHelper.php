<?php

declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class ItemsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('items', 'array', '', true);
    }

    /**
     * Returns all elements that use this field
     *
     * @return string items as string
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render(): string
    {
        $itemArray = [];

        if ($this->arguments['items']) {
            foreach ($this->arguments['items'] as $item) {
                $itemArray[] = implode(',', $item);
            }
        }

        return implode("\n", $itemArray);
    }
}
