<?php

defined('TYPO3_MODE') or die();

$tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
$pagesColumns = $tcaCodeGenerator->generateFieldsTca('pages');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
$tcaCodeGenerator->addSearchFields('pages');
