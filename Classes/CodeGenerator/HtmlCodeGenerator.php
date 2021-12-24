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

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Generates Fluid HTML for Contentelements
     */
    public function generateHtml(string $elementKey, string $table): string
    {
        $tableDefinitionCollection = $this->loader->load();
        $element = $tableDefinitionCollection->loadElement($table, $elementKey);

        if (!$element) {
            return '';
        }

        $html = [];
        foreach ($element->tcaDefinition as $field) {
            $part = $this->generateFieldHtml($field->fullKey, $elementKey, $table);
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
    protected function generateFieldHtml(string $fieldKey, string $elementKey, string $table, string $datafield = 'data', int $depth = 0): string
    {
        $tableDefinitionCollection = $this->loader->load();
        $html = [];
        $fieldType = $tableDefinitionCollection->getFieldType($fieldKey, $table);
        if (!$fieldType->isRenderable()) {
            return '';
        }
        switch ((string)$fieldType) {
            case FieldType::SELECT:
                if (($GLOBALS['TCA'][$table]['columns'][$fieldKey]['config']['foreign_table'] ?? '') !== '') {
                    $html[] =  $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $fieldKey . '_items}" as="' . $datafield . '_item' . '">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<div>{' . $datafield . '_item.uid}' . '</div>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                } else {
                    $html[] = $this->drawWhitespace(0 + $depth) . $this->getVariable($datafield, $fieldKey);
                }
                break;
            case FieldType::RADIO:
            case FieldType::CHECK:
                $html[] = $this->drawWhitespace(0 + $depth) . $this->getVariable($datafield, $fieldKey);
                break;
            case FieldType::CONTENT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="' . $datafield . '_item' . '">';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:cObject typoscriptObjectPath="lib.tx_mask.content">{' . $datafield . '_item.uid}</f:cObject>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</f:for>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::DATE:
            case FieldType::TIMESTAMP:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . ' -> f:format.date(format: \'d.m.Y\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::DATETIME:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . ' -> f:format.date(format: \'d.m.Y - H:i:s\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::FILE:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="file">';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:image image="{file}" width="200" />';
                $html[] = $this->drawWhitespace(2 + $depth) . '{file.description}';
                $html[] = $this->drawWhitespace(1 + $depth) . '</f:for>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::FLOAT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . ' -> f:format.number(decimals: \'2\', decimalSeparator: \',\', thousandsSeparator: \'.\')}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::INLINE:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<ul>';
                $html[] = $this->drawWhitespace(2 + $depth) . '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="' . $datafield . '_item' . '">';
                $html[] = $this->drawWhitespace(3 + $depth) . '<li>';
                foreach ($tableDefinitionCollection->loadInlineFields($fieldKey, $elementKey) as $inlineField) {
                    $html[] = $this->generateFieldHtml($inlineField->fullKey, $elementKey, $fieldKey, $datafield . '_item', 4 + $depth);
                }
                $html[] = $this->drawWhitespace(3 + $depth) . '</li>';
                $html[] = $this->drawWhitespace(2 + $depth) . '</f:for>';
                $html[] = $this->drawWhitespace(1 + $depth) . '</ul>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::PALETTE:
                foreach ($tableDefinitionCollection->loadInlineFields($fieldKey, $elementKey) as $paletteField) {
                    $part = $this->generateFieldHtml($paletteField->fullKey, $elementKey, $table, $datafield, $depth);
                    if ($part !== '') {
                        $html[] = $part;
                    }
                }
                break;
            case FieldType::GROUP:
                if (($GLOBALS['TCA'][$table]['columns'][$fieldKey]['config']['internal_type'] ?? '') !== 'folder') {
                    $html[] = $this->drawWhitespace(0 + $depth) . '<f:for each="{' . $datafield . '.' . $fieldKey . '_items}" as="' . $datafield . '_item' . '">';
                    $html[] = $this->drawWhitespace(1 + $depth) . '<div>{' . $datafield . '_item.uid}' . '</div>';
                    $html[] = $this->drawWhitespace(0 + $depth) . '</f:for>';
                    break;
                }
                // no break intended.
            case FieldType::STRING:
            case FieldType::INTEGER:
            case FieldType::COLORPICKER:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . '}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::LINK:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '<f:link.typolink parameter="{' . $datafield . '.' . $fieldKey . '}"></f:link.typolink>';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::RICHTEXT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . ' -> f:format.html()}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::TEXT:
                $html[] = $this->drawWhitespace(0 + $depth) . '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">';
                $html[] = $this->drawWhitespace(1 + $depth) . '{' . $datafield . '.' . $fieldKey . ' -> f:format.nl2br()}';
                $html[] = $this->drawWhitespace(0 + $depth) . '</f:if>';
                break;
            case FieldType::SLUG:
                $html[] = $this->drawWhitespace(0 + $depth) . '<div id="{' . $datafield . '.' . $fieldKey . '}"></div>';
                break;
        }
        return implode("\n", $html);
    }

    protected function getVariable(string $datafield, string $fieldKey): string
    {
        $key = AffixUtility::hasMaskPrefix($fieldKey) ? AffixUtility::removeMaskPrefix($fieldKey) : $fieldKey;
        return '<f:variable name="' . GeneralUtility::underscoredToLowerCamelCase($key) . '" value="{' . $datafield . '.' . $fieldKey . '}"/>';
    }

    protected function drawWhitespace(int $times = 1): string
    {
        return str_repeat(' ', $this->indent * $times);
    }
}
