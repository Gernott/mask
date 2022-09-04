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
    protected StandaloneView $view;
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
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $settingsUrl = $this->uriBuilder->buildUriFromRoute('tools_toolssettings');
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->assign('settingsUrl', $settingsUrl);
        $iconSize = (new Typo3Version())->getMajorVersion() > 11 ? 'medium' : 'default';
        $this->view->assign('iconSize', $iconSize);
        $this->view->getRenderingContext()->setControllerAction('Wizard/Main');
        $this->view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('mask');
        $moduleTemplate->getDocHeaderComponent()->disable();
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Mask/Mask');
        $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/mask.css');
        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }
}
