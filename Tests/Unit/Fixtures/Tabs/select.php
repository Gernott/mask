<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'config.renderType' => 6,
        ],
        [
            'config.items' => 12,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    Tab::DATABASE => [
        [
            'config.foreign_table' => 6,
        ],
        [
            'config.foreign_table_where' => 12,
        ],
    ],
    Tab::FILES => [
        [
            'config.fileFolder' => 6,
            'config.fileFolder_extList' => 6,
            'config.fileFolder_recursions' => 6,
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
    ],
];
