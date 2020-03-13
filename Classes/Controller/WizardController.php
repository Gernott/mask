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

use MASK\Mask\CodeGenerator\HtmlCodeGenerator;
use MASK\Mask\CodeGenerator\SqlCodeGenerator;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class WizardController extends ActionController
{

    /**
     * @var bool
     */
    protected $dbUpdateNeeded = false;

    /**
     * StorageRepository
     *
     * @var StorageRepository
     * @Inject()
     */
    protected $storageRepository;

    /**
     * BackendLayoutRepository
     *
     * @var BackendLayoutRepository
     * @Inject()
     */
    protected $backendLayoutRepository;

    /**
     * FieldHelper
     *
     * @var FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    /**
     * HtmlCodeGenerator
     *
     * @var HtmlCodeGenerator
     * @Inject()
     */
    protected $htmlCodeGenerator;

    /**
     * SqlCodeGenerator
     *
     * @var SqlCodeGenerator
     * @Inject()
     */
    protected $sqlCodeGenerator;

    /**
     * SettingsService
     *
     * @var SettingsService
     * @Inject()
     */
    protected $settingsService;

    /**
     * settings
     *
     * @var array
     */
    protected $extSettings;

    /**
     * $pathKeys
     *
     * @var array
     */
    protected static $folderPathKeys = [
        'content',
        'layouts',
        'partials',
        'backend',
        'layouts_backend',
        'partials_backend',
        'preview'
    ];

    protected $missingFolders = false;

    /**
     * is called before every action
     */
    public function initializeAction(): void
    {
        $this->extSettings = $this->settingsService->get();
    }

    /**
     * Generates all the necessary files
     * @author Gernot Ploiner <gp@webprofil.at>
     * @author Benjamin Butschell <bb@webprofil.at>
     * @todo clear typoscript cache after generating
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function generateAction(): void
    {
        // Update Database
        $this->sqlCodeGenerator->updateDatabase();

        // Clear system cache to force new TCA caching
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroup('system');
    }

    /**
     * Prepares the storage array for fluid view
     *
     * @param array $storage
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function prepareStorage(&$storage): void
    {
        // Fill storage with additional data before assigning to view
        if ($storage['tca']) {
            foreach ($storage['tca'] as $key => $field) {
                if (is_array($field) && $field['config']['type'] === 'inline') {
                    $storage['tca'][$key]['inlineFields'] = $this->storageRepository->loadInlineFields($key);
                    uasort($storage['tca'][$key]['inlineFields'], static function ($columnA, $columnB) {
                        $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
                        $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
                        return $a - $b;
                    });
                }
            }
        }
    }

    /**
     * Generates Fluid HTML for Contentelements
     *
     * @param string $key
     * @param string $table
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function showHtmlAction($key, $table): void
    {
        $html = $this->htmlCodeGenerator->generateHtml($key, $table);
        $this->view->assign('html', $html);
    }

    /**
     * Saves Fluid HTML for Contentelements, if File not exists
     *
     * @param string $key
     * @param string $html
     * @return bool
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function saveHtml($key, $html): bool
    {
        # fallback to prevent breaking change
        $path = MaskUtility::getTemplatePath($this->extSettings, $key);
        if (file_exists($path)) {
            return false;
        }
        GeneralUtility::writeFile($path, $html);
        return true;
    }

    /**
     * Checks if a key for a field is available
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function checkFieldKey(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $fieldKey = $queryParams['key'];
        $table = $queryParams['table'] ?? 'tt_content';

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $storageRepository = $objectManager->get(StorageRepository::class);

        // check if fieldKey is available for this table
        $isAvailable = true;
        if ($storageRepository->loadField($table, $fieldKey)) {
            $isAvailable = false;
        }

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Checks if a key for an element is available
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function checkElementKey(ServerRequest $request, Response $response): Response
    {
        $elementKey = $request->getQueryParams()['key'];

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $storageRepository = $objectManager->get(StorageRepository::class);

        $isAvailable = true;
        if ($storageRepository->loadElement('tt_content', $elementKey)) {
            $isAvailable = false;
        }

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Redirects the request to the correct view
     * @throws StopActionException
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function redirectByAction(): void
    {
        $params = $this->request->getArguments();
        $formAction = $params['formAction'];
        $arguments = [];
        if ($params['storage']['type'] === 'pages') {
            $arguments['layoutIdentifier'] = $this->backendLayoutRepository->findByIdentifier(
                $params['storage']['elements']['key'],
                explode(',', $this->extSettings['backendlayout_pids'])
            )->getIdentifier();
        } else {
            $arguments['key'] = $params['storage']['elements']['key'];
            $arguments['type'] = $params['storage']['type'];
        }
        if (key_exists('save', $formAction)) {
            $this->redirect('edit', null, null, $arguments);
        } else {
            if (key_exists('saveAndExit', $formAction)) {
                $this->redirect('list');
            }
        }
    }

    /**
     * Check, if folders from extensionmanager-settings are existing
     *
     * @return void
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function checkFolders(): void
    {
        foreach (self::$folderPathKeys as $key) {
            $this->checkFolder($this->extSettings[$key], 'tx_mask.all.error.missingfolder');
        }
        $this->checkFolder($this->extSettings['json'], 'tx_mask.all.error.missingjson');
    }

    /**
     * Creates missing folders that are needed for the use of mask
     * @return bool $success
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function createMissingFolders(): bool
    {
        $success = true;
        foreach (self::$folderPathKeys as $key) {
            $success = $success && $this->createFolder($this->extSettings[$key]);
        }
        $success = $success && $this->createFile($this->extSettings['json']);
        return $success;
    }

    /**
     * action creates missing folders
     * @throws StopActionException
     * @author Benjamin Butschell <bb@webprofil.at>
     * @noinspection PhpUnused
     */
    public function createMissingFoldersAction(): void
    {
        if ($this->createMissingFolders()) {
            $this->addFlashMessage(LocalizationUtility::translate('tx_mask.all.createdmissingfolders', 'mask'));
        }
        $this->redirect('list');
    }

    /**
     * @param $path
     * @return bool
     */
    protected function createFolder($path): bool
    {
        $success = true;
        $path = MaskUtility::getFileAbsFileName($path);
        if (!file_exists($path)) {
            $success = mkdir(
                $path,
                octdec($GLOBALS['TYPO3_CONF_VARS']['SYS']['folderCreateMask']),
                true
            );
        }
        return $success;
    }

    /**
     * @param $path
     * @return bool
     */
    protected function createFile($path): bool
    {
        $success = true;
        $path = MaskUtility::getFileAbsFileName($path);
        if (!file_exists($path)) {
            $success = $this->createFolder(dirname($path));
            $this->storageRepository->write([]);
        }
        return $success;
    }

    /**
     * @param string $path
     * @param string $translationKey
     * @return void
     */
    protected function checkFolder($path, $translationKey = 'tx_mask.all.error.missingjson'): void
    {
        if (!file_exists(MaskUtility::getFileAbsFileName($path))) {
            $this->missingFolders = true;
            $this->addFlashMessage(
                LocalizationUtility::translate($translationKey, 'mask'),
                $path,
                AbstractMessage::WARNING
            );
        }
    }
}
