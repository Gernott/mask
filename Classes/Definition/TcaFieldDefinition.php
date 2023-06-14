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

namespace MASK\Mask\Definition;

use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\FieldTypeUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class TcaFieldDefinition
{
    private const ALLOWED_EMPTY_VALUES_BY_TYPE = [
        FieldType::SELECT => [
            'config.items',
        ],
        FieldType::CHECK => [
            'config.items',
        ],
        // This is important for upgrades, to keep something in the "config" array.
        // In TYPO3 v12 type="inline" with foreign_table="sys_file_reference" is now type="file".
        // Because of this, Mask does not have any standard TCA for this field anymore.
        // The removeBlankOptions method would thus remove everything and the next save in the backend builder would
        // result in the file field to be treated as a "coreField".
        FieldType::FILE => [
            'config.minitems',
        ],
        FieldType::MEDIA => [
            'config.minitems',
        ],
    ];

    private const STOP_RECURSIVE_VALUES_BY_TYPE = [
        FieldType::SELECT => [
            'config.itemGroups',
        ],
        FieldType::SLUG => [
            'config.generatorOptions.replacements',
        ],
    ];

    public const NON_OVERRIDEABLE_OPTIONS = [
        'type',
        'relationship',
        'dbType',
        'nullable',
        'MM',
        'MM_opposite_field',
        'MM_hasUidField',
        'MM_oppositeUsage',
        'allowed',
        'foreign_table',
        'foreign_field',
        'foreign_table_field',
        'foreign_match_fields',
    ];

    private ?FieldType $type = null;
    public string $key = '';
    public string $fullKey = '';
    public string $label = '';
    public string $description = '';
    public bool $isCoreField = false;
    public bool $inPalette = false;
    public string $inlineParent = '';
    public bool $imageoverlayPalette = false;
    public string $allowedFileExtensions = '';
    public string $inlineIcon = '';
    public string $inlineLabel = '';
    public int $order = 0;

    /**
     * @var array<string, string>
     */
    public array $inlineParentByElement = [];

    /**
     * @var array<string, FieldType>
     */
    public array $bodytextTypeByElement = [];

    /**
     * @var array<string, string>
     */
    public array $labelByElement = [];

    /**
     * @var array<string, string>
     */
    public array $descriptionByElement = [];

    /**
     * @var array<string, int>
     */
    public array $orderByElement = [];

    /**
     * @var string[]
     */
    public array $cTypes = [];

    /**
     * @var string[]
     */
    public array $onlineMedia = [];

    /**
     * @var array<string, mixed>
     */
    public array $realTca = [];

    /**
     * Array of direct child tca field definitions.
     * Not filled by default.
     *
     * @var TcaFieldDefinition[]
     */
    public array $inlineFields = [];

    public static function createFromFieldArray(array $definition): TcaFieldDefinition
    {
        $key = ($definition['key'] ?? '');
        if ($key === '') {
            throw new \InvalidArgumentException('The key for a FieldDefinition must not be empty', 1629277138);
        }

        $tcaFieldDefinition = new self();

        // Prior to v6, core fields were determined by empty config array.
        // Note: In older Mask versions, file fields had no config section. Only options set to "file".
        // These should not be added as core fields.
        $tcaFieldDefinition->isCoreField = isset($definition['coreField']) || (!isset($definition['config']) && !isset($definition['options']));

        $tcaFieldDefinition->key = $key;
        // Set full key with mask prefix if not core field
        $tcaFieldDefinition->fullKey = $definition['fullKey'] ?? '';
        if ($tcaFieldDefinition->fullKey === '') {
            if ($tcaFieldDefinition->isCoreField) {
                $tcaFieldDefinition->fullKey = $tcaFieldDefinition->key;
            } else {
                $tcaFieldDefinition->fullKey = AffixUtility::addMaskPrefix($tcaFieldDefinition->key);
            }
        }

        $tcaFieldDefinition->inPalette = (bool)($definition['inPalette'] ?? false);
        $tcaFieldDefinition->cTypes = (array)($definition['cTypes'] ?? []);
        $tcaFieldDefinition->onlineMedia = (array)($definition['onlineMedia'] ?? []);

        // Type resolving.
        // "options" was used for identifying file fields prior to v6.
        // "name" was renamed to "type" in mask 7.1.
        $fieldType = $definition['type'] ?? $definition['name'] ?? $definition['options'] ?? null;
        if ($fieldType !== null) {
            $tcaFieldDefinition->type = FieldType::cast($fieldType);
        }

        // Always set minitems / maxitems so the config array is not completely empty.
        if (!$tcaFieldDefinition->isCoreField && $tcaFieldDefinition->hasFieldType() && $tcaFieldDefinition->getFieldType()->isFileReference()) {
            $definition['config']['minitems'] ??= '';
        }

        $definition = self::migrateTCA($definition, $tcaFieldDefinition);

        // Now config is clean. Extract real TCA.
        $tcaFieldDefinition->realTca = self::extractRealTca($definition, $tcaFieldDefinition);

        // "rte" was used to identify RTE fields prior to v6.
        if (!$tcaFieldDefinition->hasFieldType() && !empty($definition['rte'])) {
            $tcaFieldDefinition->type = FieldType::cast(FieldType::RICHTEXT);
        }

        // The core field bodytext used to be hard coded as richtext in Mask versions < v7.2
        if (!$tcaFieldDefinition->hasFieldType() && $definition['key'] === 'bodytext') {
            $tcaFieldDefinition->type = FieldType::cast(FieldType::RICHTEXT);
        }

        // If the field is not a core field and the field type couldn't be resolved by now, resolve type by tca config.
        if (!$tcaFieldDefinition->hasFieldType() && !$tcaFieldDefinition->isCoreField) {
            $tcaFieldDefinition->type = FieldType::cast(FieldTypeUtility::getFieldType($tcaFieldDefinition->toArray(), $tcaFieldDefinition->fullKey));
        }

        // If imageoverlayPalette is not set (because of updates to newer version), fallback to default behaviour.
        if (isset($definition['imageoverlayPalette'])) {
            $tcaFieldDefinition->imageoverlayPalette = (bool)$definition['imageoverlayPalette'];
        } elseif ($tcaFieldDefinition->hasFieldType() && $tcaFieldDefinition->type->equals(FieldType::FILE)) {
            $tcaFieldDefinition->imageoverlayPalette = true;
        }

        if (isset($definition['inlineParent'])) {
            if (is_array($definition['inlineParent'])) {
                $tcaFieldDefinition->inlineParentByElement = $definition['inlineParent'];
            } else {
                $tcaFieldDefinition->inlineParent = $definition['inlineParent'];
            }
        }

        if (!empty($definition['bodytextTypeByElement'])) {
            foreach ($definition['bodytextTypeByElement'] as $elementKey => $bodytextType) {
                $tcaFieldDefinition->bodytextTypeByElement[$elementKey] = FieldType::cast($bodytextType);
            }
            // Unset the normal type
            $tcaFieldDefinition->type = null;
        }

        if (isset($definition['label'])) {
            if (is_array($definition['label'])) {
                $tcaFieldDefinition->labelByElement = $definition['label'];
            } else {
                $tcaFieldDefinition->label = $definition['label'];
            }
        }

        if (isset($definition['description'])) {
            if (is_array($definition['description'])) {
                $tcaFieldDefinition->descriptionByElement = $definition['description'];
            } else {
                $tcaFieldDefinition->description = $definition['description'];
            }
        }

        if (isset($definition['order'])) {
            if (is_array($definition['order'])) {
                foreach ($definition['order'] as $orderKey => $order) {
                    $tcaFieldDefinition->orderByElement[$orderKey] = (int)$order;
                }
            } else {
                $tcaFieldDefinition->order = (int)$definition['order'];
            }
        }

        return $tcaFieldDefinition;
    }

    private static function extractRealTca(array $definition, TcaFieldDefinition $fieldDefinition): array
    {
        // Unset some values that are not needed in TCA
        unset(
            $definition['options'],
            $definition['coreField'],
            $definition['type'],
            $definition['name'],
            $definition['key'],
            $definition['fullKey'],
            $definition['rte'],
            $definition['inlineParent'],
            $definition['inPalette'],
            $definition['order'],
            $definition['inlineIcon'],
            $definition['inlineLabel'],
            $definition['imageoverlayPalette'],
            $definition['cTypes'],
            $definition['onlineMedia'],
            $definition['allowedFileExtensions'],
            $definition['ctrl']
        );

        // Unset label if it is from palette fields
        if (is_array($definition['label'] ?? false)) {
            unset($definition['label']);
        }
        // Unset description if it is from palette fields
        if (is_array($definition['description'] ?? false)) {
            unset($definition['description']);
        }

        return self::removeBlankOptions($definition, $fieldDefinition);
    }

    public function getOverridesDefinition(): array
    {
        $fieldConfig = $this->toArray();
        $overrideTcaConfig = $fieldConfig['config'] ?? [];
        foreach (array_keys($overrideTcaConfig) as $configKey) {
            if (in_array($configKey, self::NON_OVERRIDEABLE_OPTIONS, true)) {
                unset($overrideTcaConfig[$configKey]);
            }
        }
        // cleanup other options that are stored in minimal definition
        unset(
            $fieldConfig['inlineParent'],
            $fieldConfig['label'],
            $fieldConfig['description'],
            $fieldConfig['order'],
            $fieldConfig['inPalette'],
            $fieldConfig['coreField'],
            $fieldConfig['bodytextTypeByElement'],
        );
        $fieldConfig['config'] = $overrideTcaConfig;
        return $fieldConfig;
    }

    public function getMinimalDefinition(): array
    {
        $minimalFieldTca = $this->toArray();
        foreach (array_keys($minimalFieldTca['config'] ?? []) as $configKey) {
            if (!in_array($configKey, self::NON_OVERRIDEABLE_OPTIONS, true)) {
                unset($minimalFieldTca['config'][$configKey]);
            }
        }

        // cleanup other options that are stored in override definition
        unset(
            $minimalFieldTca['allowedFileExtensions'],
            $minimalFieldTca['onlineMedia'],
        );
        return $minimalFieldTca;
    }

    public function hasInlineParent(string $elementKey = ''): bool
    {
        if ($this->inlineParent !== '') {
            return true;
        }

        if ($elementKey === '') {
            return $this->inlineParentByElement !== [];
        }

        return isset($this->inlineParentByElement[$elementKey]);
    }

    public function getInlineParent(string $elementKey = ''): string
    {
        // if inlineParent is an array, it's in a palette on default table
        if (!empty($this->inlineParentByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->inlineParentByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->inlineParentByElement[$elementKey];
        }

        return $this->inlineParent;
    }

    public function hasOrder(): bool
    {
        return $this->order !== 0 || $this->orderByElement !== [];
    }

    public function getOrder(string $elementKey = ''): int
    {
        if (!empty($this->orderByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->orderByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->orderByElement[$elementKey];
        }

        return $this->order;
    }

    public function getLabel(string $elementKey = ''): string
    {
        if (!empty($this->labelByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->labelByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->labelByElement[$elementKey];
        }

        return $this->label;
    }

    public function getDescription(string $elementKey = ''): string
    {
        if (!empty($this->descriptionByElement)) {
            if ($elementKey === '') {
                throw new \InvalidArgumentException(sprintf('The field "%s" is in multiple elements. Please specifiy the element key.', $this->fullKey), 1629711093);
            }

            if (!isset($this->descriptionByElement[$elementKey])) {
                throw new \InvalidArgumentException(sprintf('The field "%s" does not exist in element "%s".', $this->fullKey, $elementKey), 1629711055);
            }

            return $this->descriptionByElement[$elementKey];
        }

        return $this->description;
    }

    public function toArray(bool $withBackwardsCompatibility = false): array
    {
        $field = $this->realTca;
        $field += [
            'key' => $this->key,
            'fullKey' => $this->fullKey,
            'type' => $this->type ? (string)$this->type : null,
            'coreField' => $this->isCoreField ? 1 : null,
            'inPalette' => $this->inPalette ? 1 : null,
            'cTypes' => !empty($this->cTypes) ? $this->cTypes : null,
            'onlineMedia' => !empty($this->onlineMedia) ? $this->onlineMedia : null,
            'allowedFileExtensions' => $this->allowedFileExtensions !== '' ? $this->allowedFileExtensions : null,
        ];

        // Backwards compatibility for loadInlineFields
        if ($withBackwardsCompatibility) {
            $field['maskKey'] = $this->fullKey;
        }

        if ($this->hasFieldType() && $this->type->equals(FieldType::FILE)) {
            $field['imageoverlayPalette'] = $this->imageoverlayPalette ? 1 : 0;
        }

        if ($this->inlineIcon !== '') {
            $field['ctrl']['iconfile'] = $this->inlineIcon;
        }

        if ($this->inlineLabel !== '') {
            $field['ctrl']['label'] = $this->inlineLabel;
        }

        if (!empty($this->inlineParentByElement)) {
            $field['inlineParent'] = $this->inlineParentByElement;
        } elseif ($this->inlineParent !== '') {
            $field['inlineParent'] = $this->inlineParent;
        }

        if (!empty($this->labelByElement)) {
            $field['label'] = $this->labelByElement;
        } elseif ($this->label !== '') {
            $field['label'] = $this->label;
        }

        if (!empty($this->descriptionByElement)) {
            $field['description'] = $this->descriptionByElement;
        } elseif ($this->description !== '') {
            $field['description'] = $this->description;
        }

        if (!empty($this->orderByElement)) {
            $field['order'] = $this->orderByElement;
        } elseif ($this->order > 0) {
            $field['order'] = $this->order;
        }

        $field = array_filter($field, static function ($item) {
            return $item !== null;
        });

        if (!empty($this->inlineFields)) {
            foreach ($this->inlineFields as $inlineField) {
                $field['inlineFields'][] = $inlineField->toArray(true);
            }
        }

        return $field;
    }

    public function addInlineField(TcaFieldDefinition $definition): void
    {
        $this->inlineFields[] = $definition;
    }

    /**
     * Removes all the blank options from the tca
     */
    protected static function removeBlankOptions(array $haystack, TcaFieldDefinition $fieldDefinition, array $path = []): array
    {
        foreach ($haystack as $key => $value) {
            $path[] = $key;
            $fullPath = implode('.', $path);
            if (
                $fieldDefinition->hasFieldType()
                && array_key_exists((string)$fieldDefinition->type, self::ALLOWED_EMPTY_VALUES_BY_TYPE)
                && in_array($fullPath, self::ALLOWED_EMPTY_VALUES_BY_TYPE[(string)$fieldDefinition->type], true)
            ) {
                array_pop($path);
                continue;
            }
            if (
                is_array($value)
                && !(
                    $fieldDefinition->hasFieldType()
                    && array_key_exists((string)$fieldDefinition->type, self::STOP_RECURSIVE_VALUES_BY_TYPE)
                    && in_array($fullPath, self::STOP_RECURSIVE_VALUES_BY_TYPE[(string)$fieldDefinition->type], true)
                )
            ) {
                $haystack[$key] = self::removeBlankOptions($value, $fieldDefinition, $path);
            }
            if ((is_array($haystack[$key]) && empty($haystack[$key])) || ($haystack[$key] === '')) {
                unset($haystack[$key]);
            }
            array_pop($path);
        }
        return $haystack;
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<string, mixed>
     */
    protected static function migrateTCA(array $definition, TcaFieldDefinition $tcaFieldDefinition): array
    {
        // "inlineIcon" and "inlineLabel" renamed to ctrl.iconfile and ctrl.label in Mask v7.0.
        $tcaFieldDefinition->inlineIcon = $definition['ctrl']['iconfile'] ?? $definition['inlineIcon'] ?? '';
        $tcaFieldDefinition->inlineLabel = $definition['ctrl']['label'] ?? $definition['inlineLabel'] ?? '';

        // Since mask v7.0.0 the path for allowedFileExtensions has changed to root level. Keep this as fallback.
        $tcaFieldDefinition->allowedFileExtensions = $definition['allowedFileExtensions'] ?? $definition['config']['filter']['0']['parameters']['allowedFileExtensions'] ?? '';
        unset($definition['config']['filter']);

        // Migration for type Link (Changed in TYPO3 v8 / Mask v3)
        if (($definition['config']['wizards']['link']['module']['name'] ?? '') === 'wizard_link') {
            $definition['config']['fieldControl']['linkPopup']['options']['allowedExtensions'] = $definition['config']['wizards']['link']['params']['allowedExtensions'] ?? '';
            $definition['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] = $definition['config']['wizards']['link']['params']['blindLinkOptions'] ?? '';
            $tcaFieldDefinition->type = new FieldType(FieldType::LINK);
            unset($definition['config']['wizards']);
        }

        // Migration for foreign_record_defaults Mask v2 / TYPO3 v7.
        if (isset($definition['config']['foreign_record_defaults'])) {
            $definition['config']['overrideChildTca']['columns']['colPos']['config']['default'] = 999;
            unset($definition['config']['foreign_record_defaults']);
        }

        // #94765: Migrate levelLinksPosition "none" to showNewRecordLink=false (TYPO3 v11).
        if (
            $tcaFieldDefinition->hasFieldType()
            && ($tcaFieldDefinition->type->equals(FieldType::INLINE) || $tcaFieldDefinition->type->equals(FieldType::CONTENT))
            && ($definition['config']['appearance']['levelLinksPosition'] ?? '') === 'none'
        ) {
            $definition['config']['appearance']['levelLinksPosition'] = 'top';
            $definition['config']['appearance']['showNewRecordLink'] = 0;
        }

        // #94406: Migrate folder config to fileFolderConfig (TYPO3 v11).
        if (
            $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::SELECT)
        ) {
            if (isset($definition['config']['fileFolder'])) {
                $definition['config']['fileFolderConfig']['folder'] = $definition['config']['fileFolder'];
                unset($definition['config']['fileFolder']);
            }
            if (isset($definition['config']['fileFolder_extList'])) {
                $definition['config']['fileFolderConfig']['allowedExtensions'] = $definition['config']['fileFolder_extList'];
                unset($definition['config']['fileFolder_extList']);
            }
            if (isset($definition['config']['fileFolder_recursions'])) {
                $definition['config']['fileFolderConfig']['depth'] = $definition['config']['fileFolder_recursions'];
                unset($definition['config']['fileFolder_recursions']);
            }
        }

        // Fill item values with empty strings.
        if (
            $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::SELECT)
        ) {
            foreach ($definition['config']['items'] ?? [] as $index => $item) {
                for ($i = 0; $i < 5; $i++) {
                    if (!isset($item[$i])) {
                        $definition['config']['items'][$index][$i] = '';
                    }
                }
            }
        }

        // TCA type="inline" and foreign_table="sys_file_reference" are not necessary for type file.
        if ($tcaFieldDefinition->hasFieldType() && $tcaFieldDefinition->type->isFileReference()) {
            unset($definition['config']['type']);
            unset($definition['config']['foreign_table']);
        }

        // TCA type="datetime" moved "eval=date, datetime, ..." to "format".
        if (
            (new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::TIMESTAMP)
            && ($definition['config']['eval'] ?? '') !== ''
        ) {
            $evalList = explode(',', $definition['config']['eval']);
            $dateTypesInKeys = array_values(array_intersect($evalList, ['date', 'datetime', 'time', 'timesec']));
            $dateType = $dateTypesInKeys[0] ?? null;
            if ($dateType !== null) {
                $definition['config']['format'] = $dateType;
                // Remove dateType from normal eval array
                $keys = array_filter($evalList, static function ($a) use ($dateTypesInKeys) {
                    return $a !== $dateTypesInKeys[0];
                });
                $definition['config']['eval'] = implode(',', $keys);
            }
        }

        // TCA type=link "fieldControl" moved to "appearance"
        if (
            (new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::LINK)
        ) {
            if ($definition['config']['fieldControl']['linkPopup']['options']['allowedExtensions'] ?? false) {
                $definition['config']['appearance']['allowedFileExtensions'] = $definition['config']['fieldControl']['linkPopup']['options']['allowedExtensions'];
            }
            $blindLinkOptions = $definition['config']['fieldControl']['linkPopup']['options']['blindLinkOptions'] ?? false;
            if ($blindLinkOptions) {
                $availableTypes = $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler'] ?? [];
                if ($availableTypes !== []) {
                    $availableTypes = array_keys($availableTypes);
                } else {
                    // Fallback to a static list, in case linkHandler configuration is not available at this point
                    $availableTypes = ['page', 'file', 'folder', 'url', 'email', 'record', 'telephone'];
                }
                $definition['config']['allowedTypes'] = array_values(array_diff(
                    $availableTypes,
                    GeneralUtility::trimExplode(',', str_replace('mail', 'email', $blindLinkOptions), true)
                ));
            }
            unset($definition['config']['fieldControl']);
        }

        // New TCA type email since TYPO3 v12
        if (
            (new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::STRING)
        ) {
            $evalList = GeneralUtility::trimExplode(',', $definition['config']['eval'] ?? '');
            if (in_array('email', $evalList, true)) {
                $evalList = array_intersect($evalList, ['unique', 'uniqueInPid']);
                $definition['config']['eval'] = implode(',', $evalList);
                $definition['config']['type'] = 'email';
                $tcaFieldDefinition->setFieldType(new FieldType(FieldType::EMAIL));
            }
        }

        // New TCA type folder since TYPO3 v12
        if ((new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && $tcaFieldDefinition->type->equals(FieldType::GROUP)
        ) {
            if (($definition['config']['internal_type'] ?? '') === 'folder') {
                unset($definition['config']['internal_type']);
                $definition['config']['type'] = 'folder';
                $tcaFieldDefinition->setFieldType(new FieldType(FieldType::FOLDER));
            }
        }

        // TYPO3 v12 required=true / nullable=true
        if ((new Typo3Version())->getMajorVersion() > 11) {
            $evalList = GeneralUtility::trimExplode(',', $definition['config']['eval'] ?? '');
            if (in_array('required', $evalList, true)) {
                $definition['config']['required'] = 1;
                $evalList = array_diff($evalList, ['required']);
            }
            if (in_array('null', $evalList, true)) {
                $definition['config']['nullable'] = 1;
                $evalList = array_diff($evalList, ['null']);
            }
            $definition['config']['eval'] = implode(',', $evalList);
        }

        // TYPO3 v12 associative array keys for items.
        if (
            (new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && (
                $tcaFieldDefinition->getFieldType()->equals(FieldType::SELECT)
                || $tcaFieldDefinition->getFieldType()->equals(FieldType::RADIO)
                || $tcaFieldDefinition->getFieldType()->equals(FieldType::CHECK)
            )
            && !empty($definition['config']['items'])
        ) {
            $processedItems = array_map(
                fn (array $item) => SelectItem::fromTcaItemArray($item, (string)$tcaFieldDefinition->getFieldType())->toArray(),
                $definition['config']['items']
            );
            // Remove null and false (=invertStateDisplay) values.
            $definition['config']['items'] = array_map(fn (array $item): array => array_filter($item, fn ($value): bool => $value !== null && $value !== false), $processedItems);
        }

        // TYPO3 v12 type=number
        if (
            (new Typo3Version())->getMajorVersion() > 11
            && $tcaFieldDefinition->hasFieldType()
            && (
                $tcaFieldDefinition->getFieldType()->equals(FieldType::INTEGER)
                || $tcaFieldDefinition->getFieldType()->equals(FieldType::FLOAT)
            )
            && !empty($definition['config']['eval'])
        ) {
            $evalList = GeneralUtility::trimExplode(',', $definition['config']['eval']);
            if (in_array('int', $evalList, true) || in_array('double2', $evalList, true)) {
                $evalList = array_diff($evalList, ['int', 'double2']);
                $definition['config']['eval'] = implode(',', $evalList);
            }
        }

        return $definition;
    }

    public function hasFieldType(string $elementKey = ''): bool
    {
        return $this->type instanceof FieldType || ($elementKey !== '' && !empty($this->bodytextTypeByElement));
    }

    public function getFieldType(string $elementKey = ''): FieldType
    {
        if (!$this->hasFieldType($elementKey)) {
            throw new \OutOfBoundsException('The field "' . $this->fullKey . '" does not have a defined field type.', 1650054092);
        }

        if ($this->type instanceof FieldType) {
            return $this->type;
        }

        return $this->bodytextTypeByElement[$elementKey] ?? new FieldType(FieldType::RICHTEXT);
    }

    public function setFieldType(FieldType $fieldType): void
    {
        $this->type = $fieldType;
    }

    public function isNullable(): bool
    {
        if ((new Typo3Version())->getMajorVersion() > 11) {
            return (bool)($this->realTca['config']['nullable'] ?? false);
        }
        return GeneralUtility::inList($this->realTca['config']['eval'] ?? '', 'null');
    }

    public function mergeTca(TcaFieldDefinition $tcaFieldDefinition): TcaFieldDefinition
    {
        $field = clone $this;
        ArrayUtility::mergeRecursiveWithOverrule($field->realTca, $tcaFieldDefinition->realTca);

        $field->allowedFileExtensions = $tcaFieldDefinition->allowedFileExtensions;
        $field->onlineMedia = $tcaFieldDefinition->onlineMedia;

        return $field;
    }
}
