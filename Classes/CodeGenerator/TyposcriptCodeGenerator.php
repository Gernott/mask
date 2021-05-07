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
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Imaging\IconProvider\ContentElementIconProvider;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates all the typoscript needed for mask content elements
 */
class TyposcriptCodeGenerator
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * @var array
     */
    protected $extSettings;

    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    public function __construct(StorageRepository $storageRepository, SettingsService $settingsService)
    {
        $this->storageRepository = $storageRepository;
        $this->settingsService = $settingsService;
        $this->extSettings = $settingsService->get();
    }

    /**
     * Generates the tsConfig typoscript and registers
     * the icons for the content elements
     *
     * @return string
     */
    public function generateTsConfig(): string
    {
        $json = $this->storageRepository->load();
        // generate page TSconfig
        $content = '';
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        // make content-Elements
        foreach ($json['tt_content']['elements'] ?? [] as $element) {
            // Register icons for contentelements
            $iconIdentifier = 'mask-ce-' . $element['key'];
            $iconRegistry->registerIcon(
                $iconIdentifier,
                ContentElementIconProvider::class,
                [
                    'contentElementKey' => $element['key']
                ]
            );

            if ($element['hidden']) {
                continue;
            }

            // add the content element wizard for each content element
            $element['description'] = trim(preg_replace('/\s+/', ' ', $element['description']));
            $cTypeKey = AffixUtility::addMaskCTypePrefix($element['key']);
            $wizard = [
                'header' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
                'elements.' . $cTypeKey => [
                    'iconIdentifier' => $iconIdentifier,
                    'title' => $element['label'],
                    'description' => $element['description'],
                    'tt_content_defValues' => [
                        'CType' => $cTypeKey
                    ]
                ]
            ];
            $content .= "mod.wizards.newContentElement.wizardItems.mask {\n";
            $content .= $this->convertArrayToTypoScript($wizard, '', 1);
            $content .= "\tshow := addToList(" . $cTypeKey . ");\n";
            $content .= "}\n";

            // and switch the labels depending on which content element is selected
            $content .= "\n[isMaskContentType(\"" . $cTypeKey . "\")]\n";
            foreach ($element['columns'] ?? [] as $index => $column) {
                $this->setLabel($column, $index, $element, 'tt_content', $content);
            }
            $content .= "[end]\n\n";
        }

        return $content;
    }

    /**
     * Generates the typoscript for pages
     * @return string
     */
    public function generatePageTyposcript(): string
    {
        $json = $this->storageRepository->load();
        $pagesContent = '';
        foreach ($json['pages']['elements'] ?? [] as $element) {
            // Labels for pages
            $pagesContent .= "\n[maskBeLayout('" . $element['key'] . "')]\n";
            // if page has backendlayout with this element-key
            foreach ($element['columns'] ?? [] as $index => $column) {
                $this->setLabel($column, $index, $element, 'pages', $pagesContent);
            }
            $pagesContent .= "[end]\n";
        }

        return $pagesContent;
    }

    /**
     * @param $column
     * @param $index
     * @param $element
     * @param $table
     * @param $content
     */
    protected function setLabel($column, $index, $element, $table, &$content)
    {
        if ($this->storageRepository->getFormType($column, $element['key'], $table) == FieldType::PALETTE) {
            $items = $this->storageRepository->loadInlineFields($column, $element['key']);
            foreach ($items as $item) {
                if (is_array($item['label'])) {
                    $label = $item['label'][$element['key']];
                } else {
                    $label = $item['label'];
                }
                // With config is custom mask field
                if (isset($item['config'])) {
                    $key = AffixUtility::addMaskPrefix($item['key']);
                } else {
                    $key = $item['key'];
                }
                $content .= ' TCEFORM.' . $table . '.' . $key . '.label = ' . $label . "\n";
            }
        } else {
            $content .= ' TCEFORM.' . $table . '.' . $column . '.label = ' . $element['labels'][$index] . "\n";
        }
    }

    /**
     * Generates the typoscript for the setup field
     * @return string
     */
    public function generateSetupTyposcript(): string
    {
        $configuration = $this->storageRepository->load();
        // generate TypoScript setup
        $setupContent = [];

        // for base paths to fluid templates configured in extension settings
        $setupContent[] = $this->convertArrayToTypoScript([
            'templateRootPaths' => [
                10 => rtrim($this->extSettings['content'], '/') . '/'
            ],
            'partialRootPaths' => [
                10 => rtrim($this->extSettings['partials'], '/') . '/'
            ],
            'layoutRootPaths' => [
                10 => rtrim($this->extSettings['layouts'], '/') . '/'
            ]
        ], 'lib.maskContentElement');

        // for each content element
        if ($configuration['tt_content']['elements']) {
            foreach ($configuration['tt_content']['elements'] as $element) {
                if (!$element['hidden']) {
                    $cTypeKey = AffixUtility::addMaskCTypePrefix($element['key']);
                    $templateName = MaskUtility::getTemplatePath($this->extSettings, $element['key'], true);
                    $elementContent = [];
                    $elementContent[] = 'tt_content.' . $cTypeKey . ' =< lib.maskContentElement' . LF;
                    $elementContent[] = 'tt_content.' . $cTypeKey . ' {' . LF;
                    $elementContent[] = "\t" . 'templateName = ' . $templateName . LF;
                    $elementContent[] = '}' . LF . LF;
                    $setupContent[] = implode('', $elementContent);
                }
            }
        }

        return implode("\n\n", $setupContent);
    }

    /**
     * Converts given array to TypoScript
     *
     * @param array $typoScriptArray The array to convert to string
     * @param string $addKey Prefix given values with given key (eg. lib.whatever = {...})
     * @param int $tab Internal
     * @param bool $init Internal
     * @return string TypoScript
     */
    protected function convertArrayToTypoScript(array $typoScriptArray, $addKey = '', $tab = 0, $init = true): string
    {
        $typoScript = '';
        if ($addKey !== '') {
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . $addKey . " {\n";
            if ($init === true) {
                $tab++;
            }
        }
        $tab++;
        foreach ($typoScriptArray as $key => $value) {
            if (!is_array($value)) {
                if (strpos($value, "\n") === false) {
                    $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . "$key = $value\n";
                } else {
                    $typoScript .= str_repeat(
                        "\t",
                        ($tab === 0) ? $tab : $tab - 1
                    ) . "$key (\n$value\n" . str_repeat(
                        "\t",
                        ($tab === 0) ? $tab : $tab - 1
                    ) . ")\n";
                }
            } else {
                $typoScript .= $this->convertArrayToTypoScript($value, $key, $tab, false);
            }
        }
        if ($addKey !== '') {
            $tab--;
            $typoScript .= str_repeat("\t", ($tab === 0) ? $tab : $tab - 1) . '}';
            if ($init !== true) {
                $typoScript .= "\n";
            }
        }
        return $typoScript;
    }
}
