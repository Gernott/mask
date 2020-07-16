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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 */
class WizardController extends ActionController
{
    /**
     * StorageRepository
     *
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * BackendLayoutRepository
     *
     * @var BackendLayoutRepository
     */
    protected $backendLayoutRepository;

    /**
     * FieldHelper
     *
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * HtmlCodeGenerator
     *
     * @var HtmlCodeGenerator
     */
    protected $htmlCodeGenerator;

    /**
     * SqlCodeGenerator
     *
     * @var SqlCodeGenerator
     */
    protected $sqlCodeGenerator;

    /**
     * SettingsService
     *
     * @var SettingsService
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

    protected $missingFolders = [];

    public function __construct(
        StorageRepository $storageRepository,
        SettingsService $settingsService,
        BackendLayoutRepository $backendLayoutRepository,
        FieldHelper $fieldHelper,
        SqlCodeGenerator $sqlCodeGenerator,
        HtmlCodeGenerator $htmlCodeGenerator
    ) {
        $this->storageRepository = $storageRepository;
        $this->settingsService = $settingsService;
        $this->backendLayoutRepository = $backendLayoutRepository;
        $this->fieldHelper = $fieldHelper;
        $this->sqlCodeGenerator = $sqlCodeGenerator;
        $this->htmlCodeGenerator = $htmlCodeGenerator;
        $this->extSettings = $this->settingsService->get();
    }

    /**
     * Generates all the necessary files
     * @author Gernot Ploiner <gp@webprofil.at>
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
     */
    protected function prepareStorage(&$storage): void
    {
        // Fill storage with additional data before assigning to view
        if ($storage['tca']) {
            foreach ($storage['tca'] as $key => $field) {
                if (is_array($field)) {
                    if ($field['config']['type'] == 'inline') {
                        $storage['tca'][$key]['inlineFields'] = $this->storageRepository->loadInlineFields($key);
                        $this->sortInlineFieldsByOrder($storage['tca'][$key]['inlineFields']);
                    }
                }
                // Convert old date format Y-m-d to d-m-Y
                $dbType = $field['config']['dbType'] ?? false;
                if ($dbType && in_array($dbType, ['date', 'datetime'])) {
                    $format = ($dbType == 'date') ? 'd-m-Y' : 'H:i d-m-Y';
                    $lower = $field['config']['range']['lower'] ?? false;
                    $upper = $field['config']['range']['upper'] ?? false;
                    if ($lower && (bool)preg_match('/^[0-9]{4}]/', $lower)) {
                        $storage['tca'][$key]['config']['range']['lower'] = (new \DateTime($lower))->format($format);
                    }
                    if ($upper && (bool)preg_match('/^[0-9]{4}]/', $upper)) {
                        $storage['tca'][$key]['config']['range']['upper'] = (new \DateTime($upper))->format($format);
                    }
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
        // fallback to prevent breaking change
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
     * @return Response
     */
    public function checkFieldKey(ServerRequest $request): Response
    {
        $queryParams = $request->getQueryParams();
        $fieldKey = $queryParams['key'];
        $table = $queryParams['table'] ?? 'tt_content';

        // check if fieldKey is available for this table
        $isAvailable = true;
        if ($this->storageRepository->loadField($table, $fieldKey)) {
            $isAvailable = false;
        }

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Checks if a key for an element is available
     * @param ServerRequest $request
     * @return Response
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function checkElementKey(ServerRequest $request): Response
    {
        $elementKey = $request->getQueryParams()['key'];

        $isAvailable = true;
        if ($this->storageRepository->loadElement('tt_content', $elementKey)) {
            $isAvailable = false;
        }

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Redirects the request to the correct view
     * @throws StopActionException
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
        if (array_key_exists('save', $formAction)) {
            $this->redirect('edit', null, null, $arguments);
        } else {
            if (array_key_exists('saveAndExit', $formAction)) {
                $this->redirect('list', 'Wizard');
            }
        }
    }

    /**
     * Check, if folders from extensionmanager-settings are existing
     *
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
     */
    protected function checkFolder($path, $translationKey = 'tx_mask.all.error.missingjson'): void
    {
        if (!file_exists(MaskUtility::getFileAbsFileName($path))) {
            $this->missingFolders[] = $path;
//            $this->addFlashMessage(
//                LocalizationUtility::translate($translationKey, 'mask'),
//                $path,
//                AbstractMessage::WARNING
//            );
        }
    }

    /**
     * Sort inline fields recursively.
     *
     * @param array $inlineFields
     */
    public function sortInlineFieldsByOrder(array &$inlineFields)
    {
        uasort(
            $inlineFields,
            function ($columnA, $columnB) {
                $a = isset($columnA['order']) ? (int)$columnA['order'] : 0;
                $b = isset($columnB['order']) ? (int)$columnB['order'] : 0;
                return $a - $b;
            }
        );

        foreach ($inlineFields as $i => $field) {
            if ($field['config']['type'] == 'inline') {
                if (isset($inlineFields[$i]['inlineFields']) && is_array($inlineFields[$i]['inlineFields'])) {
                    $this->sortInlineFieldsByOrder($inlineFields[$i]['inlineFields']);
                }
            }
        }
    }

    /**
     * action list
     */
    public function listAction()
    {
        $settings = $this->settingsService->get();
        $storages = $this->storageRepository->load();
        $backendLayouts = $this->backendLayoutRepository->findAll(explode(',', $settings['backendlayout_pids']));
        $this->checkFolders();

        $this->view->assign('missingFolders', $this->missingFolders);
        $this->view->assign('storages', $storages);
        $this->view->assign('backendLayouts', $backendLayouts);
    }
}
