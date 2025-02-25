<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
        [
            'config.size' => 6,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.eval.required' => 6,
            'config.max' => 6,
        ],
        [
            'config.range.lower' => 6,
            'config.range.upper' => 6,
        ],
    ],
    Tab::FIELD_CONTROL->value => [
        [
            'config.slider.step' => 6,
            'config.slider.width' => 6,
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
            'config.autocomplete' => 6,
        ],
    ],
];
