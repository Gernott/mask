<?php

defined('TYPO3') or die();

$tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
$pagesColumns = $tcaCodeGenerator->generateFieldsTca('pages');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
$GLOBALS['TCA']['pages']['ctrl']['searchFields'] = $tcaCodeGenerator->addSearchFields('pages');
