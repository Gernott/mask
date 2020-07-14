<?php
declare(strict_types=1);

namespace MASK\Mask\CodeGenerator;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use MASK\Mask\Domain\Model\BackendLayout;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use MASK\Mask\Imaging\IconProvider\ContentElementIconProvider;

/**
 * Generates all the typoscript needed for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
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
                $iconIdentifier, ContentElementIconProvider::class, [
                    'contentElementKey' => $element['key']
                ]
            );

            if ($element['hidden']) {
                continue;
            }

            // add the content element wizard for each content element
            $wizard = [
                'header' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf:new_content_element_tab',
                'elements.mask_' . $element['key'] => [
                    'iconIdentifier' => $iconIdentifier,
                    'title' => $element['label'],
                    'description' => $element['description'],
                    'tt_content_defValues' => [
                        'CType' => 'mask_' . $element['key']
                    ]
                ],

            ];
            $content .= "mod.wizards.newContentElement.wizardItems.mask {\n";
            $content .= $this->convertArrayToTypoScript($wizard, '', 1);
            $content .= "\tshow := addToList(mask_" . $element['key'] . ");\n";
            $content .= "}\n";

            // and switch the labels depending on which content element is selected
            $content .= "\n[isMaskContentType(\"mask_" . $element['key'] . "\")]\n";
            foreach ($element['columns'] ?? [] as $index => $column) {
                $content .= ' TCEFORM.tt_content.' . $column . '.label = ' . $element['labels'][$index] . "\n";
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
                $pagesContent .= ' TCEFORM.pages.' . $column . '.label = ' . $element['labels'][$index] . "\n";
            }
            $pagesContent .= "[end]\n";
        }

        return $pagesContent;
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

        // for backend module
        $setupContent[] = $this->convertArrayToTypoScript([
            'view' => [
                'templateRootPaths' => [
                    10 => 'EXT:mask/Resources/Private/Backend/Templates/'
                ],
                'partialRootPaths' => [
                    10 => 'EXT:mask/Resources/Private/Backend/Partials/'
                ],
                'layoutRootPaths' => [
                    10 => 'EXT:mask/Resources/Private/Backend/Layouts/'
                ]
            ],
            'persistence' => [
                'classes' => [
                    BackendLayout::class => [
                        'mapping' => [
                            'tableName' => 'backend_layout',
                            'columns' => [
                                'uid.mapOnProperty' => 'uid',
                                'title.mapOnProperty' => 'title'
                            ]
                        ]
                    ]
                ]
            ]
        ], 'module.tx_mask');

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
                    $templateName = MaskUtility::getTemplatePath($this->extSettings, $element['key'], true);
                    $elementContent = [];
                    $elementContent[] = 'tt_content.mask_' . $element['key'] . ' =< lib.maskContentElement' . LF;
                    $elementContent[] = 'tt_content.mask_' . $element['key'] . " {" . LF;
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
     * @param integer $tab Internal
     * @param boolean $init Internal
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
                    $typoScript .= str_repeat("\t",
                            ($tab === 0) ? $tab : $tab - 1) . "$key (\n$value\n" . str_repeat("\t",
                            ($tab === 0) ? $tab : $tab - 1) . ")\n";
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
