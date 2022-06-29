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
use MASK\Mask\ConfigurationLoader\ConfigurationLoader;
use MASK\Mask\Definition\ElementTcaDefinition;
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Enumeration\Tab;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use MASK\Mask\Utility\TemplatePathUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AjaxController
 * @internal
 */
class AjaxController
{
    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var SqlCodeGenerator
     */
    protected $sqlCodeGenerator;

    /**
     * @var HtmlCodeGenerator
     */
    protected $htmlCodeGenerator;

    /**
     * @var BackendLayoutRepository
     */
    protected $backendLayoutRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var FlashMessageQueue
     */
    protected $flashMessageQueue;

    /**
     * @var OnlineMediaHelperRegistry
     */
    protected $onlineMediaHelperRegistry;

    /**
     * @var array
     */
    protected $maskExtensionConfiguration;

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var string[]
     */
    protected static $folderPathKeys = [
        'content',
        'layouts',
        'partials',
        'backend',
        'layouts_backend',
        'partials_backend',
        'preview',
    ];

    public function __construct(
        StorageRepository $storageRepository,
        IconFactory $iconFactory,
        SqlCodeGenerator $sqlCodeGenerator,
        HtmlCodeGenerator $htmlCodeGenerator,
        BackendLayoutRepository $backendLayoutRepository,
        ResourceFactory $resourceFactory,
        ConfigurationLoader $configurationLoader,
        OnlineMediaHelperRegistry $onlineMediaHelperRegistry,
        TableDefinitionCollection $tableDefinitionCollection,
        LoaderInterface $loader,
        array $maskExtensionConfiguration
    ) {
        $this->storageRepository = $storageRepository;
        $this->iconFactory = $iconFactory;
        $this->sqlCodeGenerator = $sqlCodeGenerator;
        $this->htmlCodeGenerator = $htmlCodeGenerator;
        $this->backendLayoutRepository = $backendLayoutRepository;
        $this->resourceFactory = $resourceFactory;
        $this->configurationLoader = $configurationLoader;
        $this->onlineMediaHelperRegistry = $onlineMediaHelperRegistry;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->loader = $loader;
        $this->flashMessageQueue = new FlashMessageQueue('mask');
    }

    public function setupComplete(ServerRequestInterface $request): Response
    {
        // If the loader identifier is NOT DEFINED OR "json" AND the path to the mask.json DOES NOT exist, the setup is incomplete.
        $loaderIdentifier = $this->maskExtensionConfiguration['loader_identifier'] ?? '';
        if (($loaderIdentifier === '' || $loaderIdentifier === 'json') && ($this->maskExtensionConfiguration['json'] ?? '') === '') {
            return new JsonResponse(['setupComplete' => 0, 'loader' => $loaderIdentifier]);
        }

        // If the loader identifier IS "json-split" AND the content elements' folder DOES NOT exist, the setup is incomplete.
        if ($loaderIdentifier === 'json-split' && ($this->maskExtensionConfiguration['content_elements_folder'] ?? '') === '') {
            return new JsonResponse(['setupComplete' => 0, 'loader' => $loaderIdentifier]);
        }

        return new JsonResponse(['setupComplete' => 1, 'loader' => $loaderIdentifier]);
    }

    public function autoConfigureSetup(ServerRequestInterface $request): Response
    {
        $parameters = $request->getParsedBody();
        $extensionKey = $parameters['extension'];
        $loader = $parameters['loader'];

        if ($extensionKey === '') {
            return new JsonResponse(['result' => ['error' => 'required!']]);
        }

        if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
            return new JsonResponse(['result' => ['error' => 'must be loaded!']]);
        }

        $extensionPath = 'EXT:' . $extensionKey;
        $extensionConfiguration = new ExtensionConfiguration();
        $configuration = $extensionConfiguration->get('mask');
        $configuration['loader_identifier'] = $loader;
        if ($loader === 'json') {
            $configuration['json'] = $extensionPath . '/Configuration/Mask/mask.json';
        } else {
            $configuration['content_elements_folder'] = $extensionPath . '/Configuration/Mask/ContentElements';
            $configuration['backend_layouts_folder'] = $extensionPath . '/Configuration/Mask/BackendLayouts';
        }
        $configuration['content'] = $extensionPath . '/Resources/Private/Mask/Frontend/Templates';
        $configuration['layouts'] = $extensionPath . '/Resources/Private/Mask/Frontend/Layouts';
        $configuration['partials'] = $extensionPath . '/Resources/Private/Mask/Frontend/Partials';
        $configuration['backend'] = $extensionPath . '/Resources/Private/Mask/Backend/Templates';
        $configuration['layouts_backend'] = $extensionPath . '/Resources/Private/Mask/Backend/Layouts';
        $configuration['partials_backend'] = $extensionPath . '/Resources/Private/Mask/Backend/Partials';
        $configuration['preview'] = $extensionPath . '/Resources/Public/Mask/';

        if ((new Typo3Version())->getMajorVersion() > 10) {
            $extensionConfiguration->set('mask', $configuration);
        } else {
            $extensionConfiguration->set('mask', '', $configuration);
        }

        return new JsonResponse(['result' => ['error' => '']]);
    }

    public function missingFilesOrFolders(ServerRequestInterface $request): Response
    {
        $missing = 0;
        $missingFolders = $this->getMissingFolders();
        if ($this->getMissingFolders() !== []) {
            $missing = 1;
        }

        // If no elements exist, there can't be any missing templates.
        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return new JsonResponse(['missing' => $missing, 'missingFolders' => $missingFolders, 'missingTemplates' => []]);
        }

        // Loop through each element and check if template exists.
        $missingTemplates = [];
        foreach ($this->tableDefinitionCollection->getTable('tt_content')->elements as $element) {
            if (!$this->contentElementTemplateExists($element->key)) {
                $missingTemplates[$element->key] = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $element->key, true);
            }
        }

        if ($missingTemplates !== []) {
            return new JsonResponse(['missing' => 1, 'missingFolders' => $missingFolders, 'missingTemplates' => $missingTemplates]);
        }

        return new JsonResponse(['missing' => $missing, 'missingFolders' => $missingFolders, 'missingTemplates' => $missingTemplates]);
    }

    public function fixMissingFilesOrFolders(ServerRequestInterface $request): Response
    {
        foreach ($this->getMissingFolders() as $missingFolderPath) {
            if ($this->createFolder($missingFolderPath)) {
                $this->addFlashMessage('Successfully created directory: ' . $missingFolderPath);
            } else {
                $this->addFlashMessage('Failed to create directory: ' . $missingFolderPath, '', AbstractMessage::ERROR);
            }
        }

        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush()]);
        }

        $success = true;
        $numberTemplateFilesCreated = 0;
        foreach ($this->tableDefinitionCollection->getTable('tt_content')->elements as $element) {
            if (!$this->contentElementTemplateExists($element->key)) {
                try {
                    $success &= $this->createHtml($element->key);
                    $numberTemplateFilesCreated++;
                } catch (\Exception $e) {
                    $success = false;
                }
            }
        }

        if (!$success) {
            $this->addFlashMessage('Failed to create template files. Please check your extension configuration "content"', '', AbstractMessage::ERROR);
        }

        if ($numberTemplateFilesCreated > 0 && $success) {
            $this->addFlashMessage('Successfully created ' . $numberTemplateFilesCreated . ' template files.');
        }

        return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush()]);
    }

    /**
     * Generates Fluid HTML for Contentelements
     */
    public function showHtmlAction(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $html = $this->htmlCodeGenerator->generateHtml($params['key'], $params['table']);
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setPartialRootPaths(['EXT:mask/Resources/Private/Backend/Partials']);
        $view->setTemplatePathAndFilename('EXT:mask/Resources/Private/Backend/Templates/Ajax/ShowHtml.html');
        $view->assign('html', $html);
        return new HtmlResponse($view->render());
    }

    public function save(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        $isNew = (bool)$params['isNew'];
        $elementKey = $params['element']['key'];
        $fields = json_decode($params['fields'], true);
        try {
            $tableDefinitionCollection = $this->storageRepository->update($params['element'], $fields, $params['type'], $isNew);
        } catch (\Exception $e) {
            $this->addFlashMessage($e->getMessage(), '', AbstractMessage::ERROR);
            return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush(), 'hasError' => 1]);
        }
        $this->generateAction($tableDefinitionCollection);
        if ($params['type'] === 'tt_content') {
            try {
                $this->createHtml($elementKey);
            } catch (\Exception $e) {
                $this->addFlashMessage('Creating template file has failed. Please check your extension setting "content".', '', AbstractMessage::WARNING);
            }
        }
        if ($isNew) {
            $this->addFlashMessage($this->translateLabel('tx_mask.content.newcontentelement'));
        } else {
            $this->addFlashMessage($this->translateLabel('tx_mask.content.updatedcontentelement'));
        }
        return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush(), 'hasError' => 0]);
    }

    /**
     * Delete a content element
     */
    public function delete(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        if ($params['purge']) {
            $this->deleteHtml($params['key']);
        }
        $tableDefinitionCollection = $this->storageRepository->persist($this->storageRepository->remove('tt_content', $params['key']));
        $this->generateAction($tableDefinitionCollection);
        $this->addFlashMessage($this->translateLabel('tx_mask.content.deletedcontentelement'));
        return new JsonResponse($this->flashMessageQueue->getAllMessagesAndFlush());
    }

    public function toggleVisibility(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        if ((int)$params['element']['hidden'] === 1) {
            $tableDefinitionCollection = $this->storageRepository->activate('tt_content', $params['element']['key']);
            $this->addFlashMessage($this->translateLabel('tx_mask.content.activatedcontentelement'));
        } else {
            $tableDefinitionCollection = $this->storageRepository->hide('tt_content', $params['element']['key']);
            $this->addFlashMessage($this->translateLabel('tx_mask.content.hiddencontentelement'));
        }
        $this->generateAction($tableDefinitionCollection);
        return new JsonResponse($this->flashMessageQueue->getAllMessagesAndFlush());
    }

    public function backendLayouts(ServerRequestInterface $request): Response
    {
        $backendLayouts = $this->backendLayoutRepository->findAll(GeneralUtility::trimExplode(',', $this->maskExtensionConfiguration['backendlayout_pids'] ?? ''));
        $json['backendLayouts'] = [];
        /** @var BackendLayout $backendLayout */
        foreach ($backendLayouts as $key => $backendLayout) {
            $iconPath = $backendLayout->getIconPath();
            if ($iconPath) {
                $image = $this->resourceFactory->retrieveFileOrFolderObject($iconPath);
                if ($image instanceof File) {
                    $processingInstructions = [
                        'width' => '32',
                        'height' => '32c',
                    ];
                    $processedImage = $image->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, $processingInstructions);
                    $publicUrl = $processedImage->getPublicUrl();
                    // TYPO3 v10 compatibility
                    // This is essentially what is done in PublicUrlPrefixer since TYPO3 v11.
                    if (
                        (new Typo3Version())->getMajorVersion() === 10
                        && !(str_starts_with($publicUrl, '//') || strpos($publicUrl, '://') > 0)
                    ) {
                        $publicUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_PATH') . $publicUrl;
                    }
                    $backendLayout->setIconPath($publicUrl);
                }
            }
            $json['backendLayouts'][] = [
                'key' => $key,
                'title' => $backendLayout->getTitle(),
                'description' => $backendLayout->getDescription(),
                'icon' => $backendLayout->getIconPath(),
            ];
        }
        return new JsonResponse($json);
    }

    public function elements(ServerRequestInterface $request): Response
    {
        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return new JsonResponse(['elements' => []]);
        }

        $elements = [];
        foreach ($this->tableDefinitionCollection->getTable('tt_content')->elements as $element) {
            $overlay = $element->hidden ? 'overlay-hidden' : null;
            $translatedLabel = $GLOBALS['LANG']->sl($element->label);
            $translatedDescription = $GLOBALS['LANG']->sl($element->description);
            $elements[$element->key] = [
                'color' => $element->color,
                'colorOverlay' => $element->colorOverlay,
                'description' => $element->description,
                'translatedDescription' => $translatedDescription !== '' ? $translatedDescription : $element->description,
                'icon' => $element->icon,
                'iconOverlay' => $element->iconOverlay,
                'key' => $element->key,
                'label' => $element->label,
                'translatedLabel' => $translatedLabel !== '' ? $translatedLabel : $element->label,
                'shortLabel' => $element->shortLabel,
                'iconMarkup' => $this->iconFactory->getIcon('mask-ce-' . $element->key, Icon::SIZE_DEFAULT, $overlay)->render(),
                'templateExists' => $this->contentElementTemplateExists($element->key) ? 1 : 0,
                'hidden' => $element->hidden ? 1 : 0,
                'count' => $this->getElementCount($element->key),
                'sorting' => $element->sorting,
            ];
        }
        $json['elements'] = $elements;
        return new JsonResponse($json);
    }

    protected function getElementCount($elementKey): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        return (int)$queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter(AffixUtility::addMaskCTypePrefix($elementKey))))
            ->execute()
            ->rowCount();
    }

    public function fieldTypes(ServerRequestInterface $request): Response
    {
        $json = [];
        $availability = [
            FieldType::CATEGORY => 11,
        ];
        $typo3Version = new Typo3Version();
        $defaults = $this->configurationLoader->loadDefaults();
        $grouping = $this->configurationLoader->loadFieldGroups();
        foreach (FieldType::getConstants() as $type) {
            if (isset($availability[$type]) && $typo3Version->getMajorVersion() < $availability[$type]) {
                continue;
            }

            $config = [
                'name' => $type,
                'icon' => $this->iconFactory->getIcon('mask-fieldtype-' . $type)->getMarkup(),
                'fields' => [],
                'key' => '',
                'label' => '',
                'description' => '',
                'translatedLabel' => '',
                'itemLabel' => $this->translateLabel('tx_mask.field.' . $type),
                'parent' => [],
                'group' => $grouping[$type],
                'newField' => true,
                'tca' => [
                    'l10n_mode' => '',
                ],
            ];

            if (isset($defaults[$type]['tca_in'])) {
                foreach ($defaults[$type]['tca_in'] as $tcaKey => $value) {
                    $config['tca'][$tcaKey] = $value;
                }
            }
            $json[] = $config;
        }
        return new JsonResponse($json);
    }

    public function fieldGroups(ServerRequestInterface $request): Response
    {
        return new JsonResponse(
            [
                'groups' => [
                    [
                        'name' => 'input',
                        'label' => $this->translateLabel('tx_mask.input'),
                    ],
                    [
                        'name' => 'text',
                        'label' => $this->translateLabel('tx_mask.text'),
                    ],
                    [
                        'name' => 'date',
                        'label' => $this->translateLabel('tx_mask.date'),
                    ],
                    [
                        'name' => 'choice',
                        'label' => $this->translateLabel('tx_mask.choice'),
                    ],
                    [
                        'name' => 'special',
                        'label' => $this->translateLabel('tx_mask.special'),
                    ],
                    [
                        'name' => 'repeating',
                        'label' => $this->translateLabel('tx_mask.repeating'),
                    ],
                    [
                        'name' => 'structure',
                        'label' => $this->translateLabel('tx_mask.structure'),
                    ],
                ],
            ]
        );
    }

    public function multiUse(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $key = $params['key'];
        $newField = (int)$params['newField'];
        $elementKey = '';
        if ($newField === 0) {
            $elementKey = $params['elementKey'];
        }
        $json['multiUseElements'] = $this->getMultiUseForField($key, $elementKey);
        return new JsonResponse($json);
    }

    /**
     * Checks all fields of an element for multi usage.
     * These fields CAN NOT be shared: inline, palette, tab, fields in inline.
     * These fields CAN be shared: all other first level fields, all other fields in first level palettes.
     */
    public function loadAllMultiUse(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $table = $params['table'];
        $elementKey = $params['elementKey'];
        $element = $this->tableDefinitionCollection->loadElement($table, $elementKey);

        if (!$element) {
            return new JsonResponse(['multiUseElements' => []]);
        }

        $multiUseElements = [];
        foreach ($element->getRootTcaFields() as $field) {
            if ($field->isCoreField) {
                continue;
            }

            // Get fields in palette
            if ($field->getFieldType()->equals(FieldType::PALETTE)) {
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $elementKey) as $paletteField) {
                    if ($paletteField->isCoreField || !$paletteField->getFieldType()->canBeShared()) {
                        continue;
                    }
                    $multiUseElements[$paletteField->fullKey] = $this->getMultiUseForField($paletteField->fullKey, $elementKey);
                }
                continue;
            }

            if (!$field->getFieldType()->canBeShared()) {
                continue;
            }

            $multiUseElements[$field->fullKey] = $this->getMultiUseForField($field->fullKey, $elementKey);
        }

        return new JsonResponse(['multiUseElements' => $multiUseElements]);
    }

    protected function getMultiUseForField(string $key, string $elementKey): array
    {
        $type = $this->tableDefinitionCollection->getTableByField($key, $elementKey);
        $multiUseElements = $this->tableDefinitionCollection->getElementsWhichUseField($key, $type)->toArray();

        // Filter elements with same element key
        $multiUseElements = array_filter(
            $multiUseElements,
            static function ($item) use ($elementKey) {
                return $item['key'] !== $elementKey;
            }
        );

        $multiUseElements = array_map(
            static function ($item) {
                return [
                    'key' => $item['key'],
                    'label' => $item['label'],
                ];
            },
            $multiUseElements
        );

        // Reset keys and return values
        return array_values($multiUseElements);
    }

    public function migrationsDone(ServerRequestInterface $request): Response
    {
        return new JsonResponse(['migrationsDone' => (int)$this->tableDefinitionCollection->getMigrationDone()]);
    }

    public function persistMaskDefinition(ServerRequestInterface $request): Response
    {
        try {
            $this->loader->write($this->tableDefinitionCollection);
            return new JsonResponse(['status' => 'ok', 'title' => $this->translateLabel('tx_mask.update_complete.title'), 'message' => $this->translateLabel('tx_mask.update_complete.message')]);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'error', 'title' => $this->translateLabel('tx_mask.update_failed.title'), 'message' => $this->translateLabel('tx_mask.update_failed.message')]);
        }
    }

    public function icons(ServerRequestInterface $request): Response
    {
        $icons = [
            'Web Application' => ['address-book', 'address-book-o', 'address-card', 'address-card-o', 'adjust', 'anchor', 'archive', 'asterisk', 'at', 'balance-scale', 'ban', 'bank', 'barcode', 'bars', 'bath', 'bathtub', 'battery', 'battery-0', 'battery-1', 'battery-2', 'battery-3', 'battery-4', 'battery-empty', 'battery-full', 'battery-half', 'battery-quarter', 'battery-three-quarters', 'bed', 'beer', 'bell', 'bell-o', 'bell-slash', 'bell-slash-o', 'binoculars', 'birthday-cake', 'bolt', 'bomb', 'book', 'bookmark', 'bookmark-o', 'briefcase', 'bug', 'building', 'building-o', 'bullhorn', 'bullseye', 'calculator', 'calendar', 'calendar-check-o', 'calendar-minus-o', 'calendar-o', 'calendar-plus-o', 'calendar-times-o', 'camera', 'camera-retro', 'cart-arrow-down', 'cart-plus', 'certificate', 'check', 'check-circle', 'check-circle-o', 'child', 'circle-thin', 'clock-o', 'clone', 'close', 'cloud', 'cloud-download', 'cloud-upload', 'code', 'code-fork', 'coffee', 'cogs', 'comment', 'comment-o', 'commenting', 'commenting-o', 'comments', 'comments-o', 'compass', 'copyright', 'creative-commons', 'crop', 'crosshairs', 'cube', 'cubes', 'cutlery', 'dashboard', 'database', 'desktop', 'diamond', 'download', 'drivers-license', 'drivers-license-o', 'edit', 'ellipsis-h', 'ellipsis-v', 'envelope', 'envelope-o', 'envelope-open', 'envelope-open-o', 'envelope-square', 'exclamation', 'exclamation-circle', 'exclamation-triangle', 'external-link', 'external-link-square', 'eye', 'eye-slash', 'eyedropper', 'fax', 'feed', 'female', 'film', 'filter', 'fire', 'fire-extinguisher', 'flag', 'flag-checkered', 'flag-o', 'flash', 'flask', 'folder', 'folder-o', 'folder-open', 'folder-open-o', 'frown-o', 'futbol-o', 'gamepad', 'gavel', 'gears', 'gift', 'glass', 'globe', 'graduation-cap', 'group', 'handshake-o', 'hashtag', 'hdd-o', 'headphones', 'history', 'home', 'hotel', 'hourglass', 'hourglass-1', 'hourglass-2', 'hourglass-3', 'hourglass-end', 'hourglass-half', 'hourglass-o', 'hourglass-start', 'i-cursor', 'id-badge', 'id-card', 'id-card-o', 'image', 'inbox', 'industry', 'info', 'info-circle', 'institution', 'key', 'keyboard-o', 'language', 'laptop', 'leaf', 'legal', 'lemon-o', 'level-down', 'level-up', 'life-bouy', 'life-buoy', 'life-ring', 'life-saver', 'lightbulb-o', 'location-arrow', 'lock', 'magic', 'magnet', 'mail-forward', 'mail-reply', 'mail-reply-all', 'male', 'map', 'map-marker', 'map-o', 'map-pin', 'map-signs', 'meh-o', 'microchip', 'microphone', 'microphone-slash', 'minus', 'minus-circle', 'mobile', 'mobile-phone', 'moon-o', 'mortar-board', 'mouse-pointer', 'music', 'navicon', 'newspaper-o', 'object-group', 'object-ungroup', 'paint-brush', 'paper-plane', 'paper-plane-o', 'paw', 'pencil', 'pencil-square', 'pencil-square-o', 'percent', 'phone', 'phone-square', 'photo', 'picture-o', 'plug', 'plus', 'plus-circle', 'podcast', 'power-off', 'print', 'puzzle-piece', 'qrcode', 'question', 'question-circle', 'quote-left', 'quote-right', 'recycle', 'registered', 'remove', 'reorder', 'reply', 'reply-all', 'retweet', 'road', 'rss', 'rss-square', 's15', 'search', 'search-minus', 'search-plus', 'send', 'send-o', 'server', 'share', 'share-square', 'share-square-o', 'shield', 'shopping-bag', 'shopping-basket', 'shopping-cart', 'shower', 'sign-in', 'sign-out', 'signal', 'sitemap', 'sliders', 'smile-o', 'snowflake-o', 'soccer-ball-o', 'sort', 'sort-alpha-asc', 'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-asc', 'sort-desc', 'sort-down', 'sort-numeric-asc', 'sort-numeric-desc', 'sort-up', 'spoon', 'star', 'star-half', 'star-half-empty', 'star-half-full', 'star-half-o', 'star-o', 'sticky-note', 'sticky-note-o', 'street-view', 'suitcase', 'sun-o', 'support', 'tablet', 'tachometer', 'tag', 'tags', 'tasks', 'television', 'terminal', 'thermometer', 'thermometer-0', 'thermometer-1', 'thermometer-2', 'thermometer-3', 'thermometer-4', 'thermometer-empty', 'thermometer-full', 'thermometer-half', 'thermometer-quarter', 'thermometer-three-quarters', 'thumb-tack', 'ticket', 'times', 'times-circle', 'times-circle-o', 'times-rectangle', 'times-rectangle-o', 'tint', 'toggle-off', 'toggle-on', 'trademark', 'trash', 'trash-o', 'tree', 'trophy', 'tv', 'umbrella', 'university', 'unlock', 'unlock-alt', 'unsorted', 'upload', 'user', 'user-circle', 'user-circle-o', 'user-o', 'user-plus', 'user-secret', 'user-times', 'users', 'vcard', 'vcard-o', 'video-camera', 'volume-down', 'volume-off', 'volume-up', 'warning', 'wifi', 'window-close', 'window-close-o', 'window-maximize', 'window-minimize', 'window-restore', 'wrench'],
            'Accessibility' => ['american-sign-language-interpreting', 'asl-interpreting', 'assistive-listening-systems', 'audio-description', 'blind', 'braille', 'cc', 'deaf', 'deafness', 'hard-of-hearing', 'low-vision', 'question-circle-o', 'sign-language', 'signing', 'tty', 'universal-access', 'volume-control-phone', 'wheelchair', 'wheelchair-alt'],
            'Hand' => ['hand-grab-o', 'hand-lizard-o', 'hand-o-down', 'hand-o-left', 'hand-o-right', 'hand-o-up', 'hand-paper-o', 'hand-peace-o', 'hand-pointer-o', 'hand-rock-o', 'hand-scissors-o', 'hand-spock-o', 'hand-stop-o', 'thumbs-down', 'thumbs-o-down', 'thumbs-o-up', 'thumbs-up'],
            'Transportation' => ['automobile', 'bicycle', 'bus', 'cab', 'car', 'fighter-jet', 'motorcycle', 'plane', 'rocket', 'ship', 'space-shuttle', 'subway', 'taxi', 'train', 'truck'],
            'Gender' => ['genderless', 'intersex', 'mars', 'mars-double', 'mars-stroke', 'mars-stroke-h', 'mars-stroke-v', 'mercury', 'neuter', 'transgender', 'transgender-alt', 'venus', 'venus-double', 'venus-mars'],
            'File Type' => ['file', 'file-archive-o', 'file-audio-o', 'file-code-o', 'file-excel-o', 'file-image-o', 'file-movie-o', 'file-o', 'file-pdf-o', 'file-photo-o', 'file-picture-o', 'file-powerpoint-o', 'file-sound-o', 'file-text', 'file-text-o', 'file-video-o', 'file-word-o', 'file-zip-o'],
            'Spinner' => ['circle-o-notch', 'cog', 'gear', 'refresh', 'spinner'],
            'Form Control' => ['check-square', 'check-square-o', 'circle', 'circle-o', 'dot-circle-o', 'minus-square', 'minus-square-o', 'plus-square', 'plus-square-o', 'square', 'square-o'],
            'Payment' => ['cc-amex', 'cc-diners-club', 'cc-discover', 'cc-jcb', 'cc-mastercard', 'cc-paypal', 'cc-stripe', 'cc-visa', 'credit-card', 'credit-card-alt', 'google-wallet'],
            'Chart' => ['area-chart', 'bar-chart', 'bar-chart-o', 'line-chart', 'pie-chart'],
            'Currency' => ['btc', 'cny', 'dollar', 'eur', 'euro', 'gbp', 'gg-circle', 'ils', 'inr', 'jpy', 'krw', 'money', 'rmb', 'rouble', 'rub', 'ruble', 'rupee', 'shekel', 'sheqel', 'try', 'turkish-lira', 'usd', 'viacoin', 'won', 'yen'],
            'Text Editor' => ['align-center', 'align-justify', 'align-left', 'align-right', 'bold', 'chain', 'chain-broken', 'clipboard', 'columns', 'copy', 'cut', 'dedent', 'eraser', 'files-o', 'floppy-o', 'font', 'header', 'indent', 'italic', 'link', 'list', 'list-alt', 'list-ol', 'list-ul', 'outdent', 'paperclip', 'paragraph', 'paste', 'repeat', 'rotate-left', 'rotate-right', 'save', 'scissors', 'strikethrough', 'subscript', 'superscript', 'table', 'text-height', 'text-width', 'th', 'th-large', 'th-list', 'underline', 'undo', 'unlink'],
            'Directional' => ['angle-double-down', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'arrow-circle-down', 'arrow-circle-left', 'arrow-circle-o-down', 'arrow-circle-o-left', 'arrow-circle-o-right', 'arrow-circle-o-up', 'arrow-circle-right', 'arrow-circle-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows', 'arrows-h', 'arrows-v', 'caret-down', 'caret-left', 'caret-right', 'caret-square-o-down', 'caret-square-o-left', 'caret-square-o-right', 'caret-square-o-up', 'caret-up', 'chevron-circle-down', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'exchange', 'long-arrow-down', 'long-arrow-left', 'long-arrow-right', 'long-arrow-up', 'toggle-down', 'toggle-left', 'toggle-right', 'toggle-up'],
            'Video Player' => ['arrows-alt', 'backward', 'compress', 'eject', 'expand', 'fast-backward', 'fast-forward', 'forward', 'pause', 'pause-circle', 'pause-circle-o', 'play', 'play-circle', 'play-circle-o', 'random', 'step-backward', 'step-forward', 'stop', 'stop-circle', 'stop-circle-o'],
            'Brand' => ['500px', 'adn', 'amazon', 'android', 'angellist', 'apple', 'bandcamp', 'behance', 'behance-square', 'bitbucket', 'bitbucket-square', 'bitcoin', 'black-tie', 'bluetooth', 'bluetooth-b', 'buysellads', 'paypal', 'chrome', 'codepen', 'codiepie', 'connectdevelop', 'contao', 'css3', 'dashcube', 'delicious', 'deviantart', 'digg', 'dribbble', 'dropbox', 'drupal', 'edge', 'eercast', 'empire', 'envira', 'etsy', 'expeditedssl', 'fa', 'facebook', 'facebook-f', 'facebook-official', 'facebook-square', 'firefox', 'first-order', 'flickr', 'font-awesome', 'fonticons', 'fort-awesome', 'forumbee', 'foursquare', 'free-code-camp', 'ge', 'get-pocket', 'gg', 'git', 'git-square', 'github', 'github-alt', 'github-square', 'gitlab', 'gittip', 'glide', 'glide-g', 'google', 'google-plus', 'google-plus-circle', 'google-plus-official', 'google-plus-square', 'gratipay', 'grav', 'hacker-news', 'houzz', 'html5', 'imdb', 'instagram', 'internet-explorer', 'ioxhost', 'joomla', 'jsfiddle', 'lastfm', 'lastfm-square', 'leanpub', 'linkedin', 'linkedin-square', 'linode', 'linux', 'maxcdn', 'meanpath', 'medium', 'meetup', 'mixcloud', 'modx', 'odnoklassniki', 'odnoklassniki-square', 'opencart', 'openid', 'opera', 'optin-monster', 'pagelines', 'pied-piper', 'pied-piper-alt', 'pied-piper-pp', 'pinterest', 'pinterest-p', 'pinterest-square', 'product-hunt', 'qq', 'quora', 'ra', 'ravelry', 'rebel', 'reddit', 'reddit-alien', 'reddit-square', 'renren', 'resistance', 'safari', 'scribd', 'sellsy', 'share-alt', 'share-alt-square', 'shirtsinbulk', 'simplybuilt', 'skyatlas', 'skype', 'slack', 'slideshare', 'snapchat', 'snapchat-ghost', 'snapchat-square', 'soundcloud', 'spotify', 'stack-exchange', 'stack-overflow', 'steam', 'steam-square', 'stumbleupon', 'stumbleupon-circle', 'superpowers', 'telegram', 'tencent-weibo', 'themeisle', 'trello', 'tripadvisor', 'tumblr', 'tumblr-square', 'twitch', 'twitter', 'twitter-square', 'usb', 'viadeo', 'viadeo-square', 'vimeo', 'vimeo-square', 'vine', 'vk', 'wechat', 'weibo', 'weixin', 'whatsapp', 'wikipedia-w', 'windows', 'wordpress', 'wpbeginner', 'wpexplorer', 'wpforms', 'xing', 'xing-square', 'y-combinator', 'y-combinator-square', 'yahoo', 'yc', 'yc-square', 'yelp', 'yoast', 'youtube', 'youtube-play', 'youtube-square'],
            'Medical' => ['ambulance', 'h-square', 'heart', 'heart-o', 'heartbeat', 'hospital-o', 'medkit', 'stethoscope', 'user-md'],
        ];
        foreach ($icons as $category => $values) {
            $icons[$category] = array_map(function ($item) {
                return 'fa-' . $item;
            }, $values);
        }
        return new JsonResponse($icons);
    }

    public function existingTca(ServerRequestInterface $request): Response
    {
        $allowedFields = [
            'tt_content' => [
                'header',
                'header_layout',
                'header_position',
                'date',
                'header_link',
                'subheader',
                'bodytext',
                'assets',
                'image',
                'media',
                'imagewidth',
                'imageheight',
                'imageborder',
                'imageorient',
                'imagecols',
                'image_zoom',
                'bullets_type',
                'table_delimiter',
                'table_enclosure',
                'table_caption',
                'file_collections',
                'filelink_sorting',
                'filelink_sorting_direction',
                'target',
                'filelink_size',
                'uploads_description',
                'uploads_type',
                'pages',
                'selected_categories',
                'category_field',
            ],
        ];

        $table = $request->getQueryParams()['table'];
        $type = $request->getQueryParams()['type'];
        $fields = ['mask' => [], 'core' => []];
        $searchFieldType = FieldType::cast($type);

        // Return empty result for non-shareable fields.
        if (!$searchFieldType->canBeShared()) {
            return new JsonResponse($fields);
        }

        foreach (($GLOBALS['TCA'][$table]['columns'] ?? []) as $tcaField => $tcaConfig) {
            $isMaskField = AffixUtility::hasMaskPrefix($tcaField);
            // Skip the field, if it is not a mask field, AND it is not in the allow-list
            // AND it is also not an already defined field in the configuration.
            // This may be an extension field or a core field (which was allowed in former Mask versions).
            if (
                !$isMaskField
                && !in_array($tcaField, $allowedFields[$table] ?? [], true)
                && !$this->tableDefinitionCollection->loadField($table, $tcaField) instanceof TcaFieldDefinition
            ) {
                continue;
            }

            if (($GLOBALS['TCA'][$table]['columns'][$tcaField]['config']['type'] ?? '') === 'passthrough') {
                continue;
            }

            // Add the field, if the field type matches with the search type
            // OR the field is the bodytext field, and we search for a text or textarea field.
            $fieldType = $this->tableDefinitionCollection->getFieldType($tcaField, $table);
            if (
                $fieldType->equals($type)
                || (
                    $tcaField === 'bodytext' && $searchFieldType->isTextareaField()
                )
            ) {
                $key = $isMaskField ? 'mask' : 'core';
                if ($isMaskField) {
                    $label = $this->tableDefinitionCollection->findFirstNonEmptyLabel($table, $tcaField);
                } elseif ('' === $label = $GLOBALS['LANG']->sL($tcaConfig['label'])) {
                    $label = $tcaConfig['label'];
                }
                $fields[$key][] = [
                    'field' => $tcaField,
                    'label' => $label,
                ];
            }
        }

        return new JsonResponse($fields);
    }

    public function tcaFields(ServerRequestInterface $request): Response
    {
        $tcaFields = $this->configurationLoader->loadTcaFields();
        foreach ($tcaFields as $key => $field) {
            if ($field['collision'] ?? false) {
                unset($field['collision']);
                foreach ($field as $type => $typeField) {
                    $tcaFields[$key] = $this->translateTcaFieldLabels($type, $typeField, $tcaFields[$key]);
                }
            } else {
                $tcaFields = $this->translateTcaFieldLabels($key, $field, $tcaFields);
            }
        }
        return new JsonResponse($tcaFields);
    }

    public function tables(ServerRequestInterface $request): Response
    {
        $items = ['' => ''];
        $tables = $GLOBALS['TCA'] ?? [];
        foreach ($tables as $tableKey => $table) {
            // Hidden tables should usually not be used as references.
            if ($table['ctrl']['hideTable'] ?? false) {
                continue;
            }
            $items[$tableKey] = $this->getLanguageService()->sL($table['ctrl']['title']);
        }

        ksort($items);
        $json['foreignTables'] = $items;
        return new JsonResponse($json);
    }

    /**
     * @param array<string, array<string, mixed>> $tcaFields
     * @return array<string, array<string, mixed>>
     */
    protected function translateTcaFieldLabels(string $key, array $field, array $tcaFields): array
    {
        $tcaFields[$key]['label'] = $this->translateLabel($field['label']);
        if (isset($field['placeholder'])) {
            $tcaFields[$key]['placeholder'] = $this->translateLabel($field['placeholder']);
        }
        if (isset($field['description'])) {
            $tcaFields[$key]['description'] = $this->translateLabel($field['description']);
        }
        if (isset($field['keyValueLabels'])) {
            $tcaFields[$key]['keyValueLabels']['key'] = $this->translateLabel($field['keyValueLabels']['key']);
            $tcaFields[$key]['keyValueLabels']['value'] = $this->translateLabel($field['keyValueLabels']['value']);
        }
        if (isset($field['keyValueSelectItems'])) {
            foreach (['key', 'value'] as $keyValue) {
                foreach ($field['keyValueSelectItems'][$keyValue] as $selectItemIndex => $selectItem) {
                    $tcaFields[$key]['keyValueSelectItems'][$keyValue][$selectItemIndex]['label'] = $this->translateLabel($selectItem['label']);
                }
            }
        }
        if (isset($field['properties'])) {
            foreach ($field['properties'] as $propertyKey => $property) {
                $tcaFields[$key]['properties'][$propertyKey]['label'] = $this->translateLabel($property['label']);
            }
        }
        if (isset($tcaFields[$key]['items'])) {
            foreach ($tcaFields[$key]['items'] as $itemKey => $item) {
                $tcaFields[$key]['items'][$itemKey] = $this->translateLabel($item);
            }
        }
        return $tcaFields;
    }

    public function cTypes(ServerRequestInterface $request): Response
    {
        $items = [];
        $cTypes = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'];
        foreach ($cTypes ?? [] as $type) {
            if ($type[1] !== '--div--') {
                $items[$type[1]] = $this->getLanguageService()->sL($type[0]);
            }
        }
        $json['ctypes'] = $items;
        return new JsonResponse($json);
    }

    public function tabs(ServerRequestInterface $request): Response
    {
        $availability = [
            FieldType::CATEGORY => 11,
        ];
        $typo3Version = new Typo3Version();
        $tabs = [];
        $availableTcaFields = $this->configurationLoader->loadTcaFields();
        foreach (FieldType::getConstants() as $type) {
            if (isset($availability[$type]) && $typo3Version->getMajorVersion() < $availability[$type]) {
                continue;
            }
            $tabs[$type] = $this->configurationLoader->loadTab($type);
            // Remove unavailable TCA options
            foreach ($tabs[$type] as $tabType => $rows) {
                foreach ($rows as $rowIndex => $fields) {
                    foreach ($fields as $tcaField => $ize) {
                        if (!array_key_exists($tcaField, $availableTcaFields)) {
                            unset($tabs[$type][$tabType][$rowIndex][$tcaField]);
                        }
                    }
                    // Remove empty rows
                    if (empty($tabs[$type][$tabType][$rowIndex])) {
                        unset($tabs[$type][$tabType][$rowIndex]);
                    }
                }
            }
        }
        return new JsonResponse($tabs);
    }

    public function language(ServerRequestInterface $request): Response
    {
        $language = [];
        $tabs = [
            Tab::GENERAL => 'tx_mask.tabs.default',
            Tab::APPEARANCE => 'tx_mask.tabs.appearance',
            Tab::DATABASE => 'tx_mask.tabs.database',
            Tab::EXTENDED => 'tx_mask.tabs.extended',
            Tab::FIELD_CONTROL => 'tx_mask.tabs.fieldControl',
            Tab::FILES => 'tx_mask.tabs.files',
            Tab::LOCALIZATION => 'tx_mask.tabs.localization',
            Tab::VALIDATION => 'tx_mask.tabs.validation',
            Tab::WIZARDS => 'tx_mask.tabs.wizards',
            Tab::GENERATOR => 'tx_mask.tabs.generator',
            Tab::ITEM_GROUP_SORTING => 'tx_mask.tabs.itemGroupSorting',
            Tab::VALUE_PICKER => 'tx_mask.tabs.valuePicker',
            Tab::ENABLED_CONTROLS => 'tx_mask.tabs.enabledControls',
        ];

        foreach ($tabs as $key => $tab) {
            $tabs[$key] = $this->translateLabel($tab);
        }
        $language['tabs'] = $tabs;

        $language['ok'] = $this->translateLabel('tx_mask.ok');
        $language['close'] = $this->translateLabel('tx_mask.close');
        $language['alert'] = $this->translateLabel('tx_mask.alert');
        $language['fieldsMissing'] = $this->translateLabel('tx_mask.fieldsMissing');
        $language['missingCreated'] = $this->translateLabel('tx_mask.all.createdmissingfolders');
        $language['reset'] = $this->translateLabel('tx_mask.reset_settings_success');
        $language['create'] = $this->translateLabel('tx_mask.all.create');
        $language['add'] = $this->translateLabel('tx_mask.all.add');
        $language['delete'] = $this->translateLabel('tx_mask.all.delete');
        $language['drag'] = $this->translateLabel('tx_mask.all.drag');
        $language['noGroup'] = $this->translateLabel('tx_mask.noGroup');

        $language['deleteModal'] = [
            'title' => $this->translateLabel('tx_mask.field.titleDelete'),
            'content' => $this->translateLabel('tx_mask.all.confirmdelete'),
            'close' => $this->translateLabel('tx_mask.all.abort'),
            'delete' => $this->translateLabel('tx_mask.all.delete'),
            'purge' => $this->translateLabel('tx_mask.all.purge'),
        ];

        $language['tooltip'] = [
            'editElement' => $this->translateLabel('tx_mask.tooltip.edit_element'),
            'deleteElement' => $this->translateLabel('tx_mask.tooltip.delete_element'),
            'enableElement' => $this->translateLabel('tx_mask.tooltip.enable_element'),
            'disableElement' => $this->translateLabel('tx_mask.tooltip.disable_element'),
            'html' => $this->translateLabel('tx_mask.tooltip.html'),
            'deleteField' => $this->translateLabel('tx_mask.field.delete'),
        ];

        $language['deleted'] = $this->translateLabel('tx_mask.content.deletedcontentelement');
        $language['icon'] = $this->translateLabel('tx_mask.all.icon');
        $language['iconOverlay'] = $this->translateLabel('tx_mask.all.iconOverlay');
        $language['color'] = $this->translateLabel('tx_mask.all.color');
        $language['colorOverlay'] = $this->translateLabel('tx_mask.all.colorOverlay');
        $language['usage'] = $this->translateLabel('tx_mask.content.count');
        $language['elementKey'] = $this->translateLabel('tx_mask.all.fieldkey');
        $language['elementLabel'] = $this->translateLabel('tx_mask.all.fieldLabel');

        $language['multistep'] = [
            'chooseKey' => $this->translateLabel('tx_mask.multistep.chooseKey'),
            'chooseLabel' => $this->translateLabel('tx_mask.multistep.chooseKey'),
            'text1' => $this->translateLabel('tx_mask.multistep.text1'),
            'text2' => $this->translateLabel('tx_mask.multistep.text2'),
            'placeholder1' => $this->translateLabel('tx_mask.multistep.placeholder1'),
            'placeholder2' => $this->translateLabel('tx_mask.multistep.placeholder2'),
        ];

        $language['createMissingFilesOrFolders'] = $this->translateLabel('tx_mask.all.createmissingfolders');
        $language['missingFolders'] = $this->translateLabel('tx_mask.all.missingFolders');
        $language['missingTemplates'] = $this->translateLabel('tx_mask.all.missingTemplates');
        $language['migrationsPerformedTitle'] = $this->translateLabel('tx_mask.migrations_performed.title');
        $language['migrationsPerformedMessage'] = $this->translateLabel('tx_mask.migrations_performed.message');
        $language['updateMaskDefinition'] = $this->translateLabel('tx_mask.update_mask_definition');
        $language['selectedItems'] = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.selected');
        $language['availableItems'] = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.items');

        return new JsonResponse($language);
    }

    public function richtextConfiguration(ServerRequestInterface $request): Response
    {
        $config[''] = $this->translateLabel('tx_mask.config.richtextConfiguration.none');
        $presets = array_keys($GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets'] ?? []);
        $presets = array_filter($presets, function ($item) {
            return $item !== 'sys_news';
        });
        $presets = array_combine($presets, $presets);
        $config = array_merge($config, $presets);
        return new JsonResponse($config);
    }

    public function linkHandler(ServerRequestInterface $request): Response
    {
        $linkHandlerList = (array)(BackendUtility::getPagesTSconfig(0)['TCEMAIN.']['linkHandler.'] ?? []);
        $linkHandlerResponse = [];
        foreach ($linkHandlerList as $identifier => $linkHandler) {
            $linkHandlerResponse[] = [
                'identifier' => rtrim($identifier, '.'),
                'label' => $this->getLanguageService()->sL($linkHandler['label']),
            ];
        }

        return new JsonResponse($linkHandlerResponse);
    }

    public function optionalExtensionStatus(ServerRequestInterface $request): Response
    {
        $optionalExtensions = ['rte_ckeditor'];
        $optionalExtensionStatus = [];
        foreach ($optionalExtensions as $optionalExtension) {
            $optionalExtensionStatus[$optionalExtension] = (int)ExtensionManagementUtility::isLoaded($optionalExtension);
        }
        return new JsonResponse($optionalExtensionStatus);
    }

    public function versions(ServerRequestInterface $request): Response
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        return new JsonResponse(
            [
                'typo3' => $typo3Version->getMajorVersion(),
                'mask' => ltrim(ExtensionManagementUtility::getExtensionVersion('mask'), 'v'),
            ]
        );
    }

    public function availableOnlineMedia(ServerRequestInterface $request): Response
    {
        return new JsonResponse($this->onlineMediaHelperRegistry->getSupportedFileExtensions());
    }

    /**
     * Generates all the necessary files
     */
    protected function generateAction(TableDefinitionCollection $tableDefinitionCollection): void
    {
        // Set TCA to enable DefaultTcaSchema
        $tcaCodeGenerator = GeneralUtility::makeInstance(TcaCodeGenerator::class);
        $tcaCodeGenerator->setInlineTca($tableDefinitionCollection);
        foreach ($tableDefinitionCollection as $tableDefinition) {
            if (!AffixUtility::hasMaskPrefix($tableDefinition->table)) {
                $fieldTca = $tcaCodeGenerator->generateFieldsTca($tableDefinition->table);
                if ($fieldTca === []) {
                    continue;
                }
                ExtensionManagementUtility::addTCAcolumns($tableDefinition->table, $fieldTca);
            }
        }

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
     * Saves Fluid HTML for Contentelements, if File not exists
     */
    protected function saveHtml(string $key, string $html): bool
    {
        // fallback to prevent breaking change
        $path = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $key);
        // Do not override existing files.
        if (file_exists($path)) {
            return false;
        }
        return GeneralUtility::writeFile($path, $html);
    }

    /**
     * Checks if a key for an element is available
     */
    public function checkElementKey(ServerRequest $request): Response
    {
        $elementKey = $request->getQueryParams()['key'];
        $elementTcaDefinition = $this->tableDefinitionCollection->loadElement('tt_content', $elementKey);
        $isAvailable = !$elementTcaDefinition instanceof ElementTcaDefinition;

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Checks if a key for a field is available.
     * Inline fields and content fields must not be used across elements.
     * Other "normal" fields can be used in different elements, but changes are applied for both.
     */
    public function checkFieldKey(ServerRequest $request): Response
    {
        $queryParams = $request->getQueryParams();
        $table = $queryParams['table'];
        $fieldKey = $queryParams['key'];
        $type = $queryParams['type'];
        $elementKey = $queryParams['elementKey'];

        // Check if an inline table with the same key already exists.
        if ($type === FieldType::INLINE) {
            return new JsonResponse(['isAvailable' => !$this->tableDefinitionCollection->hasTable($fieldKey)]);
        }

        // Content fields must be absolutely unique for the table, or naming
        // conflicts may occur.
        if ($type === FieldType::CONTENT) {
            return new JsonResponse(['isAvailable' => $this->tableDefinitionCollection->getTableByField($fieldKey, $elementKey) === '']);
        }

        // All other fields must be unique per table. Exception: If a field
        // with the same field type exists, it may be shared.
        $field = $this->tableDefinitionCollection->loadField($table, $fieldKey);

        return new JsonResponse(['isAvailable' => (!$field instanceof TcaFieldDefinition || !$field->hasFieldType($elementKey) || $field->getFieldType($elementKey)->equals($type))]);
    }

    /**
     * Creates a Message object and adds it to the FlashMessageQueue.
     *
     * @param string $messageBody The message
     * @param string $messageTitle Optional message title
     * @param int $severity Optional severity, must be one of \TYPO3\CMS\Core\Messaging\FlashMessage constants
     * @param bool $storeInSession Optional, defines whether the message should be stored in the session (default) or not
     * @throws \TYPO3\CMS\Core\Exception
     * @see \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function addFlashMessage(string $messageBody, string $messageTitle = '', int $severity = AbstractMessage::OK, bool $storeInSession = true): void
    {
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $messageBody,
            $messageTitle,
            $severity,
            $storeInSession
        );
        $this->flashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Check if template file exists.
     */
    protected function contentElementTemplateExists(string $key): bool
    {
        $templatePath = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $key);
        return file_exists($templatePath) && is_file($templatePath);
    }

    /**
     * Creates a folder.
     */
    protected function createFolder(string $path): bool
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
     * Writes the generated html for the content element into the template file.
     */
    protected function createHtml(string $key): bool
    {
        $html = $this->htmlCodeGenerator->generateHtml($key, 'tt_content');
        return $this->saveHtml($key, $html);
    }

    /**
     * Deletes Fluid html, if file exists
     */
    protected function deleteHtml(string $key): void
    {
        $paths = [];
        $paths[] = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $key);
        $paths[] = TemplatePathUtility::getTemplatePath($this->maskExtensionConfiguration, $key, false, $this->maskExtensionConfiguration['backend'] ?? '');
        foreach ($paths as $path) {
            @unlink($path);
        }
    }

    protected function getMissingFolders(): array
    {
        $missingFolders = [];
        foreach (self::$folderPathKeys as $key) {
            if (!isset($this->maskExtensionConfiguration[$key])) {
                continue;
            }
            $path = MaskUtility::getFileAbsFileName($this->maskExtensionConfiguration[$key]);
            if ($path === '') {
                continue;
            }
            if (!file_exists($path)) {
                $missingFolders[$key] = $this->maskExtensionConfiguration[$key];
            }
        }

        return $missingFolders;
    }

    protected function translateLabel(string $key): string
    {
        return $this->getLanguageService()->sL('LLL:EXT:mask/Resources/Private/Language/locallang.xlf:' . $key);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
