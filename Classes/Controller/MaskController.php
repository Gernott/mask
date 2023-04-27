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

namespace MASK\Mask\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @internal
 */
class MaskController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected PageRenderer $pageRenderer;
    protected UriBuilder $uriBuilder;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        UriBuilder $uriBuilder,
        PageRenderer $pageRenderer
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->uriBuilder = $uriBuilder;
        $this->pageRenderer = $pageRenderer;
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            return $this->renderLegacyModuleResponse($request);
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->getDocHeaderComponent()->disable();
        $moduleTemplate->assign('settingsUrl', $this->uriBuilder->buildUriFromRoute('tools_toolssettings'));
        $moduleTemplate->assign('iconSize', 'medium');
        $this->pageRenderer->loadJavaScriptModule('@mask/mask');
        $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/mask.css');
        return $moduleTemplate->renderResponse('Wizard/Main');
    }

    protected function renderLegacyModuleResponse(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->getDocHeaderComponent()->disable();

        if ((new Typo3Version())->getMajorVersion() < 12) {
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('mask');
            $view->setTemplate('Wizard/MainLegacy');
            $view->assign('settingsUrl', $this->uriBuilder->buildUriFromRoute('tools_toolssettings'));
            $view->assign('iconSize', 'default');
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Mask/AmdBundle');
            $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/mask.css');
            $moduleTemplate->setContent($view->render());
        }

        return new HtmlResponse($moduleTemplate->renderContent());
    }
}
