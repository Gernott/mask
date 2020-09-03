<?php

declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @author Benjamin Butschell bb@webprofil.at>
 */
class SubstrViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('string', 'string', 'String to search in', true);
        $this->registerArgument('search', 'string', 'String to search', true);
        $this->registerArgument('from', 'integer', 'Startpoint', true);
        $this->registerArgument('length', 'integer', 'Length', true);
    }

    /**
     * @return bool the rendered string
     */
    public function render(): bool
    {
        $string = $this->arguments['string'];
        $search = $this->arguments['search'];
        $from = $this->arguments['from'];
        $length = $this->arguments['length'];

        return substr($string, $from, $length) === $search;
    }
}
