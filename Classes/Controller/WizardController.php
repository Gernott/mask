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

use MASK\Mask\Domain\Repository\StorageRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * ^
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class WizardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var bool
     */
    protected $dbUpdateNeeded = false;

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @Inject()
     */
    protected $storageRepository;

    /**
     * BackendLayoutRepository
     *
     * @var \MASK\Mask\Domain\Repository\BackendLayoutRepository
     * @Inject()
     */
    protected $backendLayoutRepository;

    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @Inject()
     */
    protected $fieldHelper;

    /**
     * HtmlCodeGenerator
     *
     * @var \MASK\Mask\CodeGenerator\HtmlCodeGenerator
     * @Inject()
     */
    protected $htmlCodeGenerator;

    /**
     * SqlCodeGenerator
     *
     * @var \MASK\Mask\CodeGenerator\SqlCodeGenerator
     * @Inject()
     */
    protected $sqlCodeGenerator;

    /**
     * SettingsService
     *
     * @var \MASK\Mask\Domain\Service\SettingsService
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
     * is called before every action
     */
    public function initializeAction()
    {
        $this->extSettings = $this->settingsService->get();
    }

    /**
     * Generates all the necessary files
     * @author Gernot Ploiner <gp@webprofil.at>
     * @author Benjamin Butschell <bb@webprofil.at>
     * @todo clear typoscript cache after generating
     */
    public function generateAction()
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
    protected function prepareStorage(&$storage)
    {
        // Fill storage with additional data before assigning to view
        if ($storage["tca"]) {
            foreach ($storage["tca"] as $key => $field) {
                if (is_array($field)) {
                    if ($field["config"]["type"] == "inline") {
                        $storage["tca"][$key]["inlineFields"] = $this->storageRepository->loadInlineFields($key);
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
    protected function showHtmlAction($key, $table)
    {
        $html = $this->htmlCodeGenerator->generateHtml($key, $table);
        $this->view->assign('html', $html);
    }

    /**
     * Saves Fluid HTML for Contentelements, if File not exists
     *
     * @param string $key
     * @param string $html
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    protected function saveHtml($key, $html)
    {
        if (file_exists(PATH_site . $this->extSettings["content"] . ucfirst($key) . ".html")) {
            return false;
        } else {
            \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $this->extSettings["content"] . ucfirst($key) . ".html",
                $html);
            return true;
        }
    }

    /**
     * Checks if a key for a field is available
     */
    public function checkFieldKey(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $fieldKey = $queryParams['key'];
        $table = 'tt_content';
        if (isset($queryParams['table'])) {
            $table = $queryParams['table'];
        }

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
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    protected function redirectByAction()
    {
        $params = $this->request->getArguments();
        $formAction = $params["formAction"];
        $arguments = array();
        if ($params["storage"]["type"] == "pages") {
            $arguments["layoutIdentifier"] = $this->backendLayoutRepository->findByIdentifier($params["storage"]["elements"]["key"],
                explode(",", $this->extSettings['backendlayout_pids']))->getIdentifier();
        } else {
            $arguments["key"] = $params["storage"]["elements"]["key"];
            $arguments["type"] = $params["storage"]["type"];
        }
        if (key_exists("save", $formAction)) {
            $this->redirect('edit', null, null, $arguments);
        } else {
            if (key_exists("saveAndExit", $formAction)) {
                $this->redirect('list');
            }
        }
    }

    /**
     * Check, if folders from extensionmanager-settings are existing
     *
     * @author Gernot Ploiner <gp@webprofil.at>
     * @return array $messages
     */
    protected function checkFolders()
    {

        $messages = [];

        if (!file_exists(PATH_site . $this->extSettings["content"])) {
            $messages[] = $this->extSettings["content"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder',
                    'mask');
        }
        if (!file_exists(PATH_site . $this->extSettings["preview"])) {
            $messages[] = $this->extSettings["preview"] . ": " . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.error.missingfolder',
                    'mask');
        }
        return $messages;
    }

    /**
     * Creates missing folders that are needed for the use of mask
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return bool $success
     */
    protected function createMissingFolders()
    {
        $success = true;
        if (!file_exists(PATH_site . $this->extSettings["content"])) {
            $success = $success && mkdir(PATH_site . $this->extSettings["content"], 0755, true);
        }
        if (!file_exists(PATH_site . $this->extSettings["preview"])) {
            $success = $success && mkdir(PATH_site . $this->extSettings["preview"], 0755, true);
        }
        return $success;
    }

    /**
     * action creates missing folders
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function createMissingFoldersAction()
    {
        if ($this->createMissingFolders()) {
            $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_mask.all.createdmissingfolders',
                'mask'));
        }
        $this->redirect("list");
    }
}
