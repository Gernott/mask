<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'imageoverlayPalette' => 6,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    TAB::APPEARANCE => [
        [
            'allowedFileExtensions' => 6,
        ],
        [
            'config.appearance.collapseAll' => 6,
            'config.appearance.expandSingle' => 6,
            'config.appearance.useSortable' => 6,
            'config.appearance.fileUploadAllowed' => 6,
        ],
    ],
    Tab::LOCALIZATION => [
        [
            'l10n_mode' => 12,
        ],
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
        [
            'config.appearance.showSynchronizationLink' => 6,
            'config.appearance.showPossibleLocalizationRecords' => 6,
            'config.appearance.showAllLocalizationLink' => 6,
            'config.appearance.showRemovedLocalizationRecords' => 6,
        ],
    ],
];
