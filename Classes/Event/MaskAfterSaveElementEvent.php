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

namespace MASK\Mask\Event;

final class MaskAfterSaveElementEvent
{
    /**
     * @var string
     */
    private string $elementKey;

    /**
     * @var bool
     */
    private bool $isNewElement;

    /**
     * @param string $elementKey the key of the saved content element
     * @param bool $isNewElement weather the element is a new element or a already existing one
     */
    public function __construct(string $elementKey, bool $isNewElement)
    {
        $this->elementKey = $elementKey;
        $this->isNewElement = $isNewElement;
    }

    /**
     * @return string
     */
    public function getElementKey(): string
    {
        return $this->elementKey;
    }

    /**
     * @return bool
     */
    public function isNewElement(): bool
    {
        return $this->isNewElement;
    }
}
