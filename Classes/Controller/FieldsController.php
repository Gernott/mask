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

namespace MASK\Mask\Controller;

use MASK\Mask\ConfigurationLoader\ConfigurationLoaderInterface;
use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\DateUtility;
use MASK\Mask\Utility\TcaConverter;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Class FieldsController
 * @internal
 */
class FieldsController
{
    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var ConfigurationLoaderInterface
     */
    protected $configurationLoader;

    public function __construct(
        TableDefinitionCollection $tableDefinitionCollection,
        IconFactory $iconFactory,
        ConfigurationLoaderInterface $configurationLoader
    ) {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->iconFactory = $iconFactory;
        $this->configurationLoader = $configurationLoader;
    }

    public function loadElement(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $table = $params['type'];
        $elementKey = $params['key'];

        $element = $this->tableDefinitionCollection->loadElement($table, $elementKey);
        $json['fields'] = [];
        if ($element instanceof ElementTcaDefinition) {
            $json['fields'] = $this->addFields($element->getRootTcaFields()->toArray(), $table, $elementKey);
        }

        return new JsonResponse($json);
    }

    public function loadField(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $table = $params['type'];
        $key = $params['key'];
        $fieldDefinition = $this->tableDefinitionCollection->loadField($table, $key);
        $field = [];
        if ($fieldDefinition) {
            $field = $fieldDefinition->toArray();
        }
        $json['field'] = $this->addFields([$key => $field], $table)[0];
        $json['field']['label'] = $this->tableDefinitionCollection->findFirstNonEmptyLabel($table, $key);
        $json['field']['description'] = $this->tableDefinitionCollection->findFirstNonEmptyDescription($table, $key);

        return new JsonResponse($json);
    }

    /**
     * This is the main function for adding fields ready to use in Vue JS.
     * It creates a nested structure out of the flat array input.
     * It takes care of translating labels, legacy conversions, timestamp conversions, ...
     *
     * @param array $fields
     * @param string $table
     * @param string $elementKey
     * @param array|null $parent
     * @return array
     */
    protected function addFields(array $fields, string $table, string $elementKey = '', ?array $parent = null): array
    {
        $defaults = $this->configurationLoader->loadDefaults();
        $nestedFields = [];
        foreach ($fields as $field) {
            $newField = [
                'fields' => [],
                'parent' => $parent ?? [],
                'newField' => false,
            ];

            $newField['key'] = $field['fullKey'];

            if ($elementKey !== '') {
                $newField['label'] = $this->tableDefinitionCollection->getLabel($elementKey, $newField['key'], $table);
                $translatedLabel = $this->getLanguageService()->sL($newField['label']);
                $newField['translatedLabel'] = $translatedLabel !== $newField['label'] ? $translatedLabel : '';

                $newField['description'] = $this->tableDefinitionCollection->getDescription($elementKey, $newField['key'], $table);
            }

            $fieldType = $this->tableDefinitionCollection->getFieldType($newField['key'], $table, $elementKey);

            // Convert old date format Y-m-d to d-m-Y
            $dbType = $field['config']['dbType'] ?? false;
            if (in_array($dbType, ['date', 'datetime'], true)) {
                $lower = $field['config']['range']['lower'] ?? false;
                $upper = $field['config']['range']['upper'] ?? false;
                if ($lower && DateUtility::isOldDateFormat($lower)) {
                    $field['config']['range']['lower'] = DateUtility::convertOldToNewFormat($dbType, $lower);
                }
                if ($upper && DateUtility::isOldDateFormat($upper)) {
                    $field['config']['range']['upper'] = DateUtility::convertOldToNewFormat($dbType, $upper);
                }
            }

            $newField['name'] = (string)$fieldType;
            $newField['icon'] = $this->iconFactory->getIcon('mask-fieldtype-' . $newField['name'])->getMarkup();
            $newField['tca'] = [];

            if ($field['coreField'] ?? false) {
                $nestedFields[] = $newField;
                continue;
            }

            if (!$fieldType->isGroupingField()) {
                $tableDefinition = $this->tableDefinitionCollection->getTable($table);
                if ($tableDefinition->sql->hasColumn($newField['key'])) {
                    $newField['sql'] = $this->tableDefinitionCollection->getTable($table)->sql->getColumn($newField['key'])->sqlDefinition;
                }
                $newField['tca'] = TcaConverter::convertTcaArrayToFlat($field['config'] ?? [], ['config']);
                $newField['tca']['l10n_mode'] = $field['l10n_mode'] ?? '';
            }

            if ($fieldType->equals(FieldType::TIMESTAMP)) {
                $default = $newField['tca']['config.default'] ?? false;
                if ($default) {
                    $newField['tca']['config.default'] = DateUtility::convertTimestampToDate($newField['tca']['config.eval'], $default);
                }
                $lower = $newField['tca']['config.range.lower'] ?? false;
                if ($lower) {
                    $newField['tca']['config.range.lower'] = DateUtility::convertTimestampToDate($newField['tca']['config.eval'], $lower);
                }
                $upper = $newField['tca']['config.range.upper'] ?? false;
                if ($upper) {
                    $newField['tca']['config.range.upper'] = DateUtility::convertTimestampToDate($newField['tca']['config.eval'], $upper);
                }
            }

            if ($fieldType->equals(FieldType::FILE)) {
                $newField['tca']['imageoverlayPalette'] = $field['imageoverlayPalette'];
            }

            if ($fieldType->isFileReference()) {
                $newField['tca']['allowedFileExtensions'] = $field['allowedFileExtensions'] ?? '';
            }

            if ($fieldType->equals(FieldType::CONTENT)) {
                $newField['tca']['cTypes'] = $field['cTypes'] ?? [];
            }

            if ($fieldType->equals(FieldType::MEDIA)) {
                $newField['tca']['onlineMedia'] = $field['onlineMedia'] ?? [];
            }

            // Set defaults for mask fields
            foreach ($defaults[(string)$fieldType]['tca_in'] ?? [] as $tcaKey => $defaultValue) {
                $newField['tca'][$tcaKey] = $newField['tca'][$tcaKey] ?? $defaultValue;
            }

            if ($fieldType->equals(FieldType::INLINE)) {
                $newField['tca']['ctrl.iconfile'] = $field['ctrl']['iconfile'] ?? '';
                $newField['tca']['ctrl.label'] = $field['ctrl']['label'] ?? '';
            }

            $newField['tca'] = $this->cleanUpConfig($newField['tca'], $fieldType);

            if ($fieldType->isParentField()) {
                $inlineFields = $this->tableDefinitionCollection->loadInlineFields($newField['key'], $elementKey);
                $inlineTable = $fieldType->equals(FieldType::INLINE) ? $newField['key'] : $table;
                $newField['fields'] = $this->addFields(
                    $inlineFields->toArray(),
                    $inlineTable,
                    $elementKey,
                    $newField
                );
            }

            $nestedFields[] = $newField;
        }
        return $nestedFields;
    }

    /**
     * This method removes all tca options defined which aren't available in mask.
     */
    protected function cleanUpConfig(array $config, FieldType $fieldType): array
    {
        $tabConfig = $this->configurationLoader->loadTab((string)$fieldType);
        $tcaOptions = [];
        foreach ($tabConfig as $options) {
            foreach ($options as $row) {
                $tcaOptions[] = array_keys($row);
            }
        }
        $tcaOptions = array_merge([], ...$tcaOptions);

        return array_filter($config, static function ($key) use ($tcaOptions) {
            return in_array($key, $tcaOptions, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
