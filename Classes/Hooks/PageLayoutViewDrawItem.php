<?php

namespace MASK\Mask\Hooks;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Renders the backend preview of mask content elements
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 * @package MASK
 * @subpackage mask
 */
class PageLayoutViewDrawItem implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface
{

    protected $objectManager;
    protected $inlineHelper;
    protected $storageRepository;

    /**
     * SettingsService
     *
     * @var \MASK\Mask\Domain\Service\SettingsService
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param boolean $drawItem Whether to draw the item using the default functionalities
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     * @return void
     */
    public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        $this->settingsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Service\\SettingsService');
        $this->extSettings = $this->settingsService->get();

        // only render special backend preview if it is a mask element
        if (substr($row['CType'], 0, 4) === "mask") {
            $elementKey = substr($row['CType'], 5);
            $templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extSettings["backend"]);
            $templatePathAndFilename = $templateRootPath . $elementKey . '.html';

            if (file_exists($templatePathAndFilename)) {
                // initialize some things we need
                $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\Object\\ObjectManager');
                $this->inlineHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\InlineHelper');
                $this->storageRepository = $this->objectManager->get("MASK\Mask\Domain\Repository\StorageRepository");
                $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');

                // Load the backend template
                $view->setTemplatePathAndFilename($templatePathAndFilename);

                // if there are paths for layouts and partials set, add them to view
                if (!empty($this->extSettings["layouts_backend"])) {
                    $layoutRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extSettings["layouts_backend"]);
                    $view->setLayoutRootPaths(array($layoutRootPath));
                }
                if (!empty($this->extSettings["partials_backend"])) {
                    $partialRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extSettings["partials_backend"]);
                    $view->setPartialRootPaths(array($partialRootPath));
                }

                // Fetch and assign some useful variables
                $data = $this->getContentObject($row["uid"]);
                $element = $this->storageRepository->loadElement("tt_content", $elementKey);
                $view->assign("row", $row);
                $view->assign("data", $data);

                // if the elementLabel contains LLL: then translate it
                $elementLabel = $element["label"];
                if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($elementLabel, 'LLL:')) {
                    $elementLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($elementLabel, "mask");
                }

                // Render everything
                $content = $view->render();
                $headerContent = '<strong>' . $elementLabel . '</strong><br>';
                $itemContent .= '<div style="display:block; padding: 10px 0 4px 0px;border-top: 1px solid #CACACA;margin-top: 6px;" class="content_preview_' . $elementKey . '">';
                $itemContent .= $content;
                $itemContent .= '</div>';
                $drawItem = FALSE;
            }
        }
    }

    /**
     * Returns an array with properties of content element with given uid
     *
     * @param int $uid of content element to get
     * @return array with all properties of given content element uid
     */
    protected function getContentObject($uid)
    {
        $data = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', 'uid=' . $uid);
        $this->inlineHelper->addFilesToData($data, "tt_content");
        $this->inlineHelper->addIrreToData($data);
        return $data;
    }
}
