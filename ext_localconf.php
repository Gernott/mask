<?php

defined('TYPO3') or die();

// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject::class] = [
    'className' => \MASK\Mask\Fluid\FluidTemplateContentObject::class,
];

// Hook to override tt_content backend_preview
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mask'] = \MASK\Mask\Hooks\PageLayoutViewDrawItem::class;
// Hook to override colpos check for unused tt_content elements
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used'][] = \MASK\Mask\Hooks\PageLayoutViewHook::class . '->contentIsUsed';

// Extend Page Tca Fields specific for backend layout
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\MASK\Mask\Form\FormDataProvider\TcaTypesShowitemMaskBeLayoutFields::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRecordTypeValue::class,
    ],
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\TcaColumnsProcessCommon::class,
    ],
];

// Include css for styling of backend preview of mask content elements
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['mask'] = 'EXT:mask/Resources/Public/Styles/Backend';

// Include feature for using columnsOverrides on shared fields
$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
    ->get('mask');
$GLOBALS['TYPO3_CONF_VARS']['SYS']['features']['overrideSharedFields'] ??= (bool)($extensionConfiguration['override_shared_fields'] ?? false);

// Update wizards
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['convertTemplatesToUppercase'] = \MASK\Mask\Updates\ConvertTemplatesToUppercase::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['moveRteOptions'] = \MASK\Mask\Updates\MoveRteOptions::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['fillTranslationSourceField'] = \MASK\Mask\Updates\FillTranslationSourceField::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['migrateContentFields'] = \MASK\Mask\Updates\MigrateContentFields::class;

(static function () {
    // Register Icons needed in the backend module
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach (\MASK\Mask\Enumeration\FieldType::getConstants() as $maskIcon) {
        $iconRegistry->registerIcon(
            'mask-fieldtype-' . $maskIcon,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($maskIcon) . '.svg']
        );
    }

    // Add all the typoscript we need in the correct files
    $typoScriptCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TyposcriptCodeGenerator::class);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generateTsConfig());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generatePageTSConfigOverridesForBackendLayouts());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($typoScriptCodeGenerator->generateSetupTyposcript());

    /** @var \MASK\Mask\Definition\TableDefinitionCollection $tables */
    $tables = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Loader\LoaderRegistry::class)->loadActiveDefinition();
    if ($tables->hasTable('pages')) {
        $rootlineFields = [];
        if ($GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] !== '') {
            $rootlineFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
        }
        foreach ($tables->getTable('pages')->tca as $field) {
            if ($field->hasFieldType() && !$field->getFieldType()->isGroupingField()) {
                // Add addRootLineFields for all page fields
                $rootlineFields[] = $field->fullKey;
            }
        }
        $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(',', $rootlineFields);
    }
})();
