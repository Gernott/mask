<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL => [
        [
            'config.default' => 6,
        ],
        [
            'config.relationship' => 12,
        ],
        [
            'config.treeConfig.startingPoints' => 12,
        ],
    ],
    Tab::VALIDATION => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
            'config.exclusiveKeys' => 6,
        ],
    ],
    Tab::APPEARANCE => [
        [
            'config.treeConfig.appearance.showHeader' => 6,
            'config.treeConfig.appearance.expandAll' => 6,
        ],
        [
            'config.treeConfig.appearance.nonSelectableLevels' => 6,
        ],
    ],
    Tab::LOCALIZATION => [
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
    ],
    Tab::EXTENDED => [
        [
            'config.size' => 6,
        ],
    ],
];
