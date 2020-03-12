<?php
declare(strict_types=1);

namespace MASK\Mask\ViewHelpers;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper for rendering any content element
 * @author Paul Beck
 * @link http://blog.teamgeist-medien.de/2014/01/extbase-fluid-viewhelper-fuer-tt_content-elemente-mit-namespaces.html Source
 *
 */
class ContentViewHelper extends AbstractViewHelper
{

    protected $escapeOutput = false;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var ContentObjectRenderer Object
     */
    protected $cObj;

    public function initializeArguments(): void
    {
        $this->registerArgument('uid', 'integer', 'Uid of the content element', true);
    }

    /**
     * Parse content element
     *
     * @return string parsed content element
     */
    public function render(): string
    {
        $conf = array(
            'tables' => 'tt_content',
            'source' => $this->arguments['uid'],
            'dontCheckPid' => 1
        );
        return $this->cObj->cObjGetSingle('RECORDS', $conf);
    }

    /**
     * Injects Configuration Manager
     *
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(
        ConfigurationManagerInterface $configurationManager
    ): void {
        $this->configurationManager = $configurationManager;
        $this->cObj = $this->configurationManager->getContentObject();
    }
}
