<?php
namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
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

    /**
     * returning a EditLink-Tag for TYPO3 Backend
     * @param array $element
     * @return mixed
     */
    public function render($element) {
        if ($this->doEdit && $this->getBackendUser()->recordEditAccessInternals('tt_content', $element)) {
            $urlParameters = [
                'edit' => [
                    'tt_content' => [
                        $element['uid'] => 'edit'
                    ]
                ],
                'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI')
            ];
            $uri = BackendUtility::getModuleUrl('record_edit', $urlParameters);

            $this->tag->addAttribute('href', $uri);
        }

        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(TRUE);

        return $this->tag->render();
    }
}
