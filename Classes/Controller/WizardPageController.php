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

namespace MASK\Mask\Controller;

use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
        $json = $this->storageRepository->add($storage);
        $this->storageRepository->persist($json);
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
            $this->prepareStorage($storage, $layoutIdentifier);
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
