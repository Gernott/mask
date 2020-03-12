<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {

    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'MASK.mask', 'tools', // Make module a submodule of 'admin'
        'mask', // Submodule key
        'top', // Position
        array(
            'Wizard' => 'list',
            'WizardContent' => 'new, create, edit, update, delete, purge, generate, showHtml, createMissingFolders, hide, activate, createHtml',
            'WizardPage' => 'new, create, edit, update, delete, showHtml',
        ), array(
            'access' => 'admin',
            'icon' => 'EXT:mask/Resources/Public/Icons/module-mask_wizard.svg',
            'labels' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf',
        )
    );
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('mask', 'Configuration/TypoScript', 'Mask');

// include css for styling of backend preview of mask content elements
$TBE_STYLES['skins']['mask']['name'] = 'mask';
$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = 'EXT:mask/Resources/Public/Styles/Backend/';
//$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = "/" . $settings["backend"];

$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Domain\Repository\StorageRepository::class);
$configuration = $storageRepository->load();

if (!empty($configuration)) {
    $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);

    // allow all inline tables on standard pages
    $tcaCodeGenerator->allowInlineTablesOnStandardPages($configuration);
}
