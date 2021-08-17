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
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\DateUtility;
use MASK\Mask\Utility\TcaConverterUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class FieldsController
{
    protected $storageRepository;
    protected $tableDefinitionCollection;
    protected $iconFactory;
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
        $json['fields'] = $this->addFields($element['tca'] ?? [], $table, $elementKey);

        return new JsonResponse($json);
    }

    public function loadField(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $table = $params['type'];
        $key = $params['key'];
        $field = $this->tableDefinitionCollection->loadField($table, $key);
        $json['field'] = $this->addFields([$key => $field], $table)[0];
        $json['field']['label'] = $this->tableDefinitionCollection->findFirstNonEmptyLabel($table, $key);

        return new JsonResponse($json);
    }

    /**
     * This is the main function for adding fields ready to use in Vue JS.
     * It creates a nested structure out of the flat array input.
     * It takes care of translating labels, legacy conversions, timestamp conversions, ...
     */
    protected function addFields(array $fields, string $table, string $elementKey = '', $parent = null): array
    {
        $defaults = $this->configurationLoader->loadDefaults();
        $nestedFields = [];
        foreach ($fields as $key => $field) {
            $newField = [
                'fields' => [],
                'parent' => $parent ?? [],
                'newField' => false,
            ];

            if ($parent) {
                $newField['key'] = isset($field['coreField']) ? $field['key'] : $field['maskKey'];
            } else {
                $newField['key'] = $key;
            }

            if ($elementKey !== '') {
                $newField['label'] = $this->tableDefinitionCollection->getLabel($elementKey, $newField['key'], $table);
                $translatedLabel = $this->translateLabel($newField['label']);
                $newField['translatedLabel'] = $translatedLabel !== $newField['label'] ? $translatedLabel : '';
            }

            $fieldType = FieldType::cast($this->tableDefinitionCollection->getFormType($newField['key'], $elementKey, $table));

            // Convert old date format Y-m-d to d-m-Y
            $dbType = $field['config']['dbType'] ?? false;
            if ($dbType && in_array($dbType, ['date', 'datetime'], true)) {
                $lower = $field['config']['range']['lower'] ?? false;
                $upper = $field['config']['range']['upper'] ?? false;
                if ($lower && DateUtility::isOldDateFormat($lower)) {
                    $field['config']['range']['lower'] = DateUtility::convertOldToNewFormat($dbType, $lower);
                }
                if ($upper && DateUtility::isOldDateFormat($upper)) {
                    $field['config']['range']['upper'] = DateUtility::convertOldToNewFormat($dbType, $upper);
                }
            }

            $newField['isMaskField'] = AffixUtility::hasMaskPrefix($newField['key']);
            $newField['name'] = (string)$fieldType;
            $newField['icon'] = $this->iconFactory->getIcon('mask-fieldtype-' . $newField['name'])->getMarkup();
            $newField['description'] = $field['description'] ?? '';
            $newField['tca'] = [];

            if (!$newField['isMaskField']) {
                $nestedFields[] = $newField;
                continue;
            }

            if (!$fieldType->isGroupingField()) {
                $newField['sql'] = $this->tableDefinitionCollection->getTableDefiniton($table)->sql[$newField['key']][$table][$newField['key']];
                $newField['tca'] = TcaConverterUtility::convertTcaArrayToFlat($field['config'] ?? []);
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
                $newField['tca']['imageoverlayPalette'] = $field['imageoverlayPalette'] ?? 1;
                // Since mask v7.0.0 the path for allowedFileExtensions has changed to root level.
                $allowedFileExtensionsPath = 'config.filter.0.parameters.allowedFileExtensions';
                $newField['tca']['allowedFileExtensions'] = $field['allowedFileExtensions'] ?? $newField['tca'][$allowedFileExtensionsPath] ?? '';
                // Remove old path.
                if (isset($newField['tca'][$allowedFileExtensionsPath])) {
                    unset($newField['tca'][$allowedFileExtensionsPath]);
                }
            }

            if ($fieldType->equals(FieldType::CONTENT)) {
                $newField['tca']['cTypes'] = $field['cTypes'] ?? [];
            }

            // Set defaults for mask fields
            foreach ($defaults[(string)$fieldType]['tca_in'] ?? [] as $tcaKey => $defaultValue) {
                $newField['tca'][$tcaKey] = $newField['tca'][$tcaKey] ?? $defaultValue;
            }

            if ($fieldType->equals(FieldType::INLINE)) {
                $newField['tca']['ctrl.iconfile'] = $field['ctrl']['iconfile'] ?? $field['inlineIcon'] ?? '';
                $newField['tca']['ctrl.label'] = $field['ctrl']['label'] ?? $field['inlineLabel'] ?? '';
            }

            $newField['tca'] = $this->cleanUpConfig($newField['tca'], $fieldType);

            if ($fieldType->isParentField()) {
                $inlineTable = $fieldType->equals(FieldType::INLINE) ? $newField['key'] : $table;
                $newField['fields'] = $this->addFields(
                    $this->tableDefinitionCollection->loadInlineFields($newField['key'], $elementKey),
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

    /**
     * Translates label for the given key.
     * A key not beginning with 'LLL' is returned as is.
     */
    protected function translateLabel(string $key): string
    {
        if (empty($key) || strpos($key, 'LLL') !== 0) {
            return $key;
        }

        $result = LocalizationUtility::translate($key);
        return empty($result) ? $key : $result;
    }
}
