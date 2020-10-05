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

use MASK\Mask\Helper\FieldHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MultiuseViewHelper extends AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var FieldHelper
     */
    protected $fieldHelper;

    public function __construct(FieldHelper $fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'TCA Type', true);
        $this->registerArgument('elementKey', 'string', 'Key of element', true);
    }

    /**
     * Returns all elements that use this field
     *
     * @return array elements in use
     */
    public function render(): array
    {
        $key = $this->arguments['key'];
        $elementKey = $this->arguments['elementKey'];

        $type = $this->fieldHelper->getFieldType($key, $elementKey);

        return $this->fieldHelper->getStorageRepository()->getElementsWhichUseField($key, $type);
    }
}
