<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'config.placeholder' => 6,
        ],
        [
            'config.size' => 6,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.eval.required' => 6,
            'config.fieldControl.linkPopup.options.allowedExtensions' => 6,
        ],
    ],
    Tab::FIELD_CONTROL => [
        [
            'config.fieldControl.linkPopup.options.blindLinkOptions' => 12,
            'config.fieldControl.linkPopup.options.blindLinkOptions.file' => 4,
            'config.fieldControl.linkPopup.options.blindLinkOptions.mail' => 4,
            'config.fieldControl.linkPopup.options.blindLinkOptions.page' => 4,
            'config.fieldControl.linkPopup.options.blindLinkOptions.folder' => 4,
            'config.fieldControl.linkPopup.options.blindLinkOptions.url' => 4,
            'config.fieldControl.linkPopup.options.blindLinkOptions.telephone' => 4,
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
        ],
    ],
];
