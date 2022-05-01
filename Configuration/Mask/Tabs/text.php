<?php

use MASK\Mask\Enumeration\Tab;

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
        [
            'config.max' => 6,
        ],
        [
            'config.eval.required' => 6,
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
            'config.eval.null' => 6,
            'config.mode' => 6,
            'config.fixedFont' => 6,
            'config.enableTabulator' => 6,
            'config.wrap' => 6,
        ],
    ],
];
