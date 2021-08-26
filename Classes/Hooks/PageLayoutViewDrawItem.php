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

use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Helper\InlineHelper;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Renders the backend preview of mask content elements
 */
class PageLayoutViewDrawItem implements PageLayoutViewDrawItemHookInterface
{
    /**
     * @var InlineHelper
     */
    protected $inlineHelper;

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    public function __construct(
        array $maskExtensionConfiguration,
        InlineHelper $inlineHelper,
        TableDefinitionCollection $tableDefinitionCollection
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->inlineHelper = $inlineHelper;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
    }

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
        // only render special backend preview if it is a mask element
        if (AffixUtility::hasMaskCTypePrefix($row['CType'])) {
            $elementKey = AffixUtility::removeCTypePrefix($row['CType']);

            // fallback to prevent breaking change
            $templatePathAndFilename = MaskUtility::getTemplatePath(
                $this->maskExtensionConfiguration,
                $elementKey,
                false,
                MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['backend'])
            );

            if (file_exists($templatePathAndFilename)) {
                // initialize view
                $view = GeneralUtility::makeInstance(StandaloneView::class);

                // Load the backend template
                $view->setTemplatePathAndFilename($templatePathAndFilename);

                // if there are paths for layouts and partials set, add them to view
                if (!empty($this->maskExtensionConfiguration['layouts_backend'])) {
                    $layoutRootPath = MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['layouts_backend']);
                    $view->setLayoutRootPaths([$layoutRootPath]);
                }
                if (!empty($this->maskExtensionConfiguration['partials_backend'])) {
                    $partialRootPath = MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration['partials_backend']);
                    $view->setPartialRootPaths([$partialRootPath]);
                }

                // Fetch and assign some useful variables
                $data = $this->getContentObject($row['uid']);
                $view->assign('row', $row);
                $view->assign('data', $data);

                // if the elementLabel contains LLL: then translate it
                $elementLabel = $this->tableDefinitionCollection->loadElement('tt_content', $elementKey)->elementDefinition->label;
                if (GeneralUtility::isFirstPartOfStr($elementLabel, 'LLL:')) {
                    $elementLabel = LocalizationUtility::translate($elementLabel, 'mask');
                }

                // Render everything
                $content = $view->render();
                $editElementUrlParameters = [
                    'edit' => [
                        'tt_content' => [
                            $row['uid'] => 'edit'
                        ]
                    ],
                    'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
                ];

                $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
                $editElementUrl = $uriBuilder->buildUriFromRoute('record_edit', $editElementUrlParameters);
                $headerContent = '<strong><a href="' . $editElementUrl . '">' . $elementLabel . '</a></strong><br>';
                $itemContent .= '<div class="content_preview content_preview_' . $elementKey . '">';
                $itemContent .= $content;
                $itemContent .= '</div>';
                $drawItem = false;
            }
        }
    }

    /**
     * Returns an array with properties of content element with given uid
     *
     * @param int $uid of content element to get
     * @return array with all properties of given content element uid
     * @throws Exception
     * @throws \Exception
     */
    protected function getContentObject(int $uid): array
    {
        $data = BackendUtility::getRecordWSOL('tt_content', $uid);
        $this->inlineHelper->addFilesToData($data);
        $this->inlineHelper->addIrreToData($data);

        return $data;
    }
}
