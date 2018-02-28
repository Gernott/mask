<?php

namespace MASK\Mask\ViewHelpers;

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
class ElementCountViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * contentRepository
     *
     * @var \MASK\Mask\Domain\Repository\ContentRepository
     * @inject
     */
    protected $contentRepository = NULL;

    /**
     * Counts the occurences in tt_content
     *
     * @param string $key key of content element
     * @return int number of uses of this content element
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render($key)
    {
        $contentElements = $this->contentRepository->findByContentType('mask_' . $key)->toArray();
        return count($contentElements);
    }
}
