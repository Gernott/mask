<?php

defined('TYPO3') or die();

// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject::class] = [
    'className' => \MASK\Mask\Fluid\FluidTemplateContentObject::class,
];

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

(static function () {
    // Add all the typoscript we need in the correct files
    $typoScriptCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TyposcriptCodeGenerator::class);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generateTsConfig());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($typoScriptCodeGenerator->generatePageTSConfigOverridesForBackendLayouts());
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($typoScriptCodeGenerator->generateSetupTyposcript());

    // addRootLineFields are removed in TYPO3 v13 and are always resolved.
    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() < 13) {
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
    }
})();
