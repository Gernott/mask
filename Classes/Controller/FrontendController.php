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

    /**
     * Returns the SQL of all elements
     *
     * @param array $sqlString
     * @return array
     */
    public function addDatabaseTablesDefinition(array $sqlString)
    {
        $sql = $this->storageRepository->loadSql();
        $mergedSqlString = array_merge($sqlString, $sql);
        return array('sqlString' => $mergedSqlString);
    }
}
