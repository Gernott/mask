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

use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\AffixUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates the html and fluid for mask content elements
 * @internal
 */
class HtmlCodeGenerator
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var int
     */
    protected $indent = 4;

    /**
     * @var TableDefinitionCollection|null
     */
    protected $tableDefinitionCollection;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Generates Fluid HTML for Contentelements
     */
    public function generateHtml(string $elementKey, string $table): string
    {
        $this->tableDefinitionCollection = $this->loader->load();
        $element = $this->tableDefinitionCollection->loadElement($table, $elementKey);

        if (!$element instanceof ElementTcaDefinition) {
            return '';
        }

        $html = [];
        foreach ($element->getRootTcaFields() as $field) {
            $part = $this->generateFieldHtml($field, $element, $table);
            if ($part !== '') {
                $html[] = $part;
            }
        }
        if (count($html) > 0) {
            return implode("\n", $html) . "\n";
        }
        return '';
    }

    /**
     * Generates HTML for a field
     */
    protected function generateFieldHtml(TcaFieldDefinition $field, ElementTcaDefinition $element, string $table, string $datafield = 'data', int $depth = 0): string
    {
        $html = [];
        if (!$field->hasFieldType($element->elementDefinition->key) || !$field->getFieldType($element->elementDefinition->key)->isRenderable()) {
            return '';
        }
        switch ((string)$field->getFieldType($element->elementDefinition->key)) {
            case FieldType::SELECT:
                if (($field->realTca['config']['foreign_table'] ?? '') !== '') {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '_items}" as="' . $datafield . '_item' . '">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<div>{' . $datafield . '_item.uid}' . '</div>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                } elseif (in_array(($field->realTca['config']['renderType'] ?? ''), ['selectCheckBox', 'selectSingleBox', 'selectMultipleSideBySide'], true)) {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '_items}" as="' . $datafield . '_item' . '">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<div>{' . $datafield . '_item}' . '</div>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                } else {
                    $html[] = $this->drawWhitespace(0 + $depth) . $this->getVariableViewHelper($datafield, $field->fullKey);
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:switch expression="{' . $this->getVariableName($field->fullKey) . '}">';
                    foreach ($field->realTca['config']['items'] ?? [] as $item) {
                        $html[] = $this->drawWhitespace(1 + $depth) . '<f:case value="' . $item[1] . '">';
                        $html[] = $this->drawWhitespace(2 + $depth) . '{' . $this->getVariableName($field->fullKey) . '}';
                        $html[] = $this->drawWhitespace(1 + $depth) . '</f:case>';
                    }
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:switch>';
                }
                break;
            case FieldType::CATEGORY:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '_items}" as="category">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<div>{category.title}</div>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                break;
            case FieldType::RADIO:
            case FieldType::CHECK:
                $html[] = $this->drawWhitespace(0 + $depth) . $this->getVariableViewHelper($datafield, $field->fullKey);
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $this->getVariableName($field->fullKey) . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:then>';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:comment><!-- Do this, if checked --></f:comment>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</f:then>';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:else>';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:comment><!-- Else do this --></f:comment>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</f:else>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';

                break;
            case FieldType::CONTENT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '}" as="' . $datafield . '_item' . '">';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:cObject typoscriptObjectPath="lib.tx_mask.content">{' . $datafield . '_item.uid}</f:cObject>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</f:for>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::DATE:
            case FieldType::TIMESTAMP:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . ' -> f:format.date(format: \'d.m.Y\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::DATETIME:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . ' -> f:format.date(format: \'d.m.Y - H:i:s\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::FILE:
                if ($field->imageoverlayPalette) {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '}" as="file">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<f:image image="{file}" width="200" />';
                    $html[] = $this->drawWhitespace(1 + $depth) . '{file.description}';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                } else {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '}" as="file">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<a href="{file.originalFile.publicUrl}" target="_blank">{f:if(condition: file.title, then: file.title, else: file.name)}</a>';
                    $html[] = $this->drawWhitespace(1 + $depth) . '{file.description}';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<f:format.bytes>{file.size}</f:format.bytes>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                }
                break;
            case FieldType::MEDIA:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '}" as="file">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:media file="{file}" width="200" />';
                $html[] = $this->drawWhitespace(1 + $depth) . '{file.description}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                break;
            case FieldType::FLOAT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . ' -> f:format.number(decimals: \'2\', decimalSeparator: \',\', thousandsSeparator: \'.\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::INLINE:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<ul>';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '}" as="' . $datafield . '_item' . '">';
                $html[] = $this->drawWhitespace(3 + $depth) . '<li>';
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $element->elementDefinition->key) as $inlineField) {
                    $html[] = $this->generateFieldHtml($inlineField, $element, $field->fullKey, $datafield . '_item', 4 + $depth);
                }
                $html[] = $this->drawWhitespace(3 + $depth) . '</li>';
                $html[] = $this->drawWhitespace(2 + $depth) . '</f:for>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</ul>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::PALETTE:
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $element->elementDefinition->key) as $paletteField) {
                    $part = $this->generateFieldHtml($paletteField, $element, $table, $datafield, $depth);
                    if ($part !== '') {
                        $html[] = $part;
                    }
                }
                break;
            case FieldType::GROUP:
                if (($field->realTca['config']['internal_type'] ?? '') !== 'folder') {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $field->fullKey . '_items}" as="' . $datafield . '_item' . '">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<div>{' . $datafield . '_item.uid}' . '</div>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                    break;
                }
                // no break intended.
            case FieldType::STRING:
            case FieldType::INTEGER:
            case FieldType::COLORPICKER:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . '}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::LINK:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:link.typolink parameter="{' . $datafield . '.' . $field->fullKey . '}"></f:link.typolink>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::RICHTEXT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . ' -> f:format.html()}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::TEXT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $field->fullKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $field->fullKey . ' -> f:format.nl2br()}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::SLUG:
                $html[] = $this->drawWhitespace(0 + $depth) . '<div id="{' . $datafield . '.' . $field->fullKey . '}"></div>';
                break;
        }
        return implode("\n", $html);
    }

    protected function getVariableViewHelper(string $datafield, string $fieldKey): string
    {
        return '<f:variable name="' . $this->getVariableName($fieldKey) . '" value="{' . $datafield . '.' . $fieldKey . '}"/>';
    }

    protected function getVariableName(string $fieldKey): string
    {
        $key = AffixUtility::hasMaskPrefix($fieldKey) ? AffixUtility::removeMaskPrefix($fieldKey) : $fieldKey;
        return GeneralUtility::underscoredToLowerCamelCase($key);
    }

    protected function drawWhitespace(int $times = 1): string
    {
        return str_repeat(' ', $this->indent * $times);
    }
}
