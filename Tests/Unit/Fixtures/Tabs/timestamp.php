<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.eval' => 6,
        ],
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.range.lower' => 6,
            'config.range.upper' => 6,
            'config.eval.required' => 6,
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
            'config.eval.null' => 6,
            'config.mode' => 6,
        ],
    ],
];
