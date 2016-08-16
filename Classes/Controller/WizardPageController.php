<?php

namespace MASK\Mask\Controller;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class WizardPageController extends \MASK\Mask\Controller\WizardController
{

    /**
     * @var TYPO3\CMS\Backend\View\BackendLayoutView
     */
    protected $backendLayoutView;

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;
    protected $backendLayoutConfiguration = array('items' => array(0 => array(0 => '', 1 => '',), 1 => array(0 => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.backend_layout.none', 1 => -1,),), 'config' => array('type' => 'select', 'renderType' => 'selectSingle', 'items' => array(0 => array(0 => '', 1 => '',), 1 => array(0 => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.backend_layout.none', 1 => -1,),), 'itemsProcFunc' => 'TYPO3\\CMS\\Backend\\View\\BackendLayoutView->addBackendLayoutItems', 'showIconTable' => true, 'selicon_cols' => 5, 'size' => 1, 'maxitems' => 1,), 'TSconfig' => NULL, 'table' => 'pages', 'row' => array('uid' => '1', 'pid' => '0', 't3ver_oid' => '0', 't3ver_id' => '0', 't3ver_wsid' => '0', 't3ver_label' => '', 't3ver_state' => '0', 't3ver_stage' => '0', 't3ver_count' => '0', 't3ver_tstamp' => '0', 't3ver_move_id' => '0', 't3_origuid' => '0', 'tstamp' => '1459502247', 'sorting' => '256', 'deleted' => '0', 'perms_userid' => '1', 'perms_groupid' => '0', 'perms_user' => '31', 'perms_group' => '27', 'perms_everybody' => '0', 'editlock' => '0', 'crdate' => '1453128937', 'cruser_id' => '1', 'hidden' => '0', 'title' => 'Seite', 'doktype' => array(0 => '1',), 'TSconfig' => '', 'is_siteroot' => '0', 'php_tree_stop' => '0', 'tx_impexp_origuid' => '0', 'url' => '', 'starttime' => '0', 'endtime' => '0', 'urltype' => '1', 'shortcut' => '', 'shortcut_mode' => '0', 'no_cache' => '0', 'fe_group' => array(), 'subtitle' => '', 'layout' => array(0 => '0',), 'url_scheme' => array(0 => '0',), 'target' => '', 'media' => '0', 'lastUpdated' => '0', 'keywords' => '', 'cache_timeout' => array(0 => '0',), 'cache_tags' => '', 'newUntil' => '0', 'description' => '', 'no_search' => '0', 'SYS_LASTCHANGED' => '1462536481', 'abstract' => '', 'module' => array(), 'extendToSubpages' => '0', 'author' => '', 'author_email' => '', 'nav_title' => '', 'nav_hide' => '0', 'content_from_pid' => '', 'mount_pid' => '', 'mount_pid_ol' => '0', 'alias' => '', 'l18n_cfg' => '0', 'fe_login_mode' => array(0 => '0',), 'backend_layout' => '1', 'backend_layout_next_level' => '1', 'tsconfig_includes' => '', 'categories' => '0', 'tx_wppages_navicon' => 'nav de', 'tx_wppages_navicon_hover' => NULL, 'tx_wppages_menu_background_color' => '0', 'tx_realurl_pathsegment' => '', 'tx_realurl_pathoverride' => '0', 'tx_realurl_exclude' => '0', 'tx_realurl_nocache' => '0', 'tx_mask_asdfasfd' => '0', 'tx_mask_asdfasd' => '0000-00-00 00:00:00', 'tx_mask_ssssssssssssss' => NULL, 'tx_mask_asdfasdf' => '0', 'tx_mask_ddddddddddadsfasdf' => '0', 'tx_mask_sdfsdfadfadfasdfasfsdafasdfasfasfasdfasdf' => '0', 'tx_mask_sdfghj' => '0.00', 'tx_mask_adsfaf' => NULL, 'tx_mask_image_mobile' => '0', 'tx_mask_image' => '0', 'tx_mask_theme_text' => '', 'tx_mask_checkbox' => '0',), 'field' => 'backend_layout',);

    public function initializeAction()
    {
        $this->backendLayoutView = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\View\BackendLayoutView::class);
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
//        $pageId = $this->backendLayoutView->determinePageId($this->backendLayoutConfiguration['table'], $this->backendLayoutConfiguration['row']);
//        $pageTsConfig = (array) \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pageId);
//
//        $dataProviderContext = $this->backendLayoutView->createDataProviderContext()
//            ->setPageId($pageId)
//            ->setData($this->backendLayoutConfiguration['row'])
//            ->setTableName($this->backendLayoutConfiguration['table'])
//            ->setFieldName($this->backendLayoutConfiguration['field'])
//            ->setPageTsConfig($pageTsConfig);
//
//        $backendLayoutCollections = $this->backendLayoutView->getDataProviderCollection()->getBackendLayoutCollections($dataProviderContext);
//
//        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($backendLayoutCollections);
//        exit();
        $backendLayouts = $this->backendLayoutRepository->findAll();
        $this->view->assign('backendLayouts', $backendLayouts);
    }

    /**
     * Initializes data providers
     *
     * @return void
     */
    protected function initializeDataProviderCollection()
    {
        /** @var $dataProviderCollection \TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection */
        $dataProviderCollection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection::class
        );

        $dataProviderCollection->add(
            'default', \TYPO3\CMS\Backend\View\BackendLayout\DefaultDataProvider::class
        );

        if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider'])) {
            $dataProviders = (array) $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider'];
            foreach ($dataProviders as $identifier => $className) {
                $dataProviderCollection->add($identifier, $className);
            }
        }

        $this->setDataProviderCollection($dataProviderCollection);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $backendLayouts = $this->backendLayoutRepository->findAll();
        $this->view->assign('backendLayouts', $backendLayouts);
    }

    /**
     * action create
     *
     * @param array $storage
     * @return void
     */
    public function createAction($storage)
    {
        $this->storageRepository->add($storage);
        $this->generateAction();
        $this->addFlashMessage('Your new Content-Element was created.');
        $this->redirect('list');
    }

    /**
     * action edit
     *
     * @param string $layoutIdentifier
     * @return void
     */
    public function editAction($layoutIdentifier)
    {
        $layout = $this->backendLayoutRepository->findByIdentifier($layoutIdentifier);
        if ($layout) {
            $storage = $this->storageRepository->loadElement("pages", $layoutIdentifier);
            $this->prepareStorage($storage);
            $this->view->assign('backendLayout', $layout);
            $this->view->assign('storage', $storage);
            $this->view->assign('editMode', 1);
        }
    }

    /**
     * action update
     *
     * @param array $storage
     * @return void
     */
    public function updateAction($storage)
    {
        $this->storageRepository->update($storage);
        $this->generateAction();
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.page.updatedpage', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action delete
     *
     * @param array $storage
     * @return void
     */
    public function deleteAction(array $storage)
    {
        $this->storageRepository->remove($storage);
        $this->addFlashMessage('Your Page was removed.');
        $this->redirect('list');
    }

    /**
     * @param \TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection $dataProviderCollection
     */
    public function setDataProviderCollection(\TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection $dataProviderCollection)
    {
        $this->dataProviderCollection = $dataProviderCollection;
    }

    /**
     * @return \TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection
     */
    public function getDataProviderCollection()
    {
        return $this->dataProviderCollection;
    }
}
