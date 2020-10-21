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

namespace MASK\Mask\Helper;

use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Methods for working with inline fields (IRRE)
 */
class InlineHelper
{
    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * BackendLayoutRepository
     *
     * @var BackendLayoutRepository
     */
    protected $backendLayoutRepository;

    public function __construct(StorageRepository $storageRepository, BackendLayoutRepository $backendLayoutRepository)
    {
        $this->storageRepository = $storageRepository;
        $this->backendLayoutRepository = $backendLayoutRepository;
    }

    /**
     * Adds FAL-Files to the data-array if available
     *
     * @param array $data
     * @param string $table
     */
    public function addFilesToData(&$data, $table = 'tt_content'): void
    {
        if ($data['_LOCALIZED_UID'] ?? false) {
            $uid = $data['_LOCALIZED_UID'];
        } else {
            $uid = $data['uid'];
        }

        // using is_numeric in favor to is_int
        // due to some rare cases where uids are provided as strings
        if (!is_numeric($uid)) {
            return;
        }

        // Cast to int for findByRelation call
        $uid = (int)$uid;

        $storage = $this->storageRepository->load();
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        $contentFields = array_merge(
            ['media', 'image', 'assets'],
            array_keys($storage[$table]['tca'] ?? [])
        );

        foreach ($contentFields as $fieldKey) {
            if ($this->storageRepository->getFormType($fieldKey, '', $table) == FieldType::FILE) {
                $data[$fieldKey] = $fileRepository->findByRelation($table, $fieldKey, $uid);
            }
        }
    }

    /**
     * Adds FAL-Files to the data-array if available
     *
     * @param array $data
     * @param string $table
     * @param string $cType
     * @throws \Exception
     */
    public function addIrreToData(&$data, $table = 'tt_content', $cType = ''): void
    {
        if ($cType === '') {
            $cType = $data['CType'];
        }

        $storage = $this->storageRepository->load();
        $elementFields = [];

        // if the table is tt_content, load the element and all its columns
        if ($table === 'tt_content') {
            $element = $this->storageRepository->loadElement($table, MaskUtility::removeCtypePrefix($cType));
            $elementFields = $element['columns'];
        } elseif ($table === 'pages') {
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
                    $elementFields = $element['columns'];
                // if no backendlayout was found, just load all fields, if there are fields
                } elseif (isset($storage[$table]['tca'])) {
                    $elementFields = array_keys($storage[$table]['tca']);
                }
            }
        } elseif (isset($storage[$table])) {
            // otherwise check if its a table at all, if yes load all fields
            $elementFields = array_keys($storage[$table]['tca']);
        }

        // Check type of all element columns
        foreach ($elementFields ?? [] as $field) {
            $fieldKey = str_replace('tx_mask_', '', $field);
            $type = $this->storageRepository->getFormType($fieldKey, ($element['key'] ?? ''), $table);

            if ($type == FieldType::PALETTE) {
                $paletteFields = $this->storageRepository->loadInlineFields($field, ($element['key'] ?? ''));
                foreach ($paletteFields as $paletteField) {
                    $type = $this->storageRepository->getFormType($paletteField['key'], ($element['key'] ?? ''), $table);
                    $this->fillInlineField($data, $storage, $type, $paletteField['maskKey'], $cType, $table);
                }
            } else {
                $this->fillInlineField($data, $storage, $type, $field, $cType, $table);
            }
        }
    }

    protected function fillInlineField(&$data, $storage, $type, $field, $cType, $table)
    {
        // if it is of type inline and has to be filled (IRRE, FAL)
        if ($type == FieldType::INLINE && array_key_exists($field, $storage)) {
            $elements = $this->getInlineElements($data, $field, $cType, 'parentid', $table);
            $data[$field] = $elements;
            // or if it is of type Content (Nested Content) and has to be filled
        } elseif ($type == FieldType::CONTENT) {
            $elements = $this->getInlineElements(
                $data,
                $field,
                $cType,
                $field . '_parent',
                'tt_content',
                'tt_content'
            );
            $data[$field] = $elements;
        } elseif ($type === FieldType::SELECT && ($GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table'] ?? '') !== '') {
            $data[$field . '_items'] = $this->getRelations($data[$field], $GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table']);
        } elseif ($type === FieldType::GROUP && ($GLOBALS['TCA'][$table]['columns'][$field]['config']['internal_type'] === 'db')) {
            $data[$field . '_items'] = $this->getRelations($data[$field], $GLOBALS['TCA'][$table]['columns'][$field]['config']['allowed']);
        }
    }

    /**
     * Returns the selected relations of select or group element
     *
     * @param $uidList
     * @param $allowed
     * @return array
     */
    protected function getRelations($uidList, $allowed)
    {
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        $relationHandler->start(
            $uidList,
            $allowed
        );
        $relationHandler->getFromDB();
        $relations = $relationHandler->getResolvedItemArray();
        $records = [];
        foreach ($relations as $relation) {
            $tableName = $relation['table'];
            $uid = $relation['uid'];
            $records[] = BackendUtility::getRecordWSOL($tableName, $uid);
        }
        return $records;
    }

    /**
     * Returns Inline-Elements of Data-Object
     *
     * @param array $data the parent object
     * @param string $name The name of the irre attribut
     * @param string $cType The name of the irre attribut
     * @param string $parentFieldName The name of the irre parentid
     * @param string $parenttable The table where the parent element is stored
     * @param string|null $childTable name of childtable
     * @return array all irre elements of this attribut
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function getInlineElements(
        $data,
        $name,
        $cType,
        $parentFieldName = 'parentid',
        $parenttable = 'tt_content',
        $childTable = null
    ): array {
        // if the name of the child table is not explicitely given, take field key
        if (!$childTable) {
            $childTable = $name;
        }

        // by default, the uid of the parent is $data["uid"]
        $parentUid = $data['uid'];
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        if ($languageAspect->getId() !== 0) {
            if (isset($data['_LOCALIZED_UID'])) {
                $parentUid = $data['_LOCALIZED_UID'];
            } elseif (isset($data['_PAGES_OVERLAY_UID'])) {
                $parentUid = $data['_PAGES_OVERLAY_UID'];
            }
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($childTable);
        if (TYPO3_MODE === 'BE') {
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        }
        if (BackendUtility::isTableWorkspaceEnabled($childTable)) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, (int)$GLOBALS['BE_USER']->workspace));
        }
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
        $elements = [];
        if (TYPO3_MODE === 'FE') {
            foreach ($rows as $element) {
                $GLOBALS['TSFE']->sys_page->versionOL($childTable, $element);
                $elements[$element['uid']] = $element;
            }
        } else {
            foreach ($rows as $element) {
                $elements[$element['uid']] = BackendUtility::getRecordWSOL($childTable, $element['uid']);
            }
        }

        // Need to sort overlaid records again, because sorting might have changed.
        usort($elements, function ($a, $b) {
            return $a['sorting'] > $b['sorting'];
        });

        foreach ($elements as $key => $element) {
            if ($element) {
                $this->addIrreToData($element, $name, $cType);
                $this->addFilesToData($element, $name);
                $elements[$key] = $element;
            }
        }

        return $elements;
    }
}
