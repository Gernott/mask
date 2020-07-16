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

use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 */
class WizardPageController extends WizardController
{
    /**
     * action list
     */
    public function listAction(): void
    {
        $settings = $this->settingsService->get();
        $backendLayouts = $this->backendLayoutRepository->findAll(explode(',', $settings['backendlayout_pids']));
        $this->view->assign('backendLayouts', $backendLayouts);
    }

    /**
     * action new
     */
    public function newAction(): void
    {
        $settings = $this->settingsService->get();
        $backendLayouts = $this->backendLayoutRepository->findAll(explode(',', $settings['backendlayout_pids']));
        $this->view->assign('backendLayouts', $backendLayouts);
    }

    /**
     * action create
     *
     * @param array $storage
     * @throws StopActionException
     */
    public function createAction($storage): void
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
     */
    public function editAction($layoutIdentifier = null): void
    {
        $settings = $this->settingsService->get();
        $layout = $this->backendLayoutRepository->findByIdentifier(
            $layoutIdentifier,
            explode(',', $settings['backendlayout_pids'])
        );

        if ($layout) {
            $storage = $this->storageRepository->loadElement('pages', $layoutIdentifier);
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
     * @throws StopActionException
     */
    public function updateAction($storage): void
    {
        $this->storageRepository->update($storage);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.page.updatedpage', 'mask'));
        $this->redirectByAction();
    }

    /**
     * action delete
     *
     * @param array $storage
     * @throws StopActionException
     */
    public function deleteAction(array $storage): void
    {
        $this->redirect('list');
    }
}
