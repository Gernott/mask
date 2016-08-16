<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {

    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'MASK.' . $_EXTKEY, 'tools', // Make module a submodule of 'admin'
        'mask', // Submodule key
        'top', // Position
        array(
        'WizardContent' => 'list, new, create, edit, update, delete, purge, generate, showHtml, createMissingFolders, hide, activate',
        'WizardPage' => 'list, new, create, edit, update, delete',
        ), array(
        'access' => 'admin',
        'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module-mask_wizard.svg',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mask.xlf',
        )
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'WizardController::checkFieldKey', 'MASK\Mask\Controller\WizardController->checkFieldKey'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'WizardController::checkElementKey', 'MASK\Mask\Controller\WizardController->checkElementKey'
    );
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Mask');

$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
$configuration = $storageRepository->load();

if (!empty($configuration)) {

    $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\CodeGenerator\\TcaCodeGenerator');

    // Generate TCA for Content-Elements
    $contentColumns = $tcaCodeGenerator->generateFieldsTca($configuration["tt_content"]["tca"]);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $contentColumns);
    $tcaCodeGenerator->setElementsTca($configuration["tt_content"]["elements"]);

    // Generate TCA for Pages
    $pagesColumns = $tcaCodeGenerator->generateFieldsTca($configuration["pages"]["tca"]);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $pagesColumns);
    $tcaCodeGenerator->setPageTca($configuration["pages"]["tca"]);

    // Generate TCA for Inline-Fields
    $tcaCodeGenerator->setInlineTca($configuration);
}

// include css for styling of backend preview of mask content elements
$TBE_STYLES['skins']['mask']['name'] = 'mask';
$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = 'EXT:mask/Resources/Public/Styles/Backend/';
//$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = "/" . $settings["backend"];


