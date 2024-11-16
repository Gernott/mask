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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;

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
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $moduleTemplate->getDocHeaderComponent()->disable();
        $moduleTemplate->assign('settingsUrl', $this->uriBuilder->buildUriFromRoute('tools_toolssettings'));
        $moduleTemplate->assign('iconSize', 'medium');
        $this->pageRenderer->loadJavaScriptModule('@mask/mask');
        $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/mask.css');
        if ((new Typo3Version())->getMajorVersion() === 12) {
            // No support for dark-mode in v12
            $this->pageRenderer->addCssFile('EXT:mask/Resources/Public/Styles/disable-dark-mode.css');
        }
        return $moduleTemplate->renderResponse('Wizard/Main');
    }
}
