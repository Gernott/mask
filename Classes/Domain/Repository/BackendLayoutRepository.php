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

use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

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
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
        $this->backendLayoutView = GeneralUtility::makeInstance(\MASK\Mask\Backend\BackendLayoutView::class);
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
            $pageTsConfig = (array)\TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid);
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
            $backendLayout = new \TYPO3\CMS\Backend\View\BackendLayout\BackendLayout($layout->getUid(),
                $layout->getTitle(), "");
            if ($layout->getIcon()) {
                $backendLayout->setIconPath('/uploads/media/' . $layout->getIcon());
            }
            $backendLayout->setDescription($layout->getDescription());
            $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
        }
        return $backendLayouts;
    }


    /**
     * @param $pid
     * @return bool
     * @throws \Exception
     */
    public function findIdentifierByPid($pid)
    {
        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->from('pages')
            ->select('backend_layout', 'backend_layout_next_level', 'uid')
            ->where('uid = :pid')
            ->setParameter('pid', $pid);

        $statement = $queryBuilder->execute();
        $data = $statement->fetch(FetchMode::ASSOCIATIVE);
        $statement->closeCursor();

        $backend_layout = $data["backend_layout"];
        $backend_layout_next_level = $data["backend_layout_next_level"];
        if ($backend_layout !== "") { // If backend_layout is set on current page
            return $backend_layout;
        } elseif ($backend_layout_next_level !== "") { // If backend_layout_next_level is set on current page
            return $backend_layout_next_level;
        } else { // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
            $sysPage = GeneralUtility::makeInstance(PageRepository::class);
            try {
                $rootline = $sysPage->getRootLine($pid, '');
            } catch (\RuntimeException $ex) {
                $rootline = [];
            }
            foreach ($rootline as $page) {
                if ($page["backend_layout_next_level"] !== "") {
                    return $page["backend_layout_next_level"];
                }
            }
        }
        return null;
    }


    /**
     * Returns a backendlayout or null, if non found
     *
     * @param $identifier
     * @param array $pageTsPids
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
