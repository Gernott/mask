<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace MASK\Mask\Domain\Repository;

use Doctrine\DBAL\FetchMode;
use Exception;
use MASK\Mask\Backend\BackendLayoutView;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Repository for \TYPO3\CMS\Extbase\Domain\Model\BackendLayout.
 */
class BackendLayoutRepository extends Repository
{

    /**
     * @var BackendLayoutView
     */
    protected $backendLayoutView;

    /**
     * Initializes the repository.
     */
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
        $this->backendLayoutView = GeneralUtility::makeInstance(BackendLayoutView::class);
    }

    /**
     * Returns all backendlayouts defined, database and pageTs
     * @param array $pageTsPids
     * @return array
     */
    public function findAll($pageTsPids = []): array
    {
        $backendLayouts = [];

        // search all the pids for backend layouts defined in the pageTS
        foreach ($pageTsPids as $pid) {
            $pageTsConfig = (array)BackendUtility::getPagesTSconfig($pid);
            $dataProviderContext = $this->backendLayoutView->createDataProviderContext()->setPageTsConfig($pageTsConfig);
            $backendLayoutCollections = $this->backendLayoutView->getDataProviderCollection()->getBackendLayoutCollections($dataProviderContext);
            foreach ($backendLayoutCollections['default']->getAll() as $backendLayout) {
                $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
            }
            foreach ($backendLayoutCollections['pagets']->getAll() as $backendLayout) {
                if (GeneralUtility::isFirstPartOfStr($backendLayout->getTitle(), 'LLL:')) {
                    $backendLayout->setTitle(LocalizationUtility::translate($backendLayout->getTitle()));
                }
                $iconPath = $backendLayout->getIconPath();
                if ($iconPath !== '') {
                    $absoluteFilePath = GeneralUtility::getFileAbsFileName($iconPath);
                    if (empty($absoluteFilePath) || !is_file($absoluteFilePath)) {
                        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
                        $iconConfig = $iconRegistry->getIconConfigurationByIdentifier($iconPath);
                        $backendLayout->setIconPath($iconConfig['options']['source']);
                    }
                }
                $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
            }
        }

        // also search in the database for backendlayouts
        $databaseBackendLayouts = parent::findAll();
        /** @var \MASK\Mask\Domain\Model\BackendLayout $layout */
        foreach ($databaseBackendLayouts as $layout) {
            $backendLayout = new BackendLayout(
                $layout->getUid(),
                $layout->getTitle(),
                [
                    'backend_layout.' => [
                        'rows.' => []
                    ]
                ]
            );
            $backendLayout->setDescription($layout->getDescription());
            $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
        }
        return $backendLayouts;
    }

    /**
     * @param $pid
     * @return bool
     * @throws Exception
     */
    public function findIdentifierByPid($pid): ?string
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

        $backend_layout = $data['backend_layout'];
        $backend_layout_next_level = $data['backend_layout_next_level'];
        if (!empty($backend_layout)) { // If backend_layout is set on current page
            return $backend_layout;
        }

        if (!empty($backend_layout_next_level)) { // If backend_layout_next_level is set on current page
            return $backend_layout_next_level;
        }
        $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pid);
        try {
            $rootline = $rootLineUtility->get();
        } catch (RuntimeException $ex) {
            $rootline = [];
        }
        foreach ($rootline as $page) {
            if (!empty($page['backend_layout_next_level'])) {
                return $page['backend_layout_next_level'];
            }
        }
        return null;
    }

    /**
     * Returns a backendlayout or null, if non found
     *
     * @param $identifier
     * @param array $pageTsPids
     * @return BackendLayout|null
     */
    public function findByIdentifier($identifier, $pageTsPids = []): ?BackendLayout
    {
        $backendLayouts = $this->findAll($pageTsPids);
        return $backendLayouts[$identifier] ?? null;
    }
}
