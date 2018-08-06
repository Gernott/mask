<?php namespace MASK\Mask\Controller;

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
class WizardPageController extends \MASK\Mask\Controller\WizardController
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $settings = $this->settingsService->get();
        $backendLayouts = $this->backendLayoutRepository->findAll(explode(",", $settings['backendlayout_pids']));
        $this->view->assign('backendLayouts', $backendLayouts);
    }

    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        $settings = $this->settingsService->get();
        $backendLayouts = $this->backendLayoutRepository->findAll(explode(",", $settings['backendlayout_pids']));
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
    public function editAction($layoutIdentifier = null)
    {
        $settings = $this->settingsService->get();
        $layout = $this->backendLayoutRepository->findByIdentifier($layoutIdentifier,
            explode(",", $settings['backendlayout_pids']));

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
        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.page.updatedpage',
            'mask'));
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
}
