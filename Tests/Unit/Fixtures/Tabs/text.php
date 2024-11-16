<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
        [
            'config.cols' => 6,
            'config.rows' => 6,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.max' => 6,
        ],
        [
            'config.eval.required' => 6,
            'config.eval.trim' => 6,
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
    Tab::WIZARDS->value => [
        [
            'config.format' => 6,
        ],
    ],
    Tab::EXTENDED->value => [
        [
            'config.eval.null' => 6,
            'config.mode' => 6,
            'config.fixedFont' => 6,
            'config.enableTabulator' => 6,
            'config.wrap' => 6,
        ],
    ],
];
