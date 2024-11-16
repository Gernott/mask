<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.elementBrowserEntryPoints._default' => 6,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    Tab::LOCALIZATION->value => [
        [
            'l10n_mode' => 12,
        ],
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
    ],
    Tab::EXTENDED->value => [
        [
            'config.size' => 6,
            'config.autoSizeMax' => 6,
        ],
        [
            'config.multiple' => 6,
        ],
    ],
];
