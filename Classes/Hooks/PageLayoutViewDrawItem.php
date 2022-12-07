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

namespace MASK\Mask\Hooks;

use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Helper\InlineHelper;
use MASK\Mask\Loader\LoaderRegistry;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\TemplatePathUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Renders the backend preview of mask content elements
 * @internal
 */
class PageLayoutViewDrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionalities
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     */
    public function preProcess(
        PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ): void {
        $maskExtensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('mask');
        $inlineHelper = GeneralUtility::makeInstance(InlineHelper::class);
        $tableDefinitionCollection = GeneralUtility::makeInstance(LoaderRegistry::class)->getActiveLoader()->load();

        // only render special backend preview if it is a mask element
        if (!AffixUtility::hasMaskCTypePrefix($row['CType'])) {
            return;
        }

        $elementKey = AffixUtility::removeCTypePrefix($row['CType']);
        $elementTcaDefinition = $tableDefinitionCollection->loadElement('tt_content', $elementKey);
        // If the Mask element couldn't be found, provide a proper error message.
        if (!$elementTcaDefinition instanceof ElementTcaDefinition) {
            $drawItem = false;
            $itemContent = '<span class="badge badge-warning">'
                . sprintf($this->getLanguageService()->sL('LLL:EXT:mask/Resources/Private/Language/locallang.xlf:tx_mask.error.mask_definition_missing'), $elementKey)
                . '</span>';
            return;
        }

        // fallback to prevent breaking change
        $templatePathAndFilename = TemplatePathUtility::getTemplatePath(
            $maskExtensionConfiguration,
            $elementKey,
            false,
            GeneralUtility::getFileAbsFileName($maskExtensionConfiguration['backend'] ?? '')
        );

        // User defined backend preview exists. Turn off TYPO3 auto preview.
        if (!file_exists($templatePathAndFilename)) {
            return;
        }

        // Turn off TYPO3 auto preview rendering.
        $drawItem = false;

        // initialize view
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        // Load the backend template
        $view->setTemplatePathAndFilename($templatePathAndFilename);

        // if there are paths for layouts and partials set, add them to the view
        if (!empty($maskExtensionConfiguration['layouts_backend'])) {
            $layoutRootPath = GeneralUtility::getFileAbsFileName($maskExtensionConfiguration['layouts_backend']);
            $view->setLayoutRootPaths([$layoutRootPath]);
        }
        if (!empty($maskExtensionConfiguration['partials_backend'])) {
            $partialRootPath = GeneralUtility::getFileAbsFileName($maskExtensionConfiguration['partials_backend']);
            $view->setPartialRootPaths([$partialRootPath]);
        }

        // Fetch and assign some useful variables
        $data = BackendUtility::getRecordWSOL('tt_content', (int)$row['uid']);
        $inlineHelper->addFilesToData($data);
        $inlineHelper->addIrreToData($data);

        $view->assign('row', $row);
        $view->assign('data', $data);

        // Translate element label
        $elementLabel = $this->getLanguageService()->sL($elementTcaDefinition->elementDefinition->label);

        // Render everything
        $content = $view->render();
        $editElementUrlParameters = [
            'edit' => [
                'tt_content' => [
                    $row['uid'] => 'edit',
                ],
            ],
            'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri() . '#element-tt_content-' . $row['uid'],
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $editElementUrl = $uriBuilder->buildUriFromRoute('record_edit', $editElementUrlParameters);
        $headerContent = '<strong><a href="' . $editElementUrl . '">' . $elementLabel . '</a></strong><br>';
        $itemContent .= '<div class="content_preview content_preview_' . $elementKey . '">';
        $itemContent .= $content;
        $itemContent .= '</div>';
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
