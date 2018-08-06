<?php

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * ViewHelper for rendering any content element
 * @author Paul Beck
 * @link http://blog.teamgeist-medien.de/2014/01/extbase-fluid-viewhelper-fuer-tt_content-elemente-mit-namespaces.html Source
 *
 */
class ContentViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    protected $escapeOutput = false;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer Object
     */
    protected $cObj;

    /**
     * Parse content element
     *
     * @param int uid of the content element
     * @return string parsed content element
     */
    public function render($uid)
    {
        $conf = array(
            'tables' => 'tt_content',
            'source' => $uid,
            'dontCheckPid' => 1
        );
        return $this->cObj->cObjGetSingle('RECORDS', $conf);
    }

    /**
     * Injects Configuration Manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->cObj = $this->configurationManager->getContentObject();
    }
}
