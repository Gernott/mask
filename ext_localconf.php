<?php

defined('TYPO3_MODE') or die();

// Add mask as global fluid namespace
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['mask'][] = 'MASK\\Mask\\ViewHelpers';

// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject::class] = [
    'className' => \MASK\Mask\Fluid\FluidTemplateContentObject::class
];

// Hook to override tt_content backend_preview
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mask'] = \MASK\Mask\Hooks\PageLayoutViewDrawItem::class;
// Hook to override colpos check for unused tt_content elements
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used'][] = \MASK\Mask\Hooks\PageLayoutViewHook::class . '->contentIsUsed';

// Extend Page Tca Fields specific for backend layout
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\MASK\Mask\Form\FormDataProvider\TcaTypesShowitemMaskBeLayoutFields::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class
    ],
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class
    ]
];

// Update wizards
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['removeRichtextConfiguration'] = \MASK\Mask\Updates\RemoveRichtextConfiguration::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['convertTemplatesToUppercase'] = \MASK\Mask\Updates\ConvertTemplatesToUppercase::class;

(function () {
    // Register Icons needed in the backend module
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach (\MASK\Mask\DataStructure\FieldType::getConstants() as $maskIcon) {
        $iconRegistry->registerIcon(
            'mask-fieldtype-' . $maskIcon,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($maskIcon) . '.svg']
        );
    }

    // Add all the typoscript we need in the correct files
    $typoScriptCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TyposcriptCodeGenerator::class);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generateTsConfig());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generatePageTyposcript());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($typoScriptCodeGenerator->generateSetupTyposcript());

    $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Domain\Repository\StorageRepository::class);
    $configuration = $storageRepository->load();
    if (array_key_exists('pages', $configuration) && $configuration['pages']['tca']) {
        $rootlineFields = [];
        if ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] !== '') {
            $rootlineFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
        }
        foreach ($configuration['pages']['tca'] as $fieldKey => $value) {
            $formType = $storageRepository->getFormType($fieldKey, '', 'pages');
            if (!(\MASK\Mask\DataStructure\FieldType::cast($formType)->isGroupingField())) {
                // Add addRootLineFields for all page fields
                $rootlineFields[] = $fieldKey;
            }
        }
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(',', $rootlineFields);
    }
})();
