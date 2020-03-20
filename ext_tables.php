<?php
defined('TYPO3_MODE') or die();

(function ($extkey) {

    if (TYPO3_MODE === 'BE') {

        /**
         * Registers a Backend Module
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            $extkey,
            'tools', // Make module a submodule of 'admin'
            'mask', // Submodule key
            'top', // Position
            [
            	\MASK\Mask\Controller\WizardController::class => 'list',
                \MASK\Mask\Controller\WizardContentController::class => 'list, new, create, edit, update, delete, purge, generate, showHtml, createMissingFolders, hide, activate, createHtml',
                \MASK\Mask\Controller\WizardPageController::class => 'list, new, create, edit, update, delete, showHtml',
            ], [
                'access' => 'admin',
                'icon' => 'EXT:' . $extkey . '/Resources/Public/Icons/module-mask_wizard.svg',
                'labels' => 'LLL:EXT:' . $extkey . '/Resources/Private/Language/locallang_mask.xlf',
            ]
        );
    }
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extkey, 'Configuration/TypoScript', 'Mask');

    // include css for styling of backend preview of mask content elements
    $TBE_STYLES['skins']['mask']['name'] = 'mask';
    $TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = 'EXT:' . $extkey . '/Resources/Public/Styles/Backend/';
    //$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = "/" . $settings["backend"];

    $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\Domain\Repository\StorageRepository::class);
    $configuration = $storageRepository->load();

    if (!empty($configuration)) {
        $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MASK\Mask\CodeGenerator\TcaCodeGenerator::class);

        // allow all inline tables on standard pages
        $tcaCodeGenerator->allowInlineTablesOnStandardPages($configuration);
    }

})('mask');
