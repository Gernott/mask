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

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the allowed CTypes for nested content elements
 */
class CTypeList
{
    protected TableDefinitionCollection $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

    /**
     * Render the allowed CTypes for nested content elements
     */
    public function itemsProcFunc(array &$params, $parentObj): void
    {
        // if this tt_content element is inline element of mask
        if ((int)($params['row']['colPos'] ?? 0) === 999) {
            $fieldKey = $params['row']['tx_mask_content_role'] ?? '';
            if ($fieldKey === '' && isset($GLOBALS['TYPO3_REQUEST']->getParsedBody()['ajax']['context'])) {
                $ajaxContext = json_decode($GLOBALS['TYPO3_REQUEST']->getParsedBody()['ajax']['context'], true, 512, JSON_THROW_ON_ERROR);
                $config = json_decode($ajaxContext['config'], true, 512, JSON_THROW_ON_ERROR);
                $fieldKey = $config['foreign_match_fields']['tx_mask_content_role'] ?? '';
            }

            $table = $params['row']['tx_mask_content_tablenames'] ?? '';
            if ($table === '') {
                $table = $params['inlineParentTableName'] ?? $this->tableDefinitionCollection->getTableByField($fieldKey);
            }

            // load the json configuration of this field
            $fieldDefinition = $this->tableDefinitionCollection->loadField($table, $fieldKey);

            // if there is a restriction of cTypes specified
            if ($fieldDefinition instanceof TcaFieldDefinition && $fieldDefinition->cTypes !== []) {
                // prepare array of allowed cTypes, with cTypes as keys
                $cTypes = array_flip($fieldDefinition->cTypes);

                // and check each item if it is allowed. if not, unset it
                foreach ($params['items'] as $itemKey => $item) {
                    if (!isset($cTypes[$item[1]])) {
                        unset($params['items'][$itemKey]);
                    }
                }
            }
        } elseif (!empty($params['config']['m_itemsProcFunc'])) {
            // if it is not inline tt_content element
            // and if other itemsProcFunc from other extension was available (e.g. gridelements),
            // then call it now and let it render the items
            GeneralUtility::callUserFunction(
                $params['config']['m_itemsProcFunc'],
                $params,
                $parentObj
            );
        }
    }
}
