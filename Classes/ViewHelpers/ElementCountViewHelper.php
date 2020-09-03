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

use MASK\Mask\Domain\Repository\ContentRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ElementCountViewHelper extends AbstractViewHelper
{

    /**
     * contentRepository
     *
     * @var ContentRepository
     */
    protected $contentRepository;

    public function __construct(ContentRepository $contentRepository)
    {
        $this->contentRepository = $contentRepository;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'key of content element');
    }

    /**
     * Counts the occurences in tt_content
     *
     * @return int number of uses of this content element
     */
    public function render(): int
    {
        return $this->contentRepository->findByContentType('mask_' . $this->arguments['key'])->count();
    }
}
