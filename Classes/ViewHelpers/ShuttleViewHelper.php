<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 * <mask:shuttle data="{data}" name="tx_mask_slider"/>
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 * @todo Test if neccessary in selectbox-shuttle-frontend
 *
 */
class ShuttleViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('table', 'string', 'The name of the table', true);
        $this->registerArgument('field', 'string', 'The name of the field', true);
    }

    /**
     * Returns Shuttle-Elements of Data-Object
     *
     * @return array all irre elements of this attribut
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    public function render()
    {
        $table = $this->arguments['table'];
        $field = $this->arguments['field'];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder
            ->select('*')
            ->where('uid IN (' . $field . ')');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder->execute()->fetchAll();
    }
}
