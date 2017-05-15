<?php namespace MASK\Mask\Domain\Repository;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Repository for \TYPO3\CMS\Extbase\Domain\Model\BackendLayout.
 *
 * @api
 */
class BackendLayoutRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

   /**
	* @var MASK\Mask\Backend\BackendLayoutView
	*/
   protected $backendLayoutView;

   /**
	* Initializes the repository.
	*
	* @return void
	*/
   public function initializeObject()
   {
	  /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
	  $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
	  $querySettings->setRespectStoragePage(FALSE);
	  $this->setDefaultQuerySettings($querySettings);
	  $this->backendLayoutView = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Backend\BackendLayoutView::class);
   }

   /**
	* Returns all backendlayouts defined, database and pageTs
	* @param array $pageTsPids
	* @return array
	*/
   public function findAll($pageTsPids = array())
   {
	  $backendLayouts = array();

	  // search all the pids for backend layouts defined in the pageTS
	  foreach ($pageTsPids as $pid) {
		 $pageTsConfig = (array) \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid);
		 $dataProviderContext = $this->backendLayoutView->createDataProviderContext()->setPageTsConfig($pageTsConfig);
		 $backendLayoutCollections = $this->backendLayoutView->getDataProviderCollection()->getBackendLayoutCollections($dataProviderContext);
		 foreach ($backendLayoutCollections["default"]->getAll() as $backendLayout) {
			$backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
		 }
		 foreach ($backendLayoutCollections["pagets"]->getAll() as $backendLayout) {
			$backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
		 }
	  }

	  // also search in the database for backendlayouts
	  $databaseBackendLayouts = parent::findAll();
	  foreach ($databaseBackendLayouts as $layout) {
		 $backendLayout = new \TYPO3\CMS\Backend\View\BackendLayout\BackendLayout($layout->getUid(), $layout->getTitle(), "");
		 $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
	  }
	  return $backendLayouts;
   }

   /**
	* Returns a backendlayout or null, if non found
	*
	* @return \TYPO3\CMS\Backend\View\BackendLayout\BackendLayout
	*/
   public function findByIdentifier($identifier, $pageTsPids = array())
   {
	  $backendLayouts = $this->findAll($pageTsPids);
	  if (isset($backendLayouts[$identifier])) {
		 return $backendLayouts[$identifier];
	  } else {
		 return null;
	  }
   }
}
