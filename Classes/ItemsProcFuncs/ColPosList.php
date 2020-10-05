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

namespace MASK\Mask\ItemsProcFuncs;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Render the allowed colPos for nested content elements
 */
class ColPosList extends AbstractList
{

    /**
     * Render the allowed colPos for nested content elements
     * @param array $params
     */
    public function itemsProcFunc(&$params): void
    {
        // if this tt_content element is inline element of mask
        if ((int)$params['row']['colPos'] === $this->colPos) {
            // only allow mask nested element column
            $params['items'] = [
                [
                    LocalizationUtility::translate('mask_content_colpos', 'mask'),
                    $this->colPos,
                    null,
                    null
                ]
            ];
        } else {
            // if it is not inline tt_content element
            // and if other itemsProcFunc from other extension was available (e.g. gridelements),
            // then call it now and let it render the items
            if (!empty($params['config']['m_itemsProcFunc'])) {
                GeneralUtility::callUserFunction(
                    $params['config']['m_itemsProcFunc'],
                    $params,
                    $this
                );
            }
        }
    }
}
