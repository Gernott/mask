<?php
declare(strict_types=1);

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
     * @var boolean
     */
    protected $doEdit = 1;

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    public function initializeArguments()
    {
        $this->registerArgument('element', 'array', '', true);
    }

    /**
     * returning a EditLink-Tag for TYPO3 Backend
     * @return mixed
     * @throws RouteNotFoundException
     */
    public function render()
    {
        $element = $this->arguments['element'];

        if ($this->doEdit && $this->getBackendUser()->recordEditAccessInternals('tt_content', $element)) {
            $urlParameters = [
                'edit' => [
                    'tt_content' => [
                        $element['uid'] => 'edit'
                    ]
                ],
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
            ];
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = $uriBuilder->buildUriFromRoute('record_edit', $urlParameters);
            $this->tag->addAttribute('href', $uri);
        }

        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
