<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'config.elementBrowserEntryPoints._default' => 6,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
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
            'config.size' => 6,
            'config.autoSizeMax' => 6,
        ],
        [
            'config.multiple' => 6,
        ],
    ],
];
