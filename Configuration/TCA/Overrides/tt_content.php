<?php

// if there is already a itemsProcFunc in the tt_content colPos tca, save it to another key for later usage
if (!empty($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'])) {
    $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['m_itemsProcFunc'] = $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'];
}
// if there is already a itemsProcFunc in the tt_content CType tca, save it to another key for later usage
if (!empty($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'])) {
    $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['m_itemsProcFunc'] = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'];
}
// and set mask itemsProcFuncs
$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'MASK\Mask\ItemsProcFuncs\ColPosList->itemsProcFunc';
$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemsProcFunc'] = MASK\Mask\ItemsProcFuncs\CTypeList::class . '->itemsProcFunc';

$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Domain\Repository\StorageRepository::class);
$configuration = $storageRepository->load();
$tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);

if (!empty($configuration) && array_key_exists('tt_content', $configuration)) {
    // Generate TCA for Content-Elements
    $contentColumns = $tcaCodeGenerator->generateFieldsTca($configuration['tt_content']['tca']);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $contentColumns);
    $tcaCodeGenerator->setElementsTca($configuration['tt_content']['elements']);
}

$tcaCodeGenerator->setInlineTca();
