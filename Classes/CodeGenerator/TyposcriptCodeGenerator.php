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
use MASK\Mask\Utility\ArrayToTypoScriptConverterUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
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

        $tt_content = $this->tableDefinitionCollection->getTable('tt_content');
        $content = '';

        // Register content elements and add them in new content element wizard.
        foreach ($tt_content->elements as $element) {
            // Register icons for contentelements
            $iconIdentifier = 'mask-ce-' . $element->key;
            $this->iconRegistry->registerIcon(
                $iconIdentifier,
                ContentElementIconProvider::class,
                [
                    'contentElementKey' => $element->key
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
                        'CType' => $cTypeKey
                    ]
                ]
            ];
            $content .= "mod.wizards.newContentElement.wizardItems.mask {\n";
            $content .= ArrayToTypoScriptConverterUtility::convert($wizard, '', 1);
            $content .= "\tshow := addToList(" . $cTypeKey . ");\n";
            $content .= "}\n";

            // Switch the labels depending on which content element is selected.
            $content .= "\n[isMaskContentType(\"" . $cTypeKey . "\")]\n";
            foreach ($element->columns as $index => $column) {
                $content = $this->setLabel($column, $index, $element, 'tt_content', $content);
            }
            $content .= "[end]\n\n";
        }

        return $content;
    }

    /**
     * Generates the typoscript for pages
     */
    public function generatePageTyposcript(): string
    {
        if (!$this->tableDefinitionCollection->hasTable('pages')) {
            return '';
        }

        $pagesContent = '';
        $pages = $this->tableDefinitionCollection->getTable('pages');
        foreach ($pages->elements as $element) {
            // Labels for pages
            $pagesContent .= "\n[maskBeLayout('" . $element->key . "')]\n";
            // if page has backendlayout with this element-key
            foreach ($element->columns as $index => $column) {
                $pagesContent = $this->setLabel($column, $index, $element, 'pages', $pagesContent);
            }
            $pagesContent .= "[end]\n";
        }

        return $pagesContent;
    }

    /**
     * Sets the label via TCEFORM
     */
    protected function setLabel(string $fieldKey, int $index, ElementDefinition $element, string $table, string $content): string
    {
        $fieldDefinition = $this->tableDefinitionCollection->loadField($table, $fieldKey);
        if (!$fieldDefinition) {
            return $content;
        }
        // As this is called very early, TCA for core fields might not be loaded yet. So ignore them.
        if (!$fieldDefinition->isCoreField && $this->tableDefinitionCollection->getFieldType($fieldKey, $table, $element->key)->equals(FieldType::PALETTE)) {
            foreach ($this->tableDefinitionCollection->loadInlineFields($fieldKey, $element->key) as $field) {
                $content .= ' TCEFORM.' . $table . '.' . $field->fullKey . '.label = ' . $field->getLabel($element->key) . "\n";
            }
        } else {
            $content .= ' TCEFORM.' . $table . '.' . $fieldKey . '.label = ' . $element->labels[$index] . "\n";
        }

        return $content;
    }

    /**
     * Generates the typoscript for the setup field
     */
    public function generateSetupTyposcript(): string
    {
        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return '';
        }

        $setupContent = [];

        // for base paths to fluid templates configured in extension settings
        $setupContent[] = ArrayToTypoScriptConverterUtility::convert([
            'templateRootPaths' => [
                10 => rtrim($this->maskExtensionConfiguration['content'], '/') . '/'
            ],
            'partialRootPaths' => [
                10 => rtrim($this->maskExtensionConfiguration['partials'], '/') . '/'
            ],
            'layoutRootPaths' => [
                10 => rtrim($this->maskExtensionConfiguration['layouts'], '/') . '/'
            ]
        ], 'lib.maskContentElement');

        $tt_content = $this->tableDefinitionCollection->getTable('tt_content');
        foreach ($tt_content->elements as $element) {
            if ($element->hidden) {
                continue;
            }
            $cTypeKey = AffixUtility::addMaskCTypePrefix($element->key);
            $templateName = MaskUtility::getTemplatePath($this->maskExtensionConfiguration, $element->key, true, null, true);
            $elementContent = [];
            $elementContent[] = 'tt_content.' . $cTypeKey . ' =< lib.maskContentElement' . LF;
            $elementContent[] = 'tt_content.' . $cTypeKey . ' {' . LF;
            $elementContent[] = "\t" . 'templateName = ' . $templateName . LF;
            $elementContent[] = '}' . LF . LF;

            $setupContent[] = implode('', $elementContent);
        }

        return implode("\n\n", $setupContent);
    }
}
