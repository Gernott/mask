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
use MASK\Mask\Event\MaskAfterElementDeletedEvent;
use MASK\Mask\Event\MaskAfterElementSavedEvent;
use MASK\Mask\Event\MaskAllowedFieldsEvent;
use MASK\Mask\Loader\LoaderInterface;
use MASK\Mask\Utility\AffixUtility;
use MASK\Mask\Utility\OverrideFieldsUtility;
use MASK\Mask\Utility\TemplatePathUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * @internal
 */
class AjaxController
{
    protected StorageRepository $storageRepository;
    protected IconFactory $iconFactory;
    protected SqlCodeGenerator $sqlCodeGenerator;
    protected HtmlCodeGenerator $htmlCodeGenerator;
    protected BackendLayoutRepository $backendLayoutRepository;
    protected ResourceFactory $resourceFactory;
    protected ConfigurationLoader $configurationLoader;
    protected FlashMessageQueue $flashMessageQueue;
    protected OnlineMediaHelperRegistry $onlineMediaHelperRegistry;
    protected TableDefinitionCollection $tableDefinitionCollection;
    protected LoaderInterface $loader;
    protected EventDispatcherInterface $eventDispatcher;
    protected Features $features;

    /**
     * @var array<string, string>
     */
    protected array $maskExtensionConfiguration;

    /**
     * @var string[]
     */
    protected static array $folderPathKeys = [
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
        EventDispatcherInterface $eventDispatcher,
        Features $features,
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
        $this->eventDispatcher = $eventDispatcher;
        $this->features = $features;
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

        $extensionConfiguration->set('mask', $configuration);

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
                $this->addFlashMessage('Failed to create directory: ' . $missingFolderPath, '', ContextualFeedbackSeverity::ERROR);
            }
        }

        if (!$this->tableDefinitionCollection->hasTable('tt_content')) {
            return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush()]);
        }

        $success = true;
        $messages = [];
        $numberTemplateFilesCreated = 0;
        foreach ($this->tableDefinitionCollection->getTable('tt_content')->elements as $element) {
            if (!$this->contentElementTemplateExists($element->key)) {
                try {
                    $success &= $this->createHtml($element->key);
                    $numberTemplateFilesCreated++;
                } catch (\Exception $e) {
                    $success = false;
                    $messages[] = $e->getMessage();
                }
            }
        }

        if (!$success) {
            $this->addFlashMessage('Failed to create template files. See errors below.', '', ContextualFeedbackSeverity::ERROR);
            foreach ($messages as $message) {
                $this->addFlashMessage($message, '', ContextualFeedbackSeverity::ERROR);
            }
        }

        if ($numberTemplateFilesCreated > 0 && $success) {
            $this->addFlashMessage('Successfully created ' . $numberTemplateFilesCreated . ' template files.');
        }

        return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush()]);
    }

    /**
     * Generates Fluid HTML for Content elements
     */
    public function showHtmlAction(ServerRequestInterface $request): Response
    {
        $params = $request->getQueryParams();
        $html = $this->htmlCodeGenerator->generateHtml($params['key'], $params['table']);
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('mask');
        $view->setTemplate('Ajax/ShowHtml');
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
            $this->addFlashMessage($e->getMessage(), '', ContextualFeedbackSeverity::ERROR);
            return new JsonResponse(['messages' => $this->flashMessageQueue->getAllMessagesAndFlush(), 'hasError' => 1]);
        }
        $this->generateAction($tableDefinitionCollection);
        if ($params['type'] === 'tt_content') {
            try {
                $this->createHtml($elementKey);
            } catch (\Exception $e) {
                $this->addFlashMessage('Creating template file has failed. See error below.', '', ContextualFeedbackSeverity::ERROR);
                $this->addFlashMessage($e->getMessage(), '', ContextualFeedbackSeverity::ERROR);
            }
        }
        if ($isNew) {
            $this->addFlashMessage($this->translateLabel('tx_mask.content.newcontentelement'));
        } else {
            $this->addFlashMessage($this->translateLabel('tx_mask.content.updatedcontentelement'));
        }

        $this->eventDispatcher->dispatch(
            new MaskAfterElementSavedEvent($tableDefinitionCollection, $elementKey, $isNew)
        );

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

        $this->eventDispatcher->dispatch(
            new MaskAfterElementDeletedEvent($tableDefinitionCollection, $params['key'])
        );

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
                'translatedDescription' => $translatedDescription !== $element->description ? $translatedDescription : '',
                'icon' => $element->icon,
                'iconOverlay' => $element->iconOverlay,
                'key' => $element->key,
                'label' => $element->label,
                'translatedLabel' => $translatedLabel !== $element->label ? $translatedLabel : '',
                'shortLabel' => $element->shortLabel,
                'iconMarkup' => $this->iconFactory->getIcon('mask-ce-' . $element->key, 'medium', $overlay)->render(),
                'templateExists' => $this->contentElementTemplateExists($element->key) ? 1 : 0,
                'hidden' => $element->hidden ? 1 : 0,
                'count' => $this->getElementCount($element->key),
                'sorting' => $element->sorting,
                'saveAndClose' => $element->saveAndClose ? 1 : 0,
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
            ->executeQuery()
            ->rowCount();
    }

    public function fieldTypes(ServerRequestInterface $request): Response
    {
        $json = [];
        //        $availability = [
        //            FieldType::EMAIL => 12,
        //            FieldType::FOLDER => 12,
        //        ];
        //        $typo3Version = new Typo3Version();
        $defaults = $this->configurationLoader->loadDefaults();
        $grouping = $this->configurationLoader->loadFieldGroups();
        foreach (FieldType::getConstants() as $type) {
            //            if (isset($availability[$type]) && $typo3Version->getMajorVersion() < $availability[$type]) {
            //                continue;
            //            }

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
                foreach ($this->tableDefinitionCollection->loadInlineFields($field->fullKey, $elementKey, $element->elementDefinition) as $paletteField) {
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
            static function($item) use ($elementKey) {
                return $item['key'] !== $elementKey;
            }
        );

        $multiUseElements = array_map(
            static function($item) {
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

    public function restructuringNeeded(ServerRequestInterface $request): JsonResponse
    {
        $overrideSharedFields = $this->features->isFeatureEnabled('overrideSharedFields');
        if (!$overrideSharedFields) {
            return new JsonResponse(['restructuringNeeded' => 0]);
        }
        $needsRestructure = $this->tableDefinitionCollection->isRestructuringNeeded();
        return new JsonResponse(['restructuringNeeded' => (int)$needsRestructure]);
    }

    public function executeRestructuring(ServerRequestInterface $request): Response
    {
        $restructuredTableDefinitionCollection = OverrideFieldsUtility::restructureTcaDefinitions($this->tableDefinitionCollection);
        try {
            $this->loader->write($restructuredTableDefinitionCollection);
            return new JsonResponse(['status' => 'ok', 'title' => $this->translateLabel('tx_mask.update_complete.title'), 'message' => $this->translateLabel('tx_mask.update_complete.message')]);
        } catch (\Throwable $e) {
            return new JsonResponse(['status' => 'error', 'title' => $this->translateLabel('tx_mask.update_failed.title'), 'message' => $this->translateLabel('tx_mask.update_failed.message')]);
        }
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

        /** @var MaskAllowedFieldsEvent $allowedFieldsEvent */
        $allowedFieldsEvent = $this->eventDispatcher->dispatch(
            new MaskAllowedFieldsEvent($allowedFields)
        );
        $allowedFields = $allowedFieldsEvent->getAllowedFields();

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
            $items[$tableKey] = $this->getLanguageService()->sL($table['ctrl']['title'] ?? $tableKey);
        }

        ksort($items);
        $json['foreignTables'] = $items;
        return new JsonResponse($json);
    }

    /**
     * @param array<string, array<string, string|array<string, mixed>>> $tcaFields
     * @return array<string, array<string, string|array<string, mixed>>>
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
            $label = $type[0] ?? $type['label'];
            $value = $type[1] ?? $type['value'];
            if ($value !== '--div--') {
                $items[$value] = $this->getLanguageService()->sL($label);
            }
        }
        $json['ctypes'] = $items;
        return new JsonResponse($json);
    }

    public function tabs(ServerRequestInterface $request): Response
    {
        //        $availability = [
        //            FieldType::CATEGORY => 11,
        //        ];
        //        $typo3Version = new Typo3Version();
        $tabs = [];
        $availableTcaFields = $this->configurationLoader->loadTcaFields();
        foreach (FieldType::getConstants() as $type) {
            //            if (isset($availability[$type]) && $typo3Version->getMajorVersion() < $availability[$type]) {
            //                continue;
            //            }
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
        $language['restructuringNeededTitle'] = $this->translateLabel('tx_mask.restructuring_needed.title');
        $language['restructuringNeededMessage'] = $this->translateLabel('tx_mask.restructuring_needed.message');
        $language['executeRestructuring'] = $this->translateLabel('tx_mask.execute_restructuring');
        $language['selectedItems'] = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.selected');
        $language['availableItems'] = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.items');
        $language['multiuseTitle'] = $this->translateLabel('tx_mask.content.multiuse');
        $language['multiuseMessage'] = $this->translateLabel('tx_mask.content.multiuse.description');

        return new JsonResponse($language);
    }

    public function nonOverrideableOptions(ServerRequestInterface $request): Response
    {
        return new JsonResponse(TcaFieldDefinition::NON_OVERRIDEABLE_OPTIONS);
    }

    public function features(ServerRequestInterface $request): Response
    {
        $featuresList['overrideSharedFields'] = [
            'title' => $this->translateLabel('tx_mask.features.overrideSharedFields'),
            'state' => (int)$this->features->isFeatureEnabled('overrideSharedFields'),
            'documentation' => 'https://docs.typo3.org/p/mask/mask/main/en-us/ChangeLog/8.2/Index.html',
        ];

        return new JsonResponse($featuresList);
    }

    public function richtextConfiguration(ServerRequestInterface $request): Response
    {
        $config[''] = $this->translateLabel('tx_mask.config.richtextConfiguration.none');
        $presets = array_keys($GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets'] ?? []);
        $presets = array_filter($presets, function($item) {
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
        $linkHandlerResponse[] = [
            'identifier' => 'record',
            'label' => $this->translateLabel('tx_mask.genericLinkhandlerRecord'),
        ];

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
        $typo3Version = new Typo3Version();
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
            $this->addFlashMessage($result['error'], '', ContextualFeedbackSeverity::ERROR);
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

        // All other fields must be unique per table. Exception: If a field
        // with the same field type exists, it may be shared.
        $field = $this->tableDefinitionCollection->loadField($table, $fieldKey);

        return new JsonResponse(['isAvailable' => (!$field instanceof TcaFieldDefinition || !$field->hasFieldType($elementKey) || $field->getFieldType($elementKey)->equals($type))]);
    }

    protected function addFlashMessage(string $messageBody, string $messageTitle = '', ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK, bool $storeInSession = true): void
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
        $path = GeneralUtility::getFileAbsFileName($path);
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
            $path = GeneralUtility::getFileAbsFileName($this->maskExtensionConfiguration[$key]);
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
