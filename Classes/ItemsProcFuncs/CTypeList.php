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

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\AffixUtility as MaskUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Render the allowed CTypes for nested content elements
 */
class CTypeList extends AbstractList
{

    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    public function __construct(StorageRepository $storageRepository, FieldHelper $fieldHelper)
    {
        $this->storageRepository = $storageRepository;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * Render the allowed CTypes for nested content elements
     * @param array $params
     */
    public function itemsProcFunc(&$params): void
    {
        // if this tt_content element is inline element of mask
        if ((int)$params['row']['colPos'] === $this->colPos) {
            $fieldKey = '';

            if (isset($GLOBALS['TYPO3_REQUEST']->getParsedBody()['ajax']['context'])) {
                $ajaxContext = json_decode($GLOBALS['TYPO3_REQUEST']->getParsedBody()['ajax']['context'], true, 512, 4194304);
                $config = json_decode($ajaxContext['config'], true, 512, 4194304);
                $fieldKey = AffixUtility::removeMaskParentSuffix($config['foreign_field']);
            } else {
                $fields = $params['row'];
                foreach ($fields as $key => $field) {
                    // search for the parent field, to get the key of mask field this content element belongs to
                    if ($field > 0 && AffixUtility::hasMaskPrefix($key) && MaskUtility::hasMaskParentSuffix($key)) {

                        // if a parent field was found, that is filled with a uid, extract the mask field name from it
                        $fieldKey = AffixUtility::removeMaskParentSuffix($key);

                        // if one parent field was found, don't continue search, there can only be one parent
                        break;
                    }
                }
            }

            // This works since TYPO3 10.4.16 or v11.2
            if (isset($params['inlineParentTableName'])) {
                $table = $params['inlineParentTableName'];
            // Else we have to figure out from url
            } else if (preg_match_all('/tx_mask_\w+/', ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['ajax'][0] ?? ''), $pregResult) && count($pregResult[0]) > 1) {
                // Get the second last entry
                $table = $pregResult[0][count($pregResult[0]) - 2];
            } else {
                $table = $this->fieldHelper->getFieldType($fieldKey);
            }

            // load the json configuration of this field
            $fieldConfiguration = $this->storageRepository->loadField($table, $fieldKey);

            // if there is a restriction of cTypes specified
            if (is_array($fieldConfiguration['cTypes'])) {

                // prepare array of allowed cTypes, with cTypes as keys
                $cTypes = array_flip($fieldConfiguration['cTypes']);

                // and check each item if it is allowed. if not, unset it
                foreach ($params['items'] as $itemKey => $item) {
                    if (!isset($cTypes[$item[1]])) {
                        unset($params['items'][$itemKey]);
                    }
                }
            }
        } else { // if it is not inline tt_content element
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
