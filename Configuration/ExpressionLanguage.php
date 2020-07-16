<?php

declare(strict_types=1);

use MASK\Mask\ExpressionLanguage\MaskProvider;

defined('TYPO3_MODE') or die('Access denied.');

return [
    // Add the condition provider to the 'typoscript' namespace
    'typoscript' => [
        MaskProvider::class
    ]
];
