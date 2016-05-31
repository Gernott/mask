<?php

namespace MASK\Mask\Controller;

/**
 * FrontendController
 */

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
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

        // if there are paths for layouts and partials set, add them to view
        if (!empty($this->extSettings["layouts"])) {
            $layoutRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extSettings["layouts"]);
            $this->view->setLayoutRootPaths(array($layoutRootPath));
        }
        if (!empty($this->extSettings["partials"])) {
            $partialRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extSettings["partials"]);
            $this->view->setPartialRootPaths(array($partialRootPath));
        }
        $this->view->setTemplatePathAndFilename($this->settings["file"]);
        $data = $this->configurationManager->getContentObject()->data;
        $this->inlineHelper->addFilesToData($data, "tt_content");
        $this->inlineHelper->addIrreToData($data);
        $this->view->assign('data', $data);
    }
}
