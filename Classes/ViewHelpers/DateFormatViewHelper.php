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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DateFormatViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('field', 'array', 'The field config array.');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $format = 'd-m-Y';
        $field = $renderChildrenClosure();
        $evals = GeneralUtility::trimExplode(',', $field['config']['eval']);
        if (in_array('datetime', $evals)) {
            $format = 'H:i ' . $format;
        }
        return $format;
    }
}
