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

class SubstrViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('string', 'string', 'String to search in', true);
        $this->registerArgument('search', 'string', 'String to search', true);
        $this->registerArgument('from', 'integer', 'Startpoint', true);
        $this->registerArgument('length', 'integer', 'Length', true);
    }

    /**
     * @return bool the rendered string
     */
    public function render(): bool
    {
        $string = $this->arguments['string'];
        $search = $this->arguments['search'];
        $from = $this->arguments['from'];
        $length = $this->arguments['length'];

        return substr($string, $from, $length) === $search;
    }
}
