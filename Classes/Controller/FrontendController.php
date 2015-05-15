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
class FrontendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * StorageRepository
	 *
	 * @var \MASK\Mask\Domain\Repository\StorageRepository
	 * @inject
	 */
	protected $storageRepository;

	/**
	 * MaskUtility
	 *
	 * @var \MASK\Mask\Utility\MaskUtility
	 * @inject
	 */
	protected $utility;

	/**
	 * Displays the content in the frontend
	 *
	 * @return void
	 */
	public function contentelementAction() {
		$this->view->setTemplatePathAndFilename($this->settings["file"]);
		$data = $this->configurationManager->getContentObject()->data;
		$this->utility->addFilesToData($data, "tt_content");
		$this->utility->addIrreToData($data);
		$this->view->assign('data', $data);
	}

	/**
	 * Returns the SQL of all elements
	 *
	 * @param array $sqlString
	 * @return array
	 */
	public function addDatabaseTablesDefinition(array $sqlString) {
		$sql = $this->storageRepository->loadSql();
		$mergedSqlString = array_merge($sqlString, $sql);
		return array('sqlString' => $mergedSqlString);
	}

}