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

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

class EditLinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * @var bool
     */
    protected $doEdit = true;

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('element', 'array', '', true);
    }

    /**
     * returning a EditLink-Tag for TYPO3 Backend
     * @throws RouteNotFoundException
     */
    public function render(): string
    {
        trigger_error('The MASK\Mask\ViewHelpers\EditLinkViewHelper will be removed in Mask v8. Please use be:link.editRecord instead.', E_USER_DEPRECATED);
        $element = $this->arguments['element'];

        if ($this->doEdit && $this->getBackendUser()->recordEditAccessInternals('tt_content', $element)) {
            $urlParameters = [
                'edit' => [
                    'tt_content' => [
                        $element['uid'] => 'edit',
                    ],
                ],
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            ];
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = $uriBuilder->buildUriFromRoute('record_edit', $urlParameters);
            $this->tag->addAttribute('href', (string)$uri);
        }

        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
