<?php
defined('TYPO3_MODE') or die();

(function ($extkey) {

    // initialize mask utility for various things
    $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\Domain\Repository\StorageRepository::class);
    $fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\Helper\FieldHelper::class);
    $typoScriptCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TyposcriptCodeGenerator::class);
    $settingsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\Domain\Service\SettingsService::class);
    $configuration = $storageRepository->load();
    $settings = $settingsService->get();

    // Register Icons needed in the backend module
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $maskIcons = [
        'Check',
        'Date',
        'Datetime',
        'File',
        'Float',
        'Inline',
        'Integer',
        'Link',
        'Radio',
        'Richtext',
        'Select',
        'String',
        'Tab',
        'Text',
        'Content'
    ];
    foreach ($maskIcons as $maskIcon) {
        $iconRegistry->registerIcon('mask-fieldtype-' . $maskIcon,
            TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
                'source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . $maskIcon . '.svg'
            ]);
    }

    // Add all the typoscript we need in the correct files
    $tsConfig = $typoScriptCodeGenerator->generateTsConfig($configuration);
    $pageTs = $typoScriptCodeGenerator->generatePageTyposcript($configuration);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($tsConfig);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTs);

    $setupTs = $typoScriptCodeGenerator->generateSetupTyposcript($configuration, $settings);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($setupTs);

    // set root line fields
    if (array_key_exists('pages', $configuration) && $configuration['pages']['tca']) {
        $rootlineFields = [];
        if ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] !== '') {
            $rootlineFields =  \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
        }
        foreach ($configuration['pages']['tca'] as $fieldKey => $value) {
            $formType = $fieldHelper->getFormType($fieldKey, '', 'pages');
            if ($formType !== 'Tab') {
                // Add addRootLineFields for all page fields
                $rootlineFields[] = $fieldKey;
            }
        }
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(',', $rootlineFields);
    }

    // Enhance Fluid Output with overridden FluidTemplateContentObject
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject::class] = [
        'className' => MASK\Mask\Fluid\FluidTemplateContentObject::class
    ];

    // Hook to override tt_content backend_preview
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$extkey] = \MASK\Mask\Hooks\PageLayoutViewDrawItem::class;
    // Hook to override colpos check for unused tt_content elements
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used'] [] = MASK\Mask\Hooks\PageLayoutViewHook::class . '->contentIsUsed';
    // Extend Page Tca Fields specific for backend layout
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][MASK\Mask\Form\FormDataProvider\TcaTypesShowitemMaskBeLayoutFields::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class
        ],
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class
        ]
    ];
})('mask');
