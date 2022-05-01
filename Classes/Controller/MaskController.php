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
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class MaskController
 * @internal
 */
class MaskController
{
    /**
     * @var ModuleTemplate
     */
    private $moduleTemplate;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    public function __construct(
        ModuleTemplate $moduleTemplate,
        UriBuilder $uriBuilder,
        PageRenderer $pageRenderer
    ) {
        $this->moduleTemplate = $moduleTemplate;
        $this->uriBuilder = $uriBuilder;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * The main action.
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeView('Wizard/Main');
        $this->pageRenderer->addRequireJsConfiguration(
            [
                'paths' => [
                    'sortablejs' => PathUtility::getAbsoluteWebPath(
                        ExtensionManagementUtility::extPath('mask', 'Resources/Public/JavaScript/Contrib/sortable')
                    ),
                ],
            ]
        );
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Mask/Mask');
        $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/mask.css');
        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Sets up the Fluid View.
     */
    protected function initializeView(string $templateName): void
    {
        $settingsUrl = $this->uriBuilder->buildUriFromRoute('tools_toolssettings');
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->assign('settingsUrl', $settingsUrl);
        $this->view->getRequest()->setControllerExtensionName('mask');
        $this->view->getRenderingContext()->setControllerAction($templateName);
        $this->view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('mask');
        $this->moduleTemplate->getDocHeaderComponent()->disable();
    }
}
