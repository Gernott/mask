<?php
declare(strict_types=1);

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
 *  the Free Software Foundation; either version 2 of the License, or
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

use MASK\Mask\Domain\Repository\IconRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class WizardContentController extends WizardController
{

    /**
     * StorageRepository
     *
     * @var StorageRepository
     * @Inject()
     */
    protected $storageRepository;

    /**
     * IconRepository
     *
     * @var IconRepository
     * @Inject()
     */
    protected $iconRepository;

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): void
    {
        $this->checkFolders();
        $this->view->assign('missingFolders', $this->missingFolders);
        $this->view->assign('storages', $this->storageRepository->load());
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction(): void
    {
        $icons = $this->iconRepository->findAll();
        $this->view->assign('icons', $icons);
    }

    /**
     * action create
     *
     * @param array $storage
     * @return void
     * @throws StopActionException
     */
    public function createAction($storage): void
    {
        $this->storageRepository->add($storage);
        $this->generateAction();
        $html = $this->htmlCodeGenerator->generateHtml($storage['elements']['key'], 'tt_content');
        $this->saveHtml($storage['elements']['key'], $html);
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.newcontentelement', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action edit
     *
     * @param string $type
     * @param string $key
     * @return void
     */
    public function editAction($type, $key): void
    {
        $storage = $this->storageRepository->loadElement($type, $key);
        $icons = $this->iconRepository->findAll();
        $this->prepareStorage($storage);
        $this->view->assign('storage', $storage);
        $this->view->assign('icons', $icons);
        $this->view->assign('editMode', 1);
    }

    /**
     * action update
     *
     * @param array $storage
     * @return void
     * @throws StopActionException
     */
    public function updateAction($storage): void
    {
        $this->storageRepository->update($storage);
        $this->generateAction();
        $html = $this->htmlCodeGenerator->generateHtml($storage['elements']['key'], 'tt_content');
        $this->saveHtml($storage['elements']['key'], $html);
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.updatedcontentelement', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action delete
     *
     * @param string $key
     * @param string $type
     * @return void
     * @throws StopActionException
     */
    public function deleteAction($key, $type): void
    {
        $this->storageRepository->remove($type, $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
    }

    /**
     * action purge
     *
     * @param string $key
     * @param string $type
     * @return void
     * @throws StopActionException
     */
    public function purgeAction($key, $type): void
    {
        $this->deleteHtml($key);
        $this->storageRepository->remove($type, $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
    }

    /**
     * action hide
     *
     * @param string $key
     * @return void
     * @throws StopActionException
     */
    public function hideAction($key): void
    {
        $this->storageRepository->hide('tt_content', $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.hiddencontentelement', 'mask'));
        $this->redirect('list','Wizard');
    }

    /**
     * action activate
     *
     * @param string $key
     * @return void
     * @throws StopActionException
     */
    public function activateAction($key): void
    {
        $this->storageRepository->activate('tt_content', $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.activatedcontentelement', 'mask'));
        $this->redirect('list','Wizard');
    }

    /**
     * Deletes Fluid html, if file exists
     *
     * @param string $key
     */
    protected function deleteHtml($key): void
    {
        $paths = [];
        $paths[] = MaskUtility::getTemplatePath($this->extSettings, $key);
        $paths[] = MaskUtility::getTemplatePath($this->extSettings, $key, false, $this->extSettings['backend']);
        foreach ($paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Create single Fluid html
     *
     * @param string $key
     * @throws StopActionException
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function createHtmlAction($key): void
    {
        $html = $this->htmlCodeGenerator->generateHtml($key, 'tt_content');
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.createdHtml', 'mask'));
        $this->saveHtml($key, $html);
        $this->redirect('list', 'Wizard');
    }

}
