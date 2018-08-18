<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Annotation\Inject;

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
     * @Inject()
     */
    protected $contentRepository = null;

    /**
     * Counts the occurences in tt_content
     *
     * @param string $key key of content element
     * @return int number of uses of this content element
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render($key)
    {
        return $this->contentRepository->findByContentType('mask_' . $key)->count();
    }
}
