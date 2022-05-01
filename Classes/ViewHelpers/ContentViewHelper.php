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

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for rendering any content element
 */
class ContentViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @var ContentObjectRenderer Object
     */
    protected $cObj;

    public function injectContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): void
    {
        $this->cObj = $contentObjectRenderer;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'integer', 'Uid of the content element', true);
    }

    /**
     * Parse content element
     *
     * @return string parsed content element
     */
    public function render(): string
    {
        trigger_error('The MASK\Mask\ViewHelpers\ContentViewHelper will be removed in Mask v8. Please use f:cObject in combination with lib.tx_mask.content instead.', E_USER_DEPRECATED);
        $conf = [
            'tables' => 'tt_content',
            'source' => $this->arguments['uid'],
            'dontCheckPid' => 1,
        ];
        return $this->cObj->cObjGetSingle('RECORDS', $conf);
    }
}
