<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'config.internal_type' => 6,
            'config.allowed' => 6,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    Tab::FIELD_CONTROL => [
        [
            'config.fieldControl' => 12,
            'config.fieldControl.editPopup.disabled' => 4,
            'config.fieldControl.addRecord.disabled' => 4,
            'config.fieldControl.listModule.disabled' => 4,
            'config.fieldControl.elementBrowser.disabled' => 4,
            'config.fieldControl.insertClipboard.disabled' => 4,
        ],
        [
            'config.fieldWizard' => 12,
            'config.fieldWizard.recordsOverview.disabled' => 4,
            'config.fieldWizard.tableList.disabled' => 4,
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
        [
            'config.multiple' => 6,
        ],
    ],
];
