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

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class WizardContentController extends \MASK\Mask\Controller\WizardController
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;

    /**
     * IconRepository
     *
     * @var \MASK\Mask\Domain\Repository\IconRepository
     * @inject
     */
    protected $iconRepository;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $messages = $this->checkFolders();
        $missingFolders = FALSE;
        if (count($messages) > 0) {
            $missingFolders = TRUE;
        }
        $this->view->assign('messages', $messages);
        $this->view->assign('missingFolders', $missingFolders);
        $storages = $this->storageRepository->load();
        $this->view->assign('storages', $storages);
    }

    /**
     * action new
     *
     * @dontvalidate $newContent
     * @return void
     */
    public function newAction()
    {
        $icons = $this->iconRepository->findAll();
        $this->view->assign('icons', $icons);
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
        $html = $this->htmlCodeGenerator->generateHtml($storage["elements"]["key"]);
        $this->saveHtml($storage["elements"]["key"], $html);
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.newcontentelement', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action edit
     *
     * @param string $type
     * @param string $key
     * @return void
     */
    public function editAction($type, $key)
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
     */
    public function updateAction($storage)
    {
        $this->storageRepository->update($storage);
        $this->generateAction();
        $html = $this->htmlCodeGenerator->generateHtml($storage["elements"]["key"]);
        $this->saveHtml($storage["elements"]["key"], $html);
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.updatedcontentelement', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action delete
     *
     * @param string $key
     * @param string $type
     * @return void
     */
    public function deleteAction($key, $type)
    {
        $this->storageRepository->remove($type, $key);
        $this->generateAction();
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list');
    }

    /**
     * action purge
     *
     * @param string $key
     * @param string $type
     * @return void
     */
    public function purgeAction($key, $type)
    {
        $this->deleteHtml($key);
        $this->storageRepository->remove($type, $key);
        $this->generateAction();
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list');
    }

    /**
     * action hide
     *
     * @param string $key
     * @return void
     */
    public function hideAction($key)
    {
        $this->storageRepository->hide("tt_content", $key);
        $this->generateAction();
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.hiddencontentelement', 'mask'));
        $this->redirect('list');
    }

    /**
     * action activate
     *
     * @param string $key
     * @return void
     */
    public function activateAction($key)
    {
        $this->storageRepository->activate("tt_content", $key);
        $this->generateAction();
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.content.activatedcontentelement', 'mask'));
        $this->redirect('list');
    }

    /**
     * Deletes Fluid html, if file exists
     *
     * @param string $key
     * @param string $html
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function deleteHtml($key)
    {
        if (file_exists(PATH_site . $this->extSettings["content"] . $key . ".html")) {
            unlink(PATH_site . $this->extSettings["content"] . $key . ".html");
        }
        if (file_exists(PATH_site . $this->extSettings["backend"] . $key . ".html")) {
            unlink(PATH_site . $this->extSettings["backend"] . $key . ".html");
        }
    }
}
