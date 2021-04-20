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

use MASK\Mask\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LinkoptionViewHelper extends AbstractViewHelper
{

    /**
     * Utility
     *
     * @var GeneralUtility
     */
    protected $generalUtility;

    public function __construct(GeneralUtility $generalUtility)
    {
        $this->generalUtility = $generalUtility;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('fieldKey', 'string', 'TCA Type', true);
        $this->registerArgument('evalValue', 'string', 'value to search for', true);
        $this->registerArgument('type', 'string', 'parent table', true);
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @return bool $evalValue is set
     */
    public function render(): bool
    {
        return $this->generalUtility->isBlindLinkOptionSet($this->arguments['fieldKey'], $this->arguments['evalValue'], $this->arguments['type']);
    }
}
