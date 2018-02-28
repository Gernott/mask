<?php

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

/**
 * Generates all the typoscript needed for mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class TyposcriptCodeGenerator extends AbstractCodeGenerator
{

    /**
     * Generates the tsConfig typoscript and registers
     * the icons for the content elements
     *
     * @param array $json
     * @return string
     */
    public function generateTsConfig($json)
    {
        // generate page TSconfig
        $content = "";
        $temp = "";

        // Load page.ts Template
        $template = file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mask') . "Resources/Private/Mask/page.ts", true);
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\Imaging\IconRegistry");

        // make content-Elements
        if ($json["tt_content"]["elements"]) {
            foreach ($json["tt_content"]["elements"] as $element) {
                // Register icons for contentelements
                $iconIdentifier = 'mask-ce-' . $element["key"];
                $iconRegistry->registerIcon(
                    $iconIdentifier, "MASK\Mask\Imaging\IconProvider\ContentElementIconProvider", array(
                    'contentElementKey' => $element["key"]
                    )
                );
                if (!$element["hidden"]) {
                    $temp = str_replace("###ICON###", "iconIdentifier = " . $iconIdentifier, $template);
                    $temp = str_replace("###KEY###", $element["key"], $temp);
                    $temp = str_replace("###LABEL###", $element["label"], $temp);
                    $temp = str_replace("###DESCRIPTION###", $element["description"], $temp);
                    $content.= $temp;

                    // Labels
                    $content .= "\n[userFunc = user_mask_contentType(CType|mask_" . $element["key"] . ")]\n";
                    if ($element["columns"]) {
                        foreach ($element["columns"] as $index => $column) {
                            $content .= " TCEFORM.tt_content." . $column . ".label = " . $element["labels"][$index] . "\n";
                        }
                    }
                    $content .= "[end]\n\n";
                }
            }
        }
        return $content;
    }

    /**
     * Generates the typoscript for pages
     * @param array $json
     * @return string
     */
    public function generatePageTyposcript($json)
    {
        $pageColumns = array();
        $disableColumns = "";
        $pagesContent = "";
        if ($json["pages"]["elements"]) {
            foreach ($json["pages"]["elements"] as $element) {
                // Labels for pages
                $pagesContent .= "\n[userFunc = user_mask_beLayout(" . $element["key"] . ")]\n";
                // if page has backendlayout with this element-key
                if ($element["columns"]) {
                    foreach ($element["columns"] as $index => $column) {
                        $pagesContent .= " TCEFORM.pages." . $column . ".label = " . $element["labels"][$index] . "\n";
                        $pagesContent .= " TCEFORM.pages_language_overlay." . $column . ".label = " . $element["labels"][$index] . "\n";
                    }
                    $pagesContent .= "\n";
                    foreach ($element["columns"] as $index => $column) {
                        $pageColumns[] = $column;
                        $pagesContent .= " TCEFORM.pages." . $column . ".disabled = 0\n";
                        $pagesContent .= " TCEFORM.pages_language_overlay." . $column . ".disabled = 0\n";
                    }
                }
                $pagesContent .= "[end]\n";
            }
        }
        // disable all fields by default and only activate by condition
        foreach ($pageColumns as $column) {
            $disableColumns .= "TCEFORM.pages." . $column . ".disabled = 1\n";
            $disableColumns .= "TCEFORM.pages_language_overlay." . $column . ".disabled = 1\n";
        }
        $pagesContent = $disableColumns . "\n" . $pagesContent;
        return $pagesContent;
    }

    /**
     * Generates the typoscript for the setup.ts
     * @param array $configuration
     * @param array $settings
     */
    public function generateSetupTyposcript($configuration, $settings)
    {

        // generate TypoScript setup
        $setupContent = '
module.tx_mask {
	view {
		templateRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Templates/
		}
		partialRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Partials/
		}
		layoutRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Layouts/
		}
	}
	persistence{
		classes {
			MASK\Mask\Domain\Model\BackendLayout {
				mapping {
					tableName = backend_layout
					columns {
						uid.mapOnProperty = uid
						title.mapOnProperty = title
					}
				}
			}
		}
	}
}
';
        // Load setup.ts Template
        $template = file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mask') . "Resources/Private/Mask/setup.ts", true);
        // Fill setup.ts:
        if ($configuration["tt_content"]["elements"]) {
            foreach ($configuration["tt_content"]["elements"] as $element) {
                if (!$element["hidden"]) {
                    $temp = str_replace("###KEY###", $element["key"], $template);
                    $temp = str_replace("###PATH###", $settings['content'] . $element["key"] . '.html', $temp);
                    $setupContent.= $temp;
                }
            }
        }
        return $setupContent;
    }
}
