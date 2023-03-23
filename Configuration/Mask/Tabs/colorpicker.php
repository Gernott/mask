<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

return [
    Tab::GENERAL => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
        [
            'config.size' => 6,
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
    Tab::EXTENDED => [
        [
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.nullable' : 'config.eval.null' => 6,
            'config.mode' => 6,
        ],
    ],
];
