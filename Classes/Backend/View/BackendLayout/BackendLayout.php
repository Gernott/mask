<?php
declare(strict_types=1);

namespace MASK\Mask\Backend\View\BackendLayout;

/**
 * Class to represent a backend layout.
 */
class BackendLayout extends \TYPO3\CMS\Backend\View\BackendLayout\BackendLayout
{

    /**
     * @return array
     */
    public function getColumnPositionNumbers(): array
    {
        $colPosList = parent::getColumnPositionNumbers();
        $colPosList[] = 999;
        return array_unique($colPosList);
    }
}
