<?php

defined('TYPO3_MODE') or die ('Access denied.');

return [
    // Add the condition provider to the 'typoscript' namespace
    'typoscript' => [
        \MASK\Mask\ExpressionLanguage\MaskContentTypeConditionProvider::class,
    ]
];
