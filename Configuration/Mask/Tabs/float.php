<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

$validation = [
    (new Typo3Version())->getMajorVersion() > 11 ? 'config.required' : 'config.eval.required' => 6,
];

if ((new Typo3Version())->getMajorVersion() === 11) {
    $validation['config.max'] = 6;
}

return [
    Tab::GENERAL => [
        [
            'config.default' => 6,
            'config.placeholder' => 6,
        ],
        [
            'config.size' => 6,
        ],
    ],
    Tab::VALIDATION => [
        $validation,
        [
            'config.range.lower' => 6,
            'config.range.upper' => 6,
        ],
    ],
    Tab::FIELD_CONTROL => [
        [
            'config.slider.step' => 6,
            'config.slider.width' => 6,
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
    Tab::EXTENDED => [
        [
            (new Typo3Version())->getMajorVersion() > 11 ? 'config.nullable' : 'config.eval.null' => 6,
            'config.mode' => 6,
            'config.autocomplete' => 6,
        ],
    ],
];
