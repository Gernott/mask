<?php
declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Domain\Repository\ContentRepository;
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
     * @var ContentRepository
     * @Inject()
     */
    protected $contentRepository;

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'key of content element');
    }

    /**
     * Counts the occurences in tt_content
     *
     * @return int number of uses of this content element
     * @noinspection PhpUndefinedMethodInspection
     */
    public function render(): int
    {
        return $this->contentRepository->findByContentType('mask_' . $this->arguments['key'])->count();
    }
}
