<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class ElementCountViewHelper extends AbstractViewHelper
{

    /**
     * contentRepository
     *
     * @var \MASK\Mask\Domain\Repository\ContentRepository
     * @Inject()
     */
    protected $contentRepository = null;

    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', 'key of content element');
    }

    /**
     * Counts the occurences in tt_content
     *
     * @return int number of uses of this content element
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render()
    {
        return $this->contentRepository->findByContentType('mask_' . $this->arguments['key'])->count();
    }
}
