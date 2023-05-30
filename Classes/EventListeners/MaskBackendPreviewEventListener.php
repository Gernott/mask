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

namespace MASK\Mask\EventListeners;

use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Helper\InlineHelper;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\TemplatePathUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Fluid\View\StandaloneView;

class MaskBackendPreviewEventListener
{
    /**
     * @var array<string, string>
     */
    protected array $maskExtensionConfiguration;
    protected InlineHelper $inlineHelper;
    protected TableDefinitionCollection $tableDefinitionCollection;
    protected UriBuilder $uriBuilder;

    /**
     * @param array<string, string> $maskExtensionConfiguration
     */
    public function __construct(
        array $maskExtensionConfiguration,
        InlineHelper $inlineHelper,
        TableDefinitionCollection $tableDefinitionCollection,
        UriBuilder $uriBuilder
    ) {
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->inlineHelper = $inlineHelper;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->uriBuilder = $uriBuilder;
    }

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        // Nothing to do, if it's not a Mask element.
        if (!AffixUtility::hasMaskCTypePrefix($event->getRecord()['CType'])) {
            return;
        }

        $elementKey = AffixUtility::removeCTypePrefix($event->getRecord()['CType']);
        $elementTcaDefinition = $this->tableDefinitionCollection->loadElement('tt_content', $elementKey);
        // If the Mask element couldn't be found, provide a proper error message.
        if (!$elementTcaDefinition instanceof ElementTcaDefinition) {
            $itemContent = '<span class="badge badge-warning">'
                . sprintf($this->getLanguageService()->sL('LLL:EXT:mask/Resources/Private/Language/locallang.xlf:tx_mask.error.mask_definition_missing'), $elementKey)
                . '</span>';
            $event->setPreviewContent($itemContent);
            return;
        }

        // Fallback to prevent breaking change
        $templatePathAndFilename = TemplatePathUtility::getTemplatePath(
            $this->maskExtensionConfiguration,
            $elementKey,
            false,
            GeneralUtility::getFileAbsFileName($this->maskExtensionConfiguration['backend'] ?? '')
        );

        if (!file_exists($templatePathAndFilename)) {
            return;
        }

        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $renderingContext->setRequest($this->getRequest());

        // Initialize view.
        $view = GeneralUtility::makeInstance(StandaloneView::class, $renderingContext);
        $view->setTemplatePathAndFilename($templatePathAndFilename);
        if (!empty($this->maskExtensionConfiguration['layouts_backend'])) {
            $layoutRootPath = GeneralUtility::getFileAbsFileName($this->maskExtensionConfiguration['layouts_backend']);
            $view->setLayoutRootPaths([$layoutRootPath]);
        }
        if (!empty($this->maskExtensionConfiguration['partials_backend'])) {
            $partialRootPath = GeneralUtility::getFileAbsFileName($this->maskExtensionConfiguration['partials_backend']);
            $view->setPartialRootPaths([$partialRootPath]);
        }

        // Fetch and assign some useful variables
        $data = BackendUtility::getRecordWSOL('tt_content', (int)$event->getRecord()['uid']);
        $this->inlineHelper->addFilesToData($data);
        $this->inlineHelper->addIrreToData($data);
        $view->assign('row', $event->getRecord());
        $view->assign('data', $data);

        // Render the preview with a standard border.
        $itemContent = '<div class="content_preview content_preview_' . $elementKey . '">';
        $itemContent .= $view->render();
        $itemContent .= '</div>';
        $event->setPreviewContent($itemContent);
    }

    /**
     * @param array<string, mixed> $record
     */
    protected function getEditUrl(array $record): string
    {
        $urlParameters = [
            'edit' => [
                'tt_content' => [
                    $record['uid'] => 'edit',
                ],
            ],
            'returnUrl' => $GLOBALS['TYPO3_REQUEST']->getAttribute('normalizedParams')->getRequestUri() . '#element-tt_content-' . $record['uid'],
        ];
        return $this->uriBuilder->buildUriFromRoute('record_edit', $urlParameters) . '#element-tt_content-' . $record['uid'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
