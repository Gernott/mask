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

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Methods for working with inline fields (IRRE)
 */
class InlineHelper
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * BackendLayoutRepository
     *
     * @var BackendLayoutRepository
     */
    protected $backendLayoutRepository;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, BackendLayoutRepository $backendLayoutRepository)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->backendLayoutRepository = $backendLayoutRepository;
    }

    /**
     * Adds FAL-Files to the data-array if available
     */
    public function addFilesToData(array &$data, string $table = 'tt_content'): void
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

        $tcaKeys = [];
        if ($this->tableDefinitionCollection->hasTable($table)) {
            $tcaKeys = $this->tableDefinitionCollection->getTable($table)->tca->getKeys();
        }
        $contentFields = array_merge(['media', 'image', 'assets'], $tcaKeys);

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        foreach ($contentFields as $fieldKey) {
            if ($this->tableDefinitionCollection->getFieldType($fieldKey, $table)->equals(FieldType::FILE)) {
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
    public function addIrreToData(array &$data, string $table = 'tt_content', string $cType = ''): void
    {
        if ($cType === '') {
            $cType = $data['CType'] ?? '';
        }

        $elementFields = [];

        // if the table is tt_content, load the element and all its columns
        $tableExists = $this->tableDefinitionCollection->hasTable($table);
        if ($table === 'tt_content') {
            $element = $this->tableDefinitionCollection->loadElement($table, AffixUtility::removeCTypePrefix($cType));
            $elementFields = $element->elementDefinition->columns ?? [];
        } elseif ($table === 'pages') {
            // if the table is pages, then load the pid
            if (isset($data['uid'])) {
                // find the backendlayout by the pid
                $backendLayoutIdentifier = $this->backendLayoutRepository->findIdentifierByPid((int)$data['uid']);

                // if a backendlayout was found, then load its elements
                if ($backendLayoutIdentifier) {
                    $element = $this->tableDefinitionCollection->loadElement(
                        $table,
                        str_replace('pagets__', '', $backendLayoutIdentifier)
                    );
                    $elementFields = $element->elementDefinition->columns ?? [];
                // if no backendlayout was found, just load all fields, if there are fields
                } elseif ($tableExists) {
                    $elementFields = $this->tableDefinitionCollection->getTable($table)->tca->getKeys();
                }
            }
        } elseif ($tableExists) {
            // otherwise check if its a table at all, if yes load all fields
            $elementFields = $this->tableDefinitionCollection->getTable($table)->tca->getKeys();
        }

        // Check type of all element columns
        foreach ($elementFields ?? [] as $field) {
            $elementKey = $element->elementDefinition->key ?? '';
            $fieldType = $this->tableDefinitionCollection->getFieldType($field, $table);

            if ($fieldType->equals(FieldType::PALETTE)) {
                foreach ($this->tableDefinitionCollection->loadInlineFields($field, $elementKey) as $paletteField) {
                    $fieldType = $this->tableDefinitionCollection->getFieldType($paletteField->fullKey, $table);
                    $this->fillInlineField($data, $fieldType, $paletteField->fullKey, $cType, $table);
                }
            } else {
                $this->fillInlineField($data, $fieldType, $field, $cType, $table);
            }
        }
    }

    protected function fillInlineField(array &$data, FieldType $fieldType, string $field, string $cType, string $table): void
    {
        // if it is of type inline and has to be filled (IRRE, FAL)
        if ($fieldType->equals(FieldType::INLINE) && $this->tableDefinitionCollection->hasTable($field)) {
            $elements = $this->getInlineElements($data, $field, $cType, 'parentid', $table);
            $data[$field] = $elements;
        // or if it is of type Content (Nested Content) and has to be filled
        } elseif ($fieldType->equals(FieldType::CONTENT)) {
            $elements = $this->getInlineElements(
                $data,
                $field,
                $cType,
                AffixUtility::addMaskParentSuffix($field),
                'tt_content',
                'tt_content'
            );
            $data[$field] = $elements;
        } elseif (($GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table'] ?? '') !== '' && $fieldType->equals(FieldType::SELECT)) {
            $data[$field . '_items'] = $this->getRelations($data[$field], $GLOBALS['TCA'][$table]['columns'][$field]['config']['foreign_table']);
        } elseif (($GLOBALS['TCA'][$table]['columns'][$field]['config']['internal_type'] === 'db') && $fieldType->equals(FieldType::GROUP)) {
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
        array $data,
        string $name,
        string $cType,
        string $parentFieldName = 'parentid',
        string $parenttable = 'tt_content',
        ?string $childTable = null
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
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(FrontendGroupRestriction::class));
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
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
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
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
        usort($elements, static function ($a, $b) {
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
