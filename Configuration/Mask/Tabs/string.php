<?php

use MASK\Mask\Enumeration\Tab;
use TYPO3\CMS\Core\Information\Typo3Version;

$validation = [
    'config.eval.required' => 6,
    'config.eval.trim' => 6,
    'config.eval.alpha' => 6,
    'config.eval.num' => 6,
    'config.eval.alphanum' => 6,
    'config.eval.alphanum_x' => 6,
    'config.eval.domainname' => 6,
    'config.eval.email' => 6,
    'config.eval.lower' => 6,
    'config.eval.upper' => 6,
    'config.eval.unique' => 6,
    'config.eval.uniqueInPid' => 6,
    'config.eval.nospace' => 6,
];

if ((new Typo3Version())->getMajorVersion() > 11) {
    unset($validation['config.eval.email']);
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
        [
            'config.max' => 6,
            'config.is_in' => 6,
        ],
        $validation,
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
            'config.eval.null' => 6,
            'config.mode' => 6,
            'config.eval.md5' => 6,
            'config.eval.password' => 6,
            'config.autocomplete' => 6,
        ],
    ],
];
