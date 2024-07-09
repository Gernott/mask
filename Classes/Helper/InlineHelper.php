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

use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Methods for working with inline fields (IRRE)
 */
class InlineHelper
{
    protected TableDefinitionCollection $tableDefinitionCollection;
    protected BackendLayoutRepository $backendLayoutRepository;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, BackendLayoutRepository $backendLayoutRepository)
    {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->backendLayoutRepository = $backendLayoutRepository;
    }

    /**
     * Adds FAL-Files to the data-array if available
     * @todo Remove this and use core FilesProcessor instead.
     * @todo This needs to be converted then into the TypoScriptCodeGenerator.
     * @todo Check if this would be breaking, but hopefully not.
     */
    public function addFilesToData(array &$data, string $table = 'tt_content'): void
    {
        $uid = $data['uid'];
        if ($data['_LOCALIZED_UID'] ?? false) {
            $uid = $data['_LOCALIZED_UID'];
        } elseif ($data['_PAGES_OVERLAY_UID'] ?? false) {
            $uid = $data['_PAGES_OVERLAY_UID'];
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
        $imageFields = [];
        if ($table === 'tt_content') {
            $imageFields = ['media', 'image', 'assets'];
        }
        if ($table === 'pages') {
            $imageFields = ['media'];
        }
        $contentFields = array_merge($imageFields, $tcaKeys);
        $contentFields = array_unique($contentFields);

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        foreach ($contentFields as $fieldKey) {
            if ($this->tableDefinitionCollection->getFieldType($fieldKey, $table)->isFileReference()) {
                $data[$fieldKey] = $fileRepository->findByRelation($table, $fieldKey, $uid);
            }
        }
    }

    /**
     * Adds inline fields to the data array if available.
     * This method will be called recursively for every nested inline field by
     * $this->fillInlineField() and $this->getInlineElements().
     */
    public function addIrreToData(array &$data, string $table = 'tt_content', string $cType = '', string $originalTable = 'tt_content'): void
    {
        if (!$this->tableDefinitionCollection->hasTable($table)) {
            return;
        }
        if ($table === 'tt_content') {
            if ($cType === '') {
                $cType = (string)($data['CType'] ?? '');
            }
            if ($cType === '') {
                return;
            }
            $element = $this->tableDefinitionCollection->loadElement($table, AffixUtility::removeCTypePrefix($cType));
            $elementFields = $element->elementDefinition->columns ?? [];
        } elseif ($table === 'pages') {
            if (empty($data['uid'])) {
                return;
            }
            $backendLayoutIdentifier = $this->backendLayoutRepository->findIdentifierByPid((int)$data['uid']);
            if ($backendLayoutIdentifier === null || $backendLayoutIdentifier === '') {
                return;
            }
            $maskPageTemplateKey = str_replace('pagets__', '', $backendLayoutIdentifier);
            $element = $this->tableDefinitionCollection->loadElement($table, $maskPageTemplateKey);
            $elementFields = $element->elementDefinition->columns ?? [];
        } else {
            if ($originalTable === '') {
                return;
            }
            // If it's neither a tt_content record nor a page record, it has to be a mask inline record.
            $element = $this->tableDefinitionCollection->loadElement($originalTable, AffixUtility::removeCTypePrefix($cType));
            $elementFields = $this->tableDefinitionCollection->getTable($table)->tca->getKeys();
        }

        if (!$element instanceof ElementTcaDefinition) {
            return;
        }

        // Fill data for all fields recursively.
        foreach ($elementFields as $field) {
            $elementKey = $element->elementDefinition->key;
            $fieldType = $this->tableDefinitionCollection->getFieldType($field, $table);

            if ($fieldType->equals(FieldType::PALETTE)) {
                foreach ($this->tableDefinitionCollection->loadInlineFields($field, $elementKey, $element->elementDefinition) as $paletteField) {
                    $fieldType = $this->tableDefinitionCollection->getFieldType($paletteField->fullKey, $table);
                    $this->fillInlineField($data, $fieldType, $paletteField->fullKey, $cType, $table, $originalTable);
                }
            } else {
                $this->fillInlineField($data, $fieldType, $field, $cType, $table, $originalTable);
            }
        }
    }

    protected function fillInlineField(array &$data, FieldType $fieldType, string $field, string $cType, string $table, string $originalTable): void
    {
        if (!$fieldType->isRelationField()) {
            return;
        }
        $tcaFieldConfig = $GLOBALS['TCA'][$table]['columns'][$field] ?? [];
        // if it is of type inline and has to be filled (IRRE, FAL)
        if ($fieldType->equals(FieldType::INLINE) && $this->tableDefinitionCollection->hasTable($field)) {
            $elements = $this->getInlineElements($data, $field, $cType, 'parentid', $table, null, $originalTable);
            $data[$field] = $elements;
            // or if it is of type Content (Nested Content) and has to be filled
        } elseif ($fieldType->equals(FieldType::CONTENT)) {
            $content = $this->getRelations((string)($data[$field] ?? ''), $tcaFieldConfig['config']['foreign_table'], '', (int)$data['uid'], $table, $tcaFieldConfig['config'] ?? []);
            foreach ($content as $key => $element) {
                if ($element) {
                    $this->addIrreToData($element, 'tt_content', $element['CType'], $originalTable);
                    $this->addFilesToData($element);
                    $content[$key] = $element;
                }
            }
            $data[$field] = $content;
        } elseif ($fieldType->equals(FieldType::CATEGORY)) {
            if ($tcaFieldConfig['config']['relationship'] === 'manyToMany') {
                $data[$field . '_items'] = $this->getRelations('', ($tcaFieldConfig['config']['foreign_table'] ?? ''), $tcaFieldConfig['config']['MM'] ?? '', (int)$data['uid'], $table, $tcaFieldConfig['config'] ?? []);
            } else {
                $data[$field . '_items'] = $this->getRelations((string)($data[$field] ?? ''), ($tcaFieldConfig['config']['foreign_table'] ?? ''), $tcaFieldConfig['config']['MM'] ?? '', (int)$data['uid'], $table, $tcaFieldConfig['config'] ?? []);
            }
        } elseif (($tcaFieldConfig['config']['foreign_table'] ?? '') !== '' && $fieldType->equals(FieldType::SELECT)) {
            $data[$field . '_items'] = $this->getRelations((string)($data[$field] ?? ''), $tcaFieldConfig['config']['foreign_table'], $tcaFieldConfig['config']['MM'] ?? '', (int)$data['uid'], $table, $tcaFieldConfig['config'] ?? []);
        } elseif (($tcaFieldConfig['config']['internal_type'] ?? '') !== 'folder' && ($tcaFieldConfig['config']['type'] ?? '') !== 'folder' && $fieldType->equals(FieldType::GROUP)) {
            $data[$field . '_items'] = $this->getRelations((string)($data[$field] ?? ''), $tcaFieldConfig['config']['allowed'], $tcaFieldConfig['config']['MM'] ?? '', (int)$data['uid'], $table, $tcaFieldConfig['config'] ?? []);
        } elseif (in_array(($tcaFieldConfig['config']['renderType'] ?? ''), ['selectCheckBox', 'selectSingleBox', 'selectMultipleSideBySide'], true) && $fieldType->equals(FieldType::SELECT)) {
            $data[$field . '_items'] = ($data[$field] ?? '') !== '' ? explode(',', $data[$field]) : [];
        }
    }

    /**
     * @param array<string, mixed> $tcaFieldConf
     *
     * Returns the selected relations of select or group element
     */
    protected function getRelations(string $uidList, string $allowed, string $mmTable, int $uid, string $table, array $tcaFieldConf = []): array
    {
        $pageRepository = $this->getPageRepository();
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        $relationHandler->start($uidList, $allowed, $mmTable, $uid, $table, $tcaFieldConf);
        foreach (array_keys($relationHandler->tableArray) as $table) {
            if (isset($GLOBALS['TCA'][$table])) {
                $autoHiddenSelection = -1;
                $ignoreWorkspaceFilter = ['pid' => true];
                $relationHandler->additionalWhere[$table] = $pageRepository->enableFields($table, $autoHiddenSelection, $ignoreWorkspaceFilter);
            }
        }
        $relationHandler->getFromDB();
        $relations = $relationHandler->getResolvedItemArray();
        $records = [];
        foreach ($relations as $relation) {
            $tableName = $relation['table'];
            $record = $relation['record'];
            $pageRepository->versionOL($tableName, $record);
            if (!is_array($record)) {
                continue;
            }
            $translatedRecord = $pageRepository->getLanguageOverlay($tableName, $record);
            if ($translatedRecord !== null) {
                $records[] = $translatedRecord;
            }
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
        ?string $childTable = null,
        string $originalTable = 'tt_content'
    ): array {
        // if the name of the child table is not explicitly given, take field key
        if (!$childTable) {
            $childTable = $name;
        }

        // by default, the uid of the parent is $data["uid"]
        $parentUid = $data['uid'];
        if (isset($data['_LOCALIZED_UID'])) {
            $parentUid = $data['_LOCALIZED_UID'];
        } elseif (isset($data['_PAGES_OVERLAY_UID'])) {
            $parentUid = $data['_PAGES_OVERLAY_UID'];
        }
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($childTable);

        $workspaceId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('workspace', 'id');
        $inWorkspacePreviewMode = $workspaceId > 0;
        $isFrontendRequest = ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();

        if ($isFrontendRequest && !empty($GLOBALS['TCA'][$childTable]['ctrl']['enablecolumns']['fe_group'])) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(FrontendGroupRestriction::class));
        }

        // Remove default restrictions for workspace preview in order to fetch the original record uids.
        if ($inWorkspacePreviewMode) {
            $queryBuilder->getRestrictions()->removeAll();
        } elseif ($isFrontendRequest === false) {
            // In backend context we want to display hidden records.
            $restrictions = $queryBuilder->getRestrictions();
            $restrictions->removeByType(HiddenRestriction::class);
        }

        if (BackendUtility::isTableWorkspaceEnabled($childTable)) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, $workspaceId));
        }

        $queryBuilder
            ->select('*')
            ->from($childTable)
            ->where($queryBuilder->expr()->eq($parentFieldName, $queryBuilder->createNamedParameter($parentUid, \PDO::PARAM_INT)))
            ->orderBy('sorting');

        if ($childTable !== 'tt_content') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('parenttable', $queryBuilder->createNamedParameter($parenttable)));
        }

        $statement = $queryBuilder->executeQuery();

        // and recursively add them to an array
        $elements = [];
        if ($isFrontendRequest) {
            $pageRepository = $this->getPageRepository();
            foreach ($statement->fetchAllAssociative() as $element) {
                if ($inWorkspacePreviewMode) {
                    $pageRepository->versionOL($childTable, $element);
                }
                if ($element !== false) {
                    $elements[$element['uid']] = $element;
                }
            }
        } else {
            foreach ($statement->fetchAllAssociative() as $element) {
                if ($inWorkspacePreviewMode) {
                    $element = BackendUtility::getRecordWSOL($childTable, $element['uid']);
                    // Ignore disabled elements in backend preview.
                    if ($element === null || ($element[$GLOBALS['TCA'][$childTable]['ctrl']['enablecolumns']['disabled']] ?? false)) {
                        continue;
                    }
                }
                $elements[$element['uid']] = $element;
            }
        }

        // Need to sort overlaid records again, because sorting might have changed.
        usort($elements, static function ($a, $b) {
            return $a['sorting'] <=> $b['sorting'];
        });

        foreach ($elements as $key => $element) {
            if ($element) {
                $childCType = $cType;
                if ($childTable === 'tt_content') {
                    $childCType = $element['CType'];
                }
                $this->addIrreToData($element, $childTable, $childCType, $originalTable);
                $this->addFilesToData($element, $childTable);
                $elements[$key] = $element;
            }
        }

        return $elements;
    }

    protected function getPageRepository(): PageRepository
    {
        $tsfe = $GLOBALS['TSFE'] ?? null;
        if ($tsfe instanceof TypoScriptFrontendController && $tsfe->sys_page !== '') {
            return $tsfe->sys_page;
        }
        return GeneralUtility::makeInstance(PageRepository::class);
    }
}
