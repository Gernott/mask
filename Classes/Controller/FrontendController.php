<?php

namespace MASK\Mask\Controller;

/**
 * FrontendController
 */
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class FrontendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;

    /**
     * InlineHelper
     *
     * @var \MASK\Mask\Helper\InlineHelper
     * @inject
     */
    protected $inlineHelper;

    /**
     * settingsService
     *
     * @var \MASK\Mask\Domain\Service\SettingsService
     * @inject
     */
    protected $settingsService;

    /**
     * extension settings
     * @var array
     */
    protected $extSettings;

    /**
     * Displays the content in the frontend
     *
     * @return void
     */
    public function contentelementAction()
    {
        // load extension settings
        $this->extSettings = $this->settingsService->get();

        // get framework configuration
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        // if there are paths for layouts and partials set, add them to view
        if (!empty($this->extSettings["layouts"])) {
            $viewLayoutRootPaths = $this->getViewProperty($extbaseFrameworkConfiguration, 'layoutRootPaths');
            $extSettingsLayoutRootPath = GeneralUtility::getFileAbsFileName($this->extSettings["layouts"]);

            $this->view->setLayoutRootPaths(array_replace($viewLayoutRootPaths, [$extSettingsLayoutRootPath]));
        }
        if (!empty($this->extSettings["partials"])) {
            $viewPartialRootPaths = $this->getViewProperty($extbaseFrameworkConfiguration, 'partialRootPaths');
            $extSettingsPartialRootPath = GeneralUtility::getFileAbsFileName($this->extSettings["partials"]);

            $this->view->setPartialRootPaths(array_replace($viewPartialRootPaths, [$extSettingsPartialRootPath]));
        }
        $this->view->setTemplatePathAndFilename($this->settings["file"]);
        $data = $this->configurationManager->getContentObject()->data;
        $this->inlineHelper->addFilesToData($data, "tt_content");
        $this->inlineHelper->addIrreToData($data);
        $this->view->assign('data', $data);
    }
}
