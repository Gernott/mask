<?php

defined('TYPO3') or die();

(static function () {
    // Allow all inline tables on standard pages
    $tables = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\MASK\Mask\Loader\LoaderRegistry::class)->loadActiveDefinition();
    foreach ($tables->getCustomTables() as $tableDefinition) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($tableDefinition->table);
    }
})();
