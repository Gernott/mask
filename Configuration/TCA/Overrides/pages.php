<?php

$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
$configuration = $storageRepository->load();

if (!empty($configuration) && array_key_exists('pages', $configuration)) {

    $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\CodeGenerator\\TcaCodeGenerator');

    // Generate TCA for Pages
    $pagesColumns = $tcaCodeGenerator->generateFieldsTca($configuration["pages"]["tca"]);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $pagesColumns);
    $tcaCodeGenerator->setPageTca($configuration["pages"]["tca"]);

    // Generate TCA for Inline-Fields
    $tcaCodeGenerator->setInlineTca($configuration);
}