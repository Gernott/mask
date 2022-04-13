<?php

defined('TYPO3') or die();

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

$tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
$contentColumns = $tcaCodeGenerator->generateFieldsTca('tt_content');
\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA'], $tcaCodeGenerator->generateTCAColumnsOverrides('tt_content'));
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $contentColumns);
$tcaCodeGenerator->setInlineTca();
$tcaCodeGenerator->setElementsTca();
$GLOBALS['TCA']['tt_content']['ctrl']['searchFields'] = $tcaCodeGenerator->addSearchFields('tt_content');
$GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['search']['andWhere'] .= $tcaCodeGenerator->extendBodytextSearchAndWhere();
