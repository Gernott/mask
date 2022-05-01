<?php

declare(strict_types=1);

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

use MASK\Mask\Backend\BackendLayoutView;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class BackendLayoutRepository
{
    /**
     * @var QueryBuilder
     */
    protected $backendLayoutQueryBuilder;

    /**
     * @var QueryBuilder
     */
    protected $pagesQueryBuilder;

    public function __construct(QueryBuilder $backendLayoutQueryBuilder, QueryBuilder $pagesQueryBuilder)
    {
        $this->backendLayoutQueryBuilder = $backendLayoutQueryBuilder;
        // We don't want any restrictions here. In preview mode, we need to be able to retrieve the page nevertheless.
        $pagesQueryBuilder->setRestrictions($pagesQueryBuilder->getRestrictions()->removeAll());
        $this->pagesQueryBuilder = $pagesQueryBuilder;
    }

    /**
     * Returns all backendlayouts defined, database and pageTs
     */
    public function findAll(array $pageTsPids = []): array
    {
        $backendLayoutView = GeneralUtility::makeInstance(BackendLayoutView::class);
        $backendLayouts = [];

        // search all the pids for backend layouts defined in the pageTS
        foreach ($pageTsPids as $pid) {
            $pageTsConfig = BackendUtility::getPagesTSconfig($pid);
            $dataProviderContext = $backendLayoutView->createDataProviderContext()
                ->setPageId(0)
                ->setFieldName('backend_layout')
                ->setTableName('backend_layout')
                ->setData([])
                ->setPageTsConfig($pageTsConfig);
            $backendLayoutCollections = $backendLayoutView->getDataProviderCollection()->getBackendLayoutCollections($dataProviderContext);
            foreach ($backendLayoutCollections['default']->getAll() as $backendLayout) {
                $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
            }
            foreach ($backendLayoutCollections['pagets']->getAll() as $backendLayout) {
                $backendLayout->setTitle($this->getLanguageService()->sL($backendLayout->getTitle()));
                $iconPath = $backendLayout->getIconPath();
                if ($iconPath !== '') {
                    $absoluteFilePath = GeneralUtility::getFileAbsFileName($iconPath);
                    // If the absolute path could not be determined, or the file does not exist, check for icon identifier.
                    if ($absoluteFilePath === '' || !is_file($absoluteFilePath)) {
                        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
                        if ($iconRegistry->isRegistered($iconPath)) {
                            $iconConfig = $iconRegistry->getIconConfigurationByIdentifier($iconPath);
                            $backendLayout->setIconPath($iconConfig['options']['source']);
                        } else {
                            // Icon path provided by user is invalid. Reset to empty string.
                            $backendLayout->setIconPath('');
                        }
                    }
                }
                $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
            }
        }

        // also search in the database for backendlayouts
        $statement = $this->backendLayoutQueryBuilder
            ->from('backend_layout')
            ->select('uid', 'title', 'description')
            ->execute();

        if (method_exists($statement, 'fetchAllAssociative')) {
            $layouts = $statement->fetchAllAssociative();
        } else {
            $layouts = $statement->fetchAll();
        }

        foreach ($layouts as $layout) {
            $backendLayout = new BackendLayout(
                $layout['uid'],
                $layout['title'],
                [
                    'backend_layout.' => [
                        'rows.' => [],
                    ],
                ]
            );
            $backendLayout->setDescription($layout['description']);
            $backendLayouts[$backendLayout->getIdentifier()] = $backendLayout;
        }
        return $backendLayouts;
    }

    /**
     * Finds the current backend layout identifier
     */
    public function findIdentifierByPid(int $pid): ?string
    {
        $statement = $this->pagesQueryBuilder
            ->from('pages')
            ->select('backend_layout', 'backend_layout_next_level', 'uid')
            ->where($this->pagesQueryBuilder->expr()->eq('uid', $this->pagesQueryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)))
            ->execute();

        if (method_exists($statement, 'fetchAssociative')) {
            $requestPage = $statement->fetchAssociative();
        } else {
            $requestPage = $statement->fetch();
        }

        $backend_layout = $requestPage['backend_layout'];
        $backend_layout_next_level = $requestPage['backend_layout_next_level'];
        // If backend_layout is set on current page
        if (!empty($backend_layout)) {
            return $backend_layout;
        }

        // If backend_layout_next_level is set on current page
        if (!empty($backend_layout_next_level)) {
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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
