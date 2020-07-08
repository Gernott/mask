<?php

declare(strict_types=1);

$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Domain\Repository\StorageRepository::class);
$configuration = $storageRepository->load();
if (!empty($configuration) && array_key_exists('pages', $configuration)) {
    // Generate TCA for Pages
    $tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
    $pagesColumns = $tcaCodeGenerator->generateFieldsTca($configuration['pages']['tca']);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
}
