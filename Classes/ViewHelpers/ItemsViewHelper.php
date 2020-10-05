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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
