<?php

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

use MASK\Mask\Domain\Repository\StorageRepository;

/**
 * Generates the html and fluid for mask content elements
 */
class HtmlCodeGenerator
{
    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @param StorageRepository $storageRepository
     */
    public function __construct(StorageRepository $storageRepository)
    {
        $this->storageRepository = $storageRepository;
    }

    /**
     * Generates Fluid HTML for Contentelements
     *
     * @param string $key
     * @param string $table
     * @return string $html
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    public function generateHtml($key, $table): string
    {
        $storage = $this->storageRepository->loadElement($table, $key);
        $html = '';
        if ($storage['tca']) {
            foreach ($storage['tca'] as $fieldKey => $fieldConfig) {
                $html .= $this->generateFieldHtml($fieldKey, $key, $table);
            }
        }
        return $html;
    }

    /**
     * Generates HTML for a field
     * @param string $fieldKey
     * @param string $elementKey
     * @param string $table
     * @param string $datafield
     * @return string $html
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function generateFieldHtml($fieldKey, $elementKey, $table, $datafield = 'data'): string
    {
        $html = '';
        switch ($this->storageRepository->getFormType($fieldKey, $elementKey, $table)) {
            case 'Check':
                $html .= '{f:if(condition: ' . $datafield . '.' . $fieldKey . ", then: 'On', else: 'Off')}<br />\n\n";
                break;
            case 'Content':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="' . $datafield . '_item' . "\">\n";
                $html .= '<f:cObject typoscriptObjectPath="lib.tx_mask.content">{' . $datafield . '_item.uid}</f:cObject><br />' . "\n";
                $html .= "</f:for>\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Date':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:format.date format="d.m.Y">{' . $datafield . '.' . $fieldKey . '}</f:format.date><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Datetime':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:format.date format="d.m.Y - H:i:s">{' . $datafield . '.' . $fieldKey . '}</f:format.date><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'File':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="file">
  <f:image image="{file}" alt="{file.alternative}" title="{file.title}" width="200" /><br />
  {file.description} / {file.identifier}<br />
</f:for>' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Float':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:format.number decimals="2" decimalSeparator="," thousandsSeparator=".">{' . $datafield . '.' . $fieldKey . '}</f:format.number><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Inline':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= "<ul>\n";
                $html .= '<f:for each="{' . $datafield . '.' . $fieldKey . '}" as="' . $datafield . '_item' . "\">\n<li>";
                $inlineFields = $this->storageRepository->loadInlineFields($fieldKey);
                if ($inlineFields) {
                    foreach ($inlineFields as $inlineField) {
                        $html .= $this->generateFieldHtml(
                            $inlineField['maskKey'],
                            $elementKey,
                            $fieldKey,
                            $datafield . '_item'
                        ) . "\n";
                    }
                }
                $html .= "</li>\n</f:for>" . "\n";
                $html .= "</ul>\n";
                $html .= "</f:if>\n\n";
                break;
            case 'String':
            case 'Integer':
            case 'Group':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '{' . $datafield . '.' . $fieldKey . '}<br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Link':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:link.typolink parameter="{' . $datafield . '.' . $fieldKey . '}"></f:link.typolink><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Select':
            case 'Radio':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:switch expression="{' . $datafield . '.' . $fieldKey . '}">
  <f:case value="1">Value is: 1</f:case>
  <f:case value="2">Value is: 2</f:case>
  <f:case value="3">Value is: 3</f:case>
</f:switch><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Richtext':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:format.html>{' . $datafield . '.' . $fieldKey . '}</f:format.html><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
            case 'Text':
                $html .= '<f:if condition="{' . $datafield . '.' . $fieldKey . '}">' . "\n";
                $html .= '<f:format.nl2br>{' . $datafield . '.' . $fieldKey . '}</f:format.nl2br><br />' . "\n";
                $html .= "</f:if>\n\n";
                break;
        }
        return $html;
    }
}
