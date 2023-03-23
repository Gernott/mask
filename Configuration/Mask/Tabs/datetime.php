<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

return [
    Tab::GENERAL => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.range.lower' => 6,
            'config.range.upper' => 6,
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.required' : 'config.eval.required' => 6,
        ],
    ],
    Tab::LOCALIZATION => [
        [
            'l10n_mode' => 12,
        ],
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
    ],
    Tab::EXTENDED => [
        [
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.nullable' : 'config.eval.null' => 6,
            'config.mode' => 6,
        ],
    ],
];
