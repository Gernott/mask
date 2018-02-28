<?php

namespace MASK\Mask\ItemsProcFuncs;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Render the allowed colPos for nested content elements
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class ColPosList extends AbstractList
{

    /**
     * Render the allowed colPos for nested content elements
     * @param array $params
     */
    public function itemsProcFunc(&$params)
    {
        // if this tt_content element is inline element of mask
        if ($params["row"]["colPos"] == $this->colPos) {
            // only allow mask nested element column
            $params["items"] = array(
                array(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mask_content_colpos', 'mask'),
                    $this->colPos,
                    null,
                    null
                )
            );
        } else { // if it is not inline tt_content element
            // and if other itemsProcFunc from other extension was available (e.g. gridelements),
            // then call it now and let it render the items
            if (!empty($params["config"]["m_itemsProcFunc"])) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($params["config"]["m_itemsProcFunc"], $params, $this);
            }
        }
    }
}
