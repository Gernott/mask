<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

$validation = [
    (new Typo3Version())->getMajorVersion() > 11 ? 'config.required' : 'config.eval.required' => 6,
];

return [
    Tab::GENERAL => [
        [
            'config.richtextConfiguration' => 6,
        ],
        [
            'config.default' => 6,
        ],
    ],
    Tab::VALIDATION => [
        $validation,
    ],
    Tab::LOCALIZATION => [
        [
            'l10n_mode' => 12,
        ],
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
    ],
];
