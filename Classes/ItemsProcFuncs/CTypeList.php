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
 * Render the allowed CTypes for nested content elements
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class CTypeList extends AbstractList
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * Render the allowed CTypes for nested content elements
     * @param array $params
     */
    public function itemsProcFunc(&$params)
    {
        // if this tt_content element is inline element of mask
        if ($params["row"]["colPos"] == $this->colPos) {
            $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
            $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');

            if (isset($_REQUEST["ajax"]["context"])) {
                $ajaxContext = json_decode($_REQUEST["ajax"]["context"]);
                $fieldKey = str_replace("_parent", "", $ajaxContext->config->foreign_field);
            } else {
                $fields = $params["row"];
                foreach ($fields as $key => $field) {
                    // search for the parent field, to get the key of mask field this content element belongs to
                    if ($this->startsWith($key, "tx_mask_") && $this->endsWith($key, "_parent") && $field > 0) {

                        // if a parent field was found, that is filled with a uid, extract the mask field name from it
                        $fieldKey = str_replace("_parent", "", $key);

                        // if one parent field was found, don't continue search, there can only be one parent
                        break;
                    }
                }
            }

            // load the json configuration of this field
            $table =  $fieldHelper->getFieldType($fieldKey);
            $fieldConfiguration = $this->storageRepository->loadField($table, $fieldKey);

            // if there is a restriction of cTypes specified
            if (is_array($fieldConfiguration["cTypes"])) {

                // prepare array of allowed cTypes, with cTypes as keys
                $cTypes = array_flip($fieldConfiguration["cTypes"]);

                // and check each item if it is allowed. if not, unset it
                foreach ($params["items"] as $itemKey => $item) {
                    if (!isset($cTypes[$item[1]])) {
                        unset($params["items"][$itemKey]);
                    }
                }
            }
        } else { // if it is not inline tt_content element
            // and if other itemsProcFunc from other extension was available (e.g. gridelements),
            // then call it now and let it render the items
            if (!empty($params["config"]["m_itemsProcFunc"])) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($params["config"]["m_itemsProcFunc"], $params, $this);
            }
        }
    }

    /**
     * Checks if a string begins with a certain substring
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    protected function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Checks if a string ends with a certain substring
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    protected function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}
