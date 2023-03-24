<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

$validation = [
    'config.max' => 6,
];

if ((new Typo3Version())->getMajorVersion() > 11) {
    $validation = [
        'config.min' => 6,
        'config.max' => 6,
    ];
}

return [
    Tab::GENERAL => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
        [
            'config.cols' => 6,
            'config.rows' => 6,
        ],
    ],
    Tab::VALIDATION => [
        $validation,
        [
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.required' : 'config.eval.required' => 6,
            'config.eval.trim' => 6,
        ],
    ],
    Tab::VALUE_PICKER => [
        [
            'config.valuePicker.mode' => 6,
            'config.valuePicker.items' => 12,
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
    Tab::WIZARDS => [
        [
            'config.format' => 6,
        ],
    ],
    Tab::EXTENDED => [
        [
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.nullable' : 'config.eval.null' => 6,
            'config.mode' => 6,
            'config.fixedFont' => 6,
            'config.enableTabulator' => 6,
            'config.wrap' => 6,
        ],
    ],
];
