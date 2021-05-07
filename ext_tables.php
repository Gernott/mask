<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'tools',
    'mask',
    'after:extensionmanager',
    null,
    [
        'routeTarget' => \MASK\Mask\Controller\MaskController::class . '::mainAction',
        'access' => 'admin',
        'name' => 'tools_mask',
        'icon' => 'EXT:mask/Resources/Public/Icons/module-mask_wizard.svg',
        'labels' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf',
    ]
);

// Include css for styling of backend preview of mask content elements
$GLOBALS['TBE_STYLES']['skins']['mask']['stylesheetDirectories']['mask'] = 'EXT:mask/Resources/Public/Styles/Backend/';

(function () {
    // Allow all inline tables on standard pages
    $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
    $irreTables = $tcaCodeGenerator->getMaskIrreTables();
    foreach ($irreTables as $table) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($table);
    }
})();
