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

use MASK\Mask\Domain\Repository\IconRepository;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class WizardContentController extends WizardController
{
    /**
     * IconRepository
     *
     * @var IconRepository
     */
    protected $iconRepository;

    /**
     * @param IconRepository $iconRepository
     */
    public function injectIconRepository(IconRepository $iconRepository)
    {
        $this->iconRepository = $iconRepository;
    }

    /**
     * action new
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
     * @throws StopActionException
     */
    public function createAction($storage): void
    {
        $json = $this->storageRepository->add($storage);
        $this->storageRepository->persist($json);
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
     */
    public function editAction($type, $key): void
    {
        $storage = $this->storageRepository->loadElement($type, $key);
        $icons = $this->iconRepository->findAll();
        $this->prepareStorage($storage, $key);
        $this->view->assign('storage', $storage);
        $this->view->assign('icons', $icons);
        $this->view->assign('editMode', 1);
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
     * @throws StopActionException
     */
    public function deleteAction($key, $type): void
    {
        $this->storageRepository->persist($this->storageRepository->remove($type, $key));
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
    }

    /**
     * action purge
     *
     * @param string $key
     * @param string $type
     * @throws StopActionException
     */
    public function purgeAction($key, $type): void
    {
        $this->deleteHtml($key);
        $this->storageRepository->persist($this->storageRepository->remove($type, $key));
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
    }

    /**
     * action hide
     *
     * @param string $key
     * @throws StopActionException
     */
    public function hideAction($key): void
    {
        $this->storageRepository->hide('tt_content', $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.hiddencontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
    }

    /**
     * action activate
     *
     * @param string $key
     * @throws StopActionException
     */
    public function activateAction($key): void
    {
        $this->storageRepository->activate('tt_content', $key);
        $this->generateAction();
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.activatedcontentelement', 'mask'));
        $this->redirect('list', 'Wizard');
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
     */
    protected function createHtmlAction($key): void
    {
        $html = $this->htmlCodeGenerator->generateHtml($key, 'tt_content');
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.createdHtml', 'mask'));
        $this->saveHtml($key, $html);
        $this->redirect('list', 'Wizard');
    }
}
