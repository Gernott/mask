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

namespace MASK\Mask\CodeGenerator;

use MASK\Mask\Definition\ElementDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Imaging\IconProvider\ContentElementIconProvider;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\ArrayToTypoScriptConverter;
use MASK\Mask\Utility\TemplatePathUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;

/**
 * Generates all the typoscript needed for mask content elements
 * @internal
 */
class TyposcriptCodeGenerator
{
    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var IconRegistry
     */
    protected $iconRegistry;

    public function __construct(
        TableDefinitionCollection $tableDefinitionCollection,
        array $maskExtensionConfiguration,
        IconRegistry $iconRegistry
    ) {
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->iconRegistry = $iconRegistry;
    }

    /**
     * Generates TSconfig and registers the icons for the content elements.
     */
    public function generateTsConfig(): string
    {
        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return '';
        }

        // Register content elements and add them in new content element wizard.
        $TSConfig = [];
        $tt_content = $this->tableDefinitionCollection->getTable('tt_content');
        foreach ($tt_content->elements as $element) {
            // Register icons for contentelements
            $iconIdentifier = 'mask-ce-' . $element->key;
            $this->iconRegistry->registerIcon(
                $iconIdentifier,
                ContentElementIconProvider::class,
                [
                    'key' => $element->key,
                    'label' => $element->label,
                    'icon' => $element->icon,
                    'color' => $element->color,
                ]
            );

            if ($element->hidden) {
                continue;
            }

            // Remove any whitespace characters for the description.
            $element->description = trim(preg_replace('/\s+/', ' ', $element->description));
            $cTypeKey = AffixUtility::addMaskCTypePrefix($element->key);
            $wizard = [
                'header' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
                'elements.' . $cTypeKey => [
                    'iconIdentifier' => $iconIdentifier,
                    'title' => $element->label,
                    'description' => $element->description,
                    'tt_content_defValues' => [
                        'CType' => $cTypeKey,
                    ],
                    'saveAndClose' => $element->saveAndClose ? '1' : '0',
                ],
            ];

            // Add overlay icon
            // @todo it is not possible right now to detect a custom overlay icon
            // @todo on localconf bootstrap (with FAL), as it loads the CacheManager
            // @todo which is not available that early. A better way would be to
            // @todo add a configuration for custom icons, instead of magically
            // @todo detect them by name.
            if ($element->iconOverlay !== '') {
                $iconOverlayIdentifier = 'mask-ce-' . $element->key . '-overlay';
                $this->iconRegistry->registerIcon(
                    $iconOverlayIdentifier,
                    ContentElementIconProvider::class,
                    [
                        // @todo '_overlay' added to not conflict with the main icon.
                        // @todo will only work right now, if a FontAwesome icon is selected. See comment above.
                        'key' => $element->key . '_overlay',
                        'label' => $element->label,
                        'icon' => $element->iconOverlay,
                        'color' => $element->colorOverlay,
                    ]
                );
                $wizard['elements.' . $cTypeKey]['iconOverlay'] = $iconOverlayIdentifier;
            }

            $TSConfig[] = '';
            $TSConfig[] = 'mod.wizards.newContentElement.wizardItems.mask {';
            $TSConfig[] = ArrayToTypoScriptConverter::convert($wizard, '', 1);
            $TSConfig[] = "show := addToList($cTypeKey)";
            $TSConfig[] = '}';
            $TSConfig[] = '';
        }

        return implode("\n", $TSConfig);
    }

    /**
     * Generates the typoscript for pages
     */
    public function generatePageTSConfigOverridesForBackendLayouts(): string
    {
        if (!$this->tableDefinitionCollection->hasTable('pages')) {
            return '';
        }

        $TSConfig = [];
        $pages = $this->tableDefinitionCollection->getTable('pages');
        foreach ($pages->elements as $element) {
            // Labels for pages
            $TSConfig[] = '';
            $TSConfig[] = "[maskBeLayout('$element->key')]";
            // if page has backendlayout with this element-key
            foreach ($element->columns as $index => $column) {
                $TSConfig = $this->generateTCEFORM($column, $index, $element, 'pages', $TSConfig);
            }
            $TSConfig[] = '[end]';
            $TSConfig[] = '';
        }

        return implode("\n", $TSConfig);
    }

    /**
     * Sets the label via TCEFORM
     */
    protected function generateTCEFORM(string $fieldKey, int $index, ElementDefinition $element, string $table, array $TSConfig): array
    {
        $fieldDefinition = $this->tableDefinitionCollection->loadField($table, $fieldKey);
        if (!$fieldDefinition) {
            return $TSConfig;
        }
        // As this is called very early, TCA for core fields might not be loaded yet. So ignore them.
        if (!$fieldDefinition->isCoreField && $this->tableDefinitionCollection->getFieldType($fieldKey, $table)->equals(FieldType::PALETTE)) {
            foreach ($this->tableDefinitionCollection->loadInlineFields($fieldKey, $element->key) as $field) {
                // Add label from field config.
                $label = $field->getLabel($element->key);
                if ($label !== '') {
                    $TSConfig[] = 'TCEFORM.' . $table . '.' . $field->fullKey . '.label = ' . $label;
                }

                // Add description from field config.
                $description = $field->getDescription($element->key);
                if ($description !== '') {
                    $TSConfig[] = 'TCEFORM.' . $table . '.' . $field->fullKey . '.description (';
                    $TSConfig[] = $description;
                    $TSConfig[] = ')';
                }
            }
        } else {
            // Add label from elements array.
            if (($element->labels[$index] ?? '') !== '') {
                $TSConfig[] = 'TCEFORM.' . $table . '.' . $fieldKey . '.label = ' . $element->labels[$index];
            }

            // Add description from elements array.
            if (($element->descriptions[$index] ?? '') !== '') {
                $TSConfig[] = 'TCEFORM.' . $table . '.' . $fieldKey . '.description (';
                $TSConfig[] = $element->descriptions[$index];
                $TSConfig[] = ')';
            }
        }

        return $TSConfig;
    }

    /**
     * Generates the typoscript for the setup field
     */
    public function generateSetupTyposcript(): string
    {
        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return '';
        }

        // for base paths to fluid templates configured in extension settings
        $paths = [];
        if ($this->maskExtensionConfiguration['content'] ?? false) {
            $paths['templateRootPaths'] = [
                10 => $this->maskExtensionConfiguration['content'],
            ];
        }

        if ($this->maskExtensionConfiguration['partials'] ?? false) {
            $paths['partialRootPaths'] = [
                10 => $this->maskExtensionConfiguration['partials'],
            ];
        }

        if ($this->maskExtensionConfiguration['layouts'] ?? false) {
            $paths['layoutRootPaths'] = [
                10 => $this->maskExtensionConfiguration['layouts'],
            ];
        }

        $setupContent[] = ArrayToTypoScriptConverter::convert($paths, 'lib.maskContentElement');

        foreach ($this->tableDefinitionCollection->getTable('tt_content')->elements as $element) {
            if ($element->hidden) {
                continue;
            }
            $cTypeKey = AffixUtility::addMaskCTypePrefix($element->key);
            $templateName = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $element->key, true, null, true);
            $elementContent = [];
            $elementContent[] = "tt_content.$cTypeKey =< lib.maskContentElement";
            $elementContent[] = "tt_content.$cTypeKey {";
            $elementContent[] = "\t" . "templateName = $templateName";
            $elementContent[] = '}';

            $setupContent[] = implode("\n", $elementContent);
        }

        return implode("\n\n", $setupContent) . "\n\n";
    }
}
