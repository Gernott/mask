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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;

/**
 * Methods for working with inline fields (IRRE)
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class InlineHelper
{

    /**
     * @var TYPO3\CMS\Extbase\Object\ObjectManager
     * @Inject()
     */
    protected $objectManager;

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * BackendLayoutRepository
     *
     * @var \MASK\Mask\Domain\Repository\BackendLayoutRepository
     * @Inject()
     */
    protected $backendLayoutRepository;


    /**
     * @param \MASK\Mask\Domain\Repository\StorageRepository $storageRepository
     */
    public function __construct(\MASK\Mask\Domain\Repository\StorageRepository $storageRepository = null)
    {
        if (!$storageRepository) {
            $this->storageRepository = GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        } else {
            $this->storageRepository = $storageRepository;
        }
    }

    /**
     * Adds FAL-Files to the data-array if available
     *
     * @param array $data
     * @param string $table
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
        if (!is_numeric($uid)) {
            return;
        }
        $fieldHelper = GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        if (!$this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }

        $storage = $this->storageRepository->load();
        /* @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
        $fileRepository = $this->objectManager->get("TYPO3\CMS\Core\Resource\FileRepository");
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
     * @param string $table
     * @param string $cType
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function addIrreToData(&$data, $table = "tt_content", $cType = "")
    {

        if ($cType == "") {
            $cType = $data["CType"];
        }

        $fieldHelper = GeneralUtility::makeInstance(FieldHelper::class);
        $storage = $this->storageRepository->load();
        $elementFields = [];

        // if the table is tt_content, load the element and all its columns
        if ($table == "tt_content") {
            $element = $this->storageRepository->loadElement($table, str_replace("mask_", "", $cType));
            $elementFields = $element["columns"];
        } elseif ($table == "pages") {
            // if the table is pages, then load the pid
            if (isset($data['uid'])) {

                // find the backendlayout by the pid
                $backendLayoutIdentifier = $this->backendLayoutRepository->findIdentifierByPid($data['uid']);

                // if a backendlayout was found, then load its elements
                if ($backendLayoutIdentifier) {
                    $element = $this->storageRepository->loadElement(
                        $table,
                        str_replace('pagets__', '', $backendLayoutIdentifier)
                    );
                    $elementFields = $element["columns"];
                } else {

                    // if no backendlayout was found, just load all fields, if there are fields
                    if (isset($storage[$table]['tca'])) {
                        $elementFields = array_keys($storage[$table]['tca']);
                    }

                }
            }
        } elseif (isset($storage[$table])) {
            // otherwise check if its a table at all, if yes load all fields
            $elementFields = array_keys($storage[$table]['tca']);
        }

        // if the element has columns
        if ($elementFields) {

            // check foreach column
            foreach ($elementFields as $field) {

                $fieldKeyPrefix = $field;
                $fieldKey = str_replace("tx_mask_", "", $field);
                $type = $fieldHelper->getFormType($fieldKey, $cType, $table);

                // if it is of type inline and has to be filled (IRRE, FAL)
                if ($type == "Inline") {
                    $elements = $this->getInlineElements($data, $fieldKeyPrefix, $cType, "parentid", $table);
                    $data[$fieldKeyPrefix] = $elements;
                    // or if it is of type Content (Nested Content) and has to be filled
                } elseif ($type == "Content") {
                    $elements = $this->getInlineElements($data, $fieldKeyPrefix, $cType, $fieldKeyPrefix . "_parent",
                        "tt_content", "tt_content");
                    $data[$fieldKeyPrefix] = $elements;
                }
            }
        }
    }

    /**
     * Returns Inline-Elements of Data-Object
     *
     * @param array $data the parent object
     * @param string $name The name of the irre attribut
     * @param string $cType The name of the irre attribut
     * @param string $parentFieldName The name of the irre parentid
     * @param string $parenttable The table where the parent element is stored
     * @param string $childTable name of childtable
     * @return array all irre elements of this attribut
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getInlineElements(
        $data,
        $name,
        $cType,
        $parentFieldName = "parentid",
        $parenttable = "tt_content",
        $childTable = null
    ) {
        // if the name of the child table is not explicitely given, take field key
        if (!$childTable) {
            $childTable = $name;
        }

        // If this method is called in backend, there is no $GLOBALS['TSFE']
        if (TYPO3_MODE == 'FE' && isset($GLOBALS['TSFE']->sys_language_uid)) {
            $sysLangUid = $GLOBALS['TSFE']->sys_language_uid;
            $enableFields = $GLOBALS['TSFE']->cObj->enableFields($childTable);
        } else {
            $sysLangUid = $data['sys_language_uid'];
            $enableFields = " AND " . $childTable . ".deleted = 0";
        }

        // by default, the uid of the parent is $data["uid"]
        $parentUid = $data["uid"];

        if ($GLOBALS['TSFE']->sys_language_uid != 0 && $data["_LOCALIZED_UID"] != "") {
            $parentUid = $data["_LOCALIZED_UID"];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($childTable);
        $queryBuilder
            ->select('*')
            ->from($childTable)
            ->where($queryBuilder->expr()->eq($parentFieldName, $parentUid))
            ->orderBy('sorting');

        if ($childTable !== 'tt_content') {
            $queryBuilder->andWhere('parenttable LIKE :parenttable');
            $queryBuilder->setParameter('parenttable', $parenttable);
        }

        $rows = $queryBuilder->execute()->fetchAll();

        // and recursively add them to an array
        $elements = array();
        foreach ($rows as $element) {
            if (TYPO3_MODE == 'FE') {
                $GLOBALS['TSFE']->sys_page->versionOL($childTable, $element);
            } else {
                $element = BackendUtility::getRecordWSOL($childTable, $element['uid']);
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
