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

use MASK\Mask\CodeGenerator\HtmlCodeGenerator;
use MASK\Mask\CodeGenerator\SqlCodeGenerator;
use MASK\Mask\CodeGenerator\TcaCodeGenerator;
use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Domain\Service\SettingsService;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     */
    public function generateAction(): void
    {
        // Set tca to enable DefaultTcaSchema for new inline tables
        $tcaCodeGenerator = GeneralUtility::makeInstance(TcaCodeGenerator::class);
        $tcaCodeGenerator->setInlineTca();

        // Update Database
        $result = $this->sqlCodeGenerator->updateDatabase();
        if (array_key_exists('error', $result)) {
            $this->addFlashMessage($result['error'], '', FlashMessage::ERROR);
        }

        // Clear system cache to force new TCA caching
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroup('system');
    }

    /**
     * Prepares the storage array for fluid view
     *
     * @param array $storage
     * @param $elementKey
     * @throws \Exception
     */
    protected function prepareStorage(&$storage, $elementKey): void
    {
        // Fill storage with additional data before assigning to view
        if ($storage['tca']) {
            foreach ($storage['tca'] as $key => $field) {
                if (is_array($field)) {
                    if (in_array($field['config']['type'], ['inline', 'palette'])) {
                        $storage['tca'][$key]['inlineFields'] = $this->storageRepository->loadInlineFields($key, $elementKey);
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
        $table = $queryParams['table'];
        if (!$table) {
            $table = 'tt_content';
        }
        $type = $queryParams['type'];
        $elementKey = $queryParams['elementKey'];

        $keyExists = false;
        $fieldExists = false;

        if (FieldType::cast($type)->isParentField()) {
            $keyExists = array_key_exists($fieldKey, $this->storageRepository->load());
        }

        if ($type != FieldType::INLINE) {
            if ($type == FieldType::CONTENT) {
                $fieldExists = $this->fieldHelper->getFieldType($fieldKey, $elementKey);
            } elseif ($elementKey) {
                $elementsUse = $this->storageRepository->getElementsWhichUseField($fieldKey, $table);
                foreach ($elementsUse as $use) {
                    if ($use['key'] !== $elementKey) {
                        $fieldExists = true;
                    }
                }
            } else {
                $fieldExists = $this->storageRepository->loadField($table, $fieldKey);
            }
        }

        return new JsonResponse(['isAvailable' => !$keyExists && !$fieldExists]);
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
        $isAvailable = !$this->storageRepository->loadElement('tt_content', $elementKey);

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
