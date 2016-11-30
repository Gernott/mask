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
        $this->extSettings = $this->settingsService->getFrontendSettings();

        // if there are paths for layouts and partials set, add them to view
        if (!empty($this->extSettings["layouts"])) {
            $this->view->setLayoutRootPaths($this->extSettings["layouts"]);
        }
        if (!empty($this->extSettings["partials"])) {
            $this->view->setPartialRootPaths($this->extSettings["partials"]);
        }
        foreach ($this->extSettings['frontend'] as $templatePath) {
            $fileName = $templatePath . $this->settings["file"];
            if (is_file($fileName)) {
                $this->view->setTemplatePathAndFilename($fileName);
            }
        }
        $data = $this->configurationManager->getContentObject()->data;
        $this->inlineHelper->addFilesToData($data, "tt_content");
        $this->inlineHelper->addIrreToData($data);
        $this->view->assign('data', $data);
    }
}
