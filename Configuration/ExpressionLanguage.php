<?php
declare(strict_types=1);

defined('TYPO3_MODE') or die ('Access denied.');

return [
    // Add the condition provider to the 'typoscript' namespace
    'typoscript' => [
        \MASK\Mask\ExpressionLanguage\MaskProvider::class,
        \MASK\Mask\ExpressionLanguage\MaskContentTypeConditionProvider::class,
    ]
];
