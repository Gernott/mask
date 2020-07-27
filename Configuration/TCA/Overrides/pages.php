<?php

declare(strict_types=1);

$tcaCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\CodeGenerator\TcaCodeGenerator::class);
$pagesColumns = $tcaCodeGenerator->generateFieldsTca('pages');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
$tcaCodeGenerator->addSearchFields('pages');
