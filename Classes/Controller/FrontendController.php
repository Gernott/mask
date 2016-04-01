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
     * Displays the content in the frontend
     *
     * @return void
     */
    public function contentelementAction()
    {
        $this->view->setTemplatePathAndFilename($this->settings["file"]);
        $data = $this->configurationManager->getContentObject()->data;
        $this->inlineHelper->addFilesToData($data, "tt_content");
        $this->inlineHelper->addIrreToData($data);
        $this->view->assign('data', $data);
    }
}
