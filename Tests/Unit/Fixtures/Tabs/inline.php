<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'ctrl.label' => 6,
            'ctrl.iconfile' => 6,
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
            'config.appearance.newRecordLinkTitle' => 6,
            'config.appearance.levelLinksPosition' => 6,
        ],
        [
            'config.appearance.collapseAll' => 6,
            'config.appearance.expandSingle' => 6,
            'config.appearance.useSortable' => 6,
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
