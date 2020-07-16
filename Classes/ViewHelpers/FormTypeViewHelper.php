<?php

declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 */
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
     * @author Benjamin Butschell bb@webprofil.at>
     */
    public function render(): string
    {
        $elementKey = $this->arguments['elementKey'];
        $fieldKey = $this->arguments['fieldKey'];
        $type = $this->arguments['type'];

        return $this->storageRepository->getFormType($fieldKey, $elementKey, $type);
    }
}
