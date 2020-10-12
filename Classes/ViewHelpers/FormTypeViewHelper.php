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

use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FormTypeViewHelper extends AbstractViewHelper
{

    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    public function __construct(StorageRepository $storageRepository)
    {
        $this->storageRepository = $storageRepository;
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('elementKey', 'string', 'Key of element', true);
        $this->registerArgument('fieldKey', 'string', 'Key if field', true);
        $this->registerArgument('type', 'string', 'Key of element', false, 'tt_content');
    }

    /**
     * Returns the label of a field in an element
     *
     * @return string formType
     */
    public function render(): string
    {
        $elementKey = $this->arguments['elementKey'];
        $fieldKey = $this->arguments['fieldKey'];
        $type = $this->arguments['type'];

        if ($fieldKey === 'bodytext' && $type === 'tt_content') {
            return FieldType::RICHTEXT;
        }

        return $this->storageRepository->getFormType($fieldKey, $elementKey, $type);
    }
}
