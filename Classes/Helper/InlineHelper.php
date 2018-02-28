<?php

namespace MASK\Mask\Helper;

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
 * Methods for working with inline fields (IRRE)
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class InlineHelper
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * @param \MASK\Mask\Domain\Repository\StorageRepository $storageRepository
     */
    public function __construct(\MASK\Mask\Domain\Repository\StorageRepository $storageRepository = null)
    {
        if (!$storageRepository) {
            $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        } else {
            $this->storageRepository = $storageRepository;
        }
    }

    /**
     * Adds FAL-Files to the data-array if available
     *
     * @param array $data
     * @param array $table
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function addFilesToData(&$data, $table = "tt_content")
    {
        if ($data["_LOCALIZED_UID"]) {
            $uid = $data["_LOCALIZED_UID"];
        } else {
            $uid = $data["uid"];
        }

        // using is_numeric in favor to is_int
        // due to some rare cases where uids are provided as strings
        if(!is_numeric($uid)) {
            return;
        }

        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        $storage = $this->storageRepository->load();
        /* @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
        $fileRepository = $objectManager->get("TYPO3\CMS\Core\Resource\FileRepository");
        $contentFields = array("media", "image", "assets");
        if ($storage[$table]["tca"]) {
            foreach ($storage[$table]["tca"] as $fieldKey => $field) {
                $contentFields[] = $fieldKey;
            }
        }
        if ($contentFields) {
            foreach ($contentFields as $fieldKey) {
                if ($fieldHelper->getFormType($fieldKey, "", $table) == "File") {
                    $data[$fieldKey] = $fileRepository->findByRelation($table, $fieldKey, $uid);
                }
            }
        }
    }

    /**
     * Adds FAL-Files to the data-array if available
     *
     * @param array $data
     * @param array $table
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function addIrreToData(&$data, $table = "tt_content", $cType = "")
    {
        if ($cType == "") {
            $cType = $data["CType"];
        }
        $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        $storage = $this->storageRepository->load();
        $contentFields = $storage[$table]["tca"];

        if ($contentFields) {
            foreach ($contentFields as $fieldname => $field) {
                if ($fieldHelper->getFormType($field["key"], $cType, $table) == "Inline") {
                    $elements = $this->getInlineElements($data, $fieldname, $cType, "parentid", $table);
                    $data[$fieldname] = $elements;
                } elseif ($fieldHelper->getFormType($field["key"], $cType, $table) == "Content") {
                    $elements = $this->getInlineElements($data, $fieldname, $cType, $fieldname . "_parent", "tt_content", "tt_content");
                    $data[$fieldname] = $elements;
                }
            }
        }
    }

    /**
     * Returns Inline-Elements of Data-Object
     *
     * @param object $data the parent object
     * @param string $name The name of the irre attribut
     * @param string $cType The name of the irre attribut
     * @param string $parentid The name of the irre parentid
     * @param string $parenttable The table where the parent element is stored
     * @param string $childTable name of childtable
     * @return array all irre elements of this attribut
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getInlineElements($data, $name, $cType, $parentid = "parentid", $parenttable = "tt_content", $childTable = null)
    {
        // if the name of the child table is not explicitely given, take field key
        if (!$childTable) {
            $childTable = $name;
        }

        // If this method is called in backend, there is no $GLOBALS['TSFE']
        if (isset($GLOBALS['TSFE']->sys_language_uid)) {
            $sysLangUid = $GLOBALS['TSFE']->sys_language_uid;
            $enableFields = $GLOBALS['TSFE']->cObj->enableFields($childTable);
        } else {
            $sysLangUid = 0;
            $enableFields = " AND " . $childTable . ".deleted = 0";
        }

        // by default, the uid of the parent is $data["uid"]
        $parentUid = $data["uid"];

        /*
         * but if the parent table is the pages, and it isn't the default language
         * then pages_language_overlay becomes the parenttable
         * and $data["_PAGES_OVERLAY_UID"] becomes the id of the parent
         */
        if ($parenttable == "pages" && $GLOBALS['TSFE']->sys_language_uid != 0) {
            $parenttable = "pages_language_overlay";
            $parentUid = $data["_PAGES_OVERLAY_UID"];

            /**
             * else if the parenttable is tt_content and we are looking for translated
             * elements and the field _LOCALIZED_UID is available, then use this field
             * Otherwise we have problems with gridelements and translation
             */
        } else if ($parenttable == "tt_content" && $GLOBALS['TSFE']->sys_language_uid != 0 && $data["_LOCALIZED_UID"] != "") {
            $parentUid = $data["_LOCALIZED_UID"];
        }

        // fetching the inline elements
        if ($childTable == "tt_content") {
            $sql = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
                "*", $childTable, $parentid . " = '" . $parentUid .
                "' AND sys_language_uid IN (-1," . $sysLangUid . ")"
                . ' AND ('
                . $childTable . '.t3ver_wsid=0 OR '
                . $childTable . '.t3ver_wsid=' . (int)$GLOBALS['BE_USER']->workspace
                . ' AND ' . $childTable . '.pid<>-1'
                . ')'
                . $enableFields, "", "sorting"
            );
        } else {
            $sql = $GLOBALS["TYPO3_DB"]->exec_SELECTquery(
                "*", $childTable, $parentid . " = '" . $parentUid .
                "' AND parenttable = '" . $parenttable .
                "' AND sys_language_uid IN (-1," . $sysLangUid . ")"
                . ' AND ('
                . $childTable . '.t3ver_wsid=0 OR '
                . $childTable . '.t3ver_wsid=' . (int)$GLOBALS['BE_USER']->workspace
                . ' AND ' . $childTable . '.pid<>-1'
                . ')'
                . $enableFields, "", "sorting"
            );
        }

        // and recursively add them to an array
        $elements = array();
        while ($element = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($sql)) {
            if (TYPO3_MODE == 'FE') {
                $GLOBALS['TSFE']->sys_page->versionOL($childTable, $element);
            } else {
                $element = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordWSOL($childTable, $element['uid']);
            }
            if ($element && empty($elements[$element['uid']])) {
                $this->addIrreToData($element, $name, $cType);
                $this->addFilesToData($element, $name);
                $elements[$element['uid']] = $element;
            }
        }

        return $elements;
    }
}
