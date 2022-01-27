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
use MASK\Mask\Definition\TableDefinitionCollection;
use MASK\Mask\Definition\TcaFieldDefinition;
use MASK\Mask\Domain\Repository\BackendLayoutRepository;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Enumeration\FieldType;
use MASK\Mask\Enumeration\Tab;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\CompatibilityUtility;
use MASK\Mask\Utility\GeneralUtility as MaskUtility;
use MASK\Mask\Utility\TemplatePathUtility;
use Psr\Http\Message\ServerRequestInterface;
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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AjaxController
 * @internal
 */
class AjaxController
{
    protected $storageRepository;
    protected $iconFactory;
    protected $sqlCodeGenerator;
    protected $htmlCodeGenerator;
    protected $backendLayoutRepository;
    protected $imageService;
    protected $resourceFactory;
    protected $configurationLoader;
    protected $flashMessageQueue;
    protected $maskExtensionConfiguration;
    protected $tableDefinitionCollection;

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
        ImageService $imageService,
        ResourceFactory $resourceFactory,
        ConfigurationLoader $configurationLoader,
        TableDefinitionCollection $tableDefinitionCollection,
        array $maskExtensionConfiguration
    ) {
        $this->storageRepository = $storageRepository;
        $this->iconFactory = $iconFactory;
        $this->sqlCodeGenerator = $sqlCodeGenerator;
        $this->htmlCodeGenerator = $htmlCodeGenerator;
        $this->backendLayoutRepository = $backendLayoutRepository;
        $this->imageService = $imageService;
        $this->resourceFactory = $resourceFactory;
        $this->configurationLoader = $configurationLoader;
        $this->flashMessageQueue = new FlashMessageQueue('mask');
        $this->maskExtensionConfiguration = $maskExtensionConfiguration;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
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

        $extensionConfiguration = new ExtensionConfiguration();
        $configuration = $extensionConfiguration->get('mask');
        $configuration['loader_identifier'] = $loader;
        if ($loader === 'json') {
            $configuration['json'] = 'EXT:' . $extensionKey . '/Configuration/Mask/mask.json';
        } else {
            $configuration['content_elements_folder'] = 'EXT:' . $extensionKey . '/Configuration/Mask/ContentElements';
            $configuration['backend_layouts_folder'] = 'EXT:' . $extensionKey . '/Configuration/Mask/BackendLayouts';
        }
        $configuration['content'] = 'EXT:' . $extensionKey . '/Resources/Private/Mask/Frontend/Templates/';
        $configuration['layouts'] = 'EXT:' . $extensionKey . '/Resources/Private/Mask/Frontend/Layouts/';
        $configuration['partials'] = 'EXT:' . $extensionKey . '/Resources/Private/Mask/Frontend/Partials/';
        $configuration['backend'] = 'EXT:' . $extensionKey . '/Resources/Private/Mask/Backend/Templates/';
        $configuration['partials_backend'] = 'EXT:' . $extensionKey . '/Resources/Private/Mask/Backend/Partials/';
        $configuration['preview'] = 'EXT:' . $extensionKey . '/Resources/Public/Mask/';
        $configuration['backendlayout_pids'] = $extensionConfiguration->get('mask', 'backendlayout_pids');

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
     *
     * @param ServerRequestInterface $request
     * @return Response
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
            $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.newcontentelement', 'mask'));
        } else {
            $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.updatedcontentelement', 'mask'));
        }
        return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush(), 'hasError' => 0]);
    }

    /**
     * Delete a content element
     *
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function delete(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        if ($params['purge']) {
            $this->deleteHtml($params['key']);
        }
        $tableDefinitionCollection = $this->storageRepository->persist($this->storageRepository->remove('tt_content', $params['key']));
        $this->generateAction($tableDefinitionCollection);
        $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask'));
        return new JsonResponse($this->flashMessageQueue->getAllMessagesAndFlush());
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function toggleVisibility(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        if ((int)$params['element']['hidden'] === 1) {
            $tableDefinitionCollection = $this->storageRepository->activate('tt_content', $params['element']['key']);
            $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.activatedcontentelement', 'mask'));
        } else {
            $tableDefinitionCollection = $this->storageRepository->hide('tt_content', $params['element']['key']);
            $this->addFlashMessage(LocalizationUtility::translate('tx_mask.content.hiddencontentelement', 'mask'));
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
                $processingInstructions = [
                    'width' => '32',
                    'height' => '32c'
                ];
                $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
                $imageUri = $this->imageService->getImageUri($processedImage);
                $backendLayout->setIconPath($imageUri);
            }
            $json['backendLayouts'][] = [
                'key' => $key,
                'title' => $backendLayout->getTitle(),
                'description' => $backendLayout->getDescription(),
                'icon' => $backendLayout->getIconPath()
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
                'description' => $element->description,
                'translatedDescription' => $translatedDescription !== '' ? $translatedDescription : $element->description,
                'icon' => $element->icon,
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

    protected function getElementCount($elementKey)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        return $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter(AffixUtility::addMaskCTypePrefix($elementKey))))
            ->execute()
            ->rowCount();
    }

    public function fieldTypes(ServerRequestInterface $request): Response
    {
        $json = [];
        $defaults = $this->configurationLoader->loadDefaults();
        $grouping = $this->configurationLoader->loadFieldGroups();
        foreach (FieldType::getConstants() as $type) {
            $config = [
                'name' => $type,
                'icon' => $this->iconFactory->getIcon('mask-fieldtype-' . $type)->getMarkup(),
                'fields' => [],
                'key' => '',
                'label' => '',
                'description' => '',
                'translatedLabel' => '',
                'itemLabel' => LocalizationUtility::translate('tx_mask.field.' . $type, 'mask'),
                'parent' => [],
                'group' => $grouping[$type],
                'newField' => true,
                'tca' => [
                    'l10n_mode' => ''
                ]
            ];
            if ($type === FieldType::CONTENT) {
                $config['tca']['cTypes'] = [];
            }
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
                        'label' => LocalizationUtility::translate('tx_mask.input', 'mask')
                    ],
                    [
                        'name' => 'text',
                        'label' => LocalizationUtility::translate('tx_mask.text', 'mask')
                    ],
                    [
                        'name' => 'date',
                        'label' => LocalizationUtility::translate('tx_mask.date', 'mask')
                    ],
                    [
                        'name' => 'choice',
                        'label' => LocalizationUtility::translate('tx_mask.choice', 'mask')
                    ],
                    [
                        'name' => 'special',
                        'label' => LocalizationUtility::translate('tx_mask.special', 'mask')
                    ],
                    [
                        'name' => 'repeating',
                        'label' => LocalizationUtility::translate('tx_mask.repeating', 'mask')
                    ],
                    [
                        'name' => 'structure',
                        'label' => LocalizationUtility::translate('tx_mask.structure', 'mask')
                    ]
                ]
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
     *
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function loadAllMultiUse(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $element = $this->tableDefinitionCollection->loadElement($params['table'], $params['elementKey']);

        if (!$element) {
            return new JsonResponse(['multiUseElements' => []]);
        }

        $multiUseElements = [];
        foreach ($element->tcaDefinition as $field) {
            if (!AffixUtility::hasMaskPrefix($field->fullKey)) {
                continue;
            }

            $fieldType = $this->tableDefinitionCollection->getFieldType($field->fullKey, $params['table']);

            // Get fields in palette
            if ($fieldType->equals(FieldType::PALETTE)) {
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $params['elementKey']) as $paletteField) {
                    $paletteFieldType = $this->tableDefinitionCollection->getFieldType($paletteField->fullKey, $params['table']);
                    if (!$paletteFieldType->canBeShared()) {
                        continue;
                    }
                    $multiUseElements[$paletteField->fullKey] = $this->getMultiUseForField($paletteField->fullKey, $params['elementKey']);
                }
                continue;
            }

            if (!$fieldType->canBeShared()) {
                continue;
            }

            $multiUseElements[$field->fullKey] = $this->getMultiUseForField($field->fullKey, $params['elementKey']);
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
            'Medical' => ['ambulance', 'h-square', 'heart', 'heart-o', 'heartbeat', 'hospital-o', 'medkit', 'stethoscope', 'user-md']
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
            ]
        ];

        $table = $request->getQueryParams()['table'];
        $type = $request->getQueryParams()['type'];
        $fields = ['mask' => [], 'core' => []];

        // Return empty result for non-shareable fields.
        if (!FieldType::cast($type)->canBeShared()) {
            return new JsonResponse($fields);
        }

        foreach (($GLOBALS['TCA'][$table]['columns'] ?? []) as $tcaField => $tcaConfig) {
            $isMaskField = AffixUtility::hasMaskPrefix($tcaField);
            // Skip the field, if it is not a mask field, AND it is not in the allow-list
            // AND it is also not an already defined field in the configuration.
            // This may be an extension field or a core field, which was previously allowed.
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

            if ($this->tableDefinitionCollection->getFieldType($tcaField, $table)->equals($type)) {
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

    protected function translateTcaFieldLabels($key, $field, $tcaFields)
    {
        $tcaFields[$key]['label'] = LocalizationUtility::translate($field['label'], 'mask');
        if (isset($field['placeholder'])) {
            $tcaFields[$key]['placeholder'] = LocalizationUtility::translate($field['placeholder'], 'mask');
        }
        if (isset($field['description'])) {
            $tcaFields[$key]['description'] = LocalizationUtility::translate($field['description'], 'mask');
        }
        if (isset($tcaFields[$key]['items'])) {
            foreach ($tcaFields[$key]['items'] as $itemKey => $item) {
                $tcaFields[$key]['items'][$itemKey] = LocalizationUtility::translate($item, 'mask');
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
                if (CompatibilityUtility::isFirstPartOfStr($type[0], 'LLL:')) {
                    $items[$type[1]] = LocalizationUtility::translate($type[0], 'mask');
                } else {
                    $items[$type[1]] = $type[0];
                }
            }
        }
        $json['ctypes'] = $items;
        return new JsonResponse($json);
    }

    public function tabs(ServerRequestInterface $request): Response
    {
        $tabs = [];
        foreach (FieldType::getConstants() as $type) {
            $tabs[$type] = $this->configurationLoader->loadTab($type);
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
            Tab::GENERATOR => 'tx_mask.tabs.generator'
        ];

        foreach ($tabs as $key => $tab) {
            $tabs[$key] = LocalizationUtility::translate($tab, 'mask');
        }
        $language['tabs'] = $tabs;

        $language['ok'] = LocalizationUtility::translate('tx_mask.ok', 'mask');
        $language['close'] = LocalizationUtility::translate('tx_mask.close', 'mask');
        $language['alert'] = LocalizationUtility::translate('tx_mask.alert', 'mask');
        $language['fieldsMissing'] = LocalizationUtility::translate('tx_mask.fieldsMissing', 'mask');
        $language['missingCreated'] = LocalizationUtility::translate('tx_mask.all.createdmissingfolders', 'mask');
        $language['reset'] = LocalizationUtility::translate('tx_mask.reset_settings_success', 'mask');
        $language['create'] = LocalizationUtility::translate('tx_mask.all.create', 'mask');

        $language['deleteModal'] = [
            'title' => LocalizationUtility::translate('tx_mask.field.titleDelete', 'mask'),
            'content' => LocalizationUtility::translate('tx_mask.all.confirmdelete', 'mask'),
            'close' => LocalizationUtility::translate('tx_mask.all.abort', 'mask'),
            'delete' => LocalizationUtility::translate('tx_mask.all.delete', 'mask'),
            'purge' => LocalizationUtility::translate('tx_mask.all.purge', 'mask'),
        ];

        $language['tooltip'] = [
            'editElement' => LocalizationUtility::translate('tx_mask.tooltip.edit_element', 'mask'),
            'deleteElement' => LocalizationUtility::translate('tx_mask.tooltip.delete_element', 'mask'),
            'enableElement' => LocalizationUtility::translate('tx_mask.tooltip.enable_element', 'mask'),
            'disableElement' => LocalizationUtility::translate('tx_mask.tooltip.disable_element', 'mask'),
            'html' => LocalizationUtility::translate('tx_mask.tooltip.html', 'mask'),
            'deleteField' => LocalizationUtility::translate('tx_mask.field.delete', 'mask'),
        ];

        $language['deleted'] = LocalizationUtility::translate('tx_mask.content.deletedcontentelement', 'mask');
        $language['icon'] = LocalizationUtility::translate('tx_mask.all.icon', 'mask');
        $language['color'] = LocalizationUtility::translate('tx_mask.all.color', 'mask');
        $language['usage'] = LocalizationUtility::translate('tx_mask.content.count', 'mask');
        $language['elementKey'] = LocalizationUtility::translate('tx_mask.all.fieldkey', 'mask');
        $language['elementLabel'] = LocalizationUtility::translate('tx_mask.all.fieldLabel', 'mask');

        $language['multistep'] = [
            'chooseKey' => LocalizationUtility::translate('tx_mask.multistep.chooseKey', 'mask'),
            'chooseLabel' => LocalizationUtility::translate('tx_mask.multistep.chooseKey', 'mask'),
            'text1' => LocalizationUtility::translate('tx_mask.multistep.text1', 'mask'),
            'text2' => LocalizationUtility::translate('tx_mask.multistep.text2', 'mask'),
            'placeholder1' => LocalizationUtility::translate('tx_mask.multistep.placeholder1', 'mask'),
            'placeholder2' => LocalizationUtility::translate('tx_mask.multistep.placeholder2', 'mask'),
        ];

        $language['createMissingFilesOrFolders'] = LocalizationUtility::translate('tx_mask.all.createmissingfolders', 'mask');
        $language['missingFolders'] = LocalizationUtility::translate('tx_mask.all.missingFolders', 'mask');
        $language['missingTemplates'] = LocalizationUtility::translate('tx_mask.all.missingTemplates', 'mask');

        return new JsonResponse($language);
    }

    public function richtextConfiguration(ServerRequestInterface $request): Response
    {
        $config[''] = LocalizationUtility::translate('tx_mask.config.richtextConfiguration.none', 'mask');
        $presets = array_keys($GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets'] ?? []);
        $presets = array_filter($presets, function ($item) {
            return $item !== 'sys_news';
        });
        $presets = array_combine($presets, $presets);
        $config = array_merge($config, $presets);
        return new JsonResponse($config);
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
                'mask' => ltrim(ExtensionManagementUtility::getExtensionVersion('mask'), 'v')
            ]
        );
    }

    /**
     * Generates all the necessary files
     */
    protected function generateAction(TableDefinitionCollection $tableDefinitionCollection): void
    {
        // Set tca to enable DefaultTcaSchema for new inline tables
        $tcaCodeGenerator = GeneralUtility::makeInstance(TcaCodeGenerator::class);
        $tcaCodeGenerator->setInlineTca($tableDefinitionCollection);

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
     * @param ServerRequest $request
     * @return Response
     */
    public function checkElementKey(ServerRequest $request): Response
    {
        $elementKey = $request->getQueryParams()['key'];
        $isAvailable = !$this->tableDefinitionCollection->loadElement('tt_content', $elementKey);

        return new JsonResponse(['isAvailable' => $isAvailable]);
    }

    /**
     * Checks if a key for a field is available.
     * Inline fields and content fields must not be used across elements.
     * Other "normal" fields can be used in different elements, but changes are applied for both.
     *
     * @param ServerRequest $request
     * @return Response
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
        return new JsonResponse(['isAvailable' => ($field === null || $field->type->equals($type))]);
    }

    /**
     * Creates a Message object and adds it to the FlashMessageQueue.
     *
     * @param string $messageBody The message
     * @param string $messageTitle Optional message title
     * @param int $severity Optional severity, must be one of \TYPO3\CMS\Core\Messaging\FlashMessage constants
     * @param bool $storeInSession Optional, defines whether the message should be stored in the session (default) or not
     * @throws \InvalidArgumentException if the message body is no string
     * @see \TYPO3\CMS\Core\Messaging\FlashMessage
     */
    public function addFlashMessage(string $messageBody, string $messageTitle = '', int $severity = AbstractMessage::OK, bool $storeInSession = true): void
    {
        if (!is_string($messageBody)) {
            throw new \InvalidArgumentException('The message body must be of type string, "' . gettype($messageBody) . '" given.', 1243258395);
        }
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
}
