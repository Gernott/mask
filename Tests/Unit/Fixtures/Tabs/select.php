<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.renderType' => 6,
        ],
        [
            'config.items' => 12,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    Tab::DATABASE->value => [
        [
            'config.foreign_table' => 6,
        ],
        [
            'config.foreign_table_where' => 12,
        ],
    ],
    Tab::FILES->value => [
        [
            'config.fileFolder' => 6,
            'config.fileFolder_extList' => 6,
            'config.fileFolder_recursions' => 6,
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
    ],
];
