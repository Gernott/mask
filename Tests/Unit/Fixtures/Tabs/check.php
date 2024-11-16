<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.renderType' => 6,
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
            'config.items' => 12,
        ],
        [
            'config.default' => 6,
        ],
        [
            'config.cols' => 6,
        ],
    ],
];
