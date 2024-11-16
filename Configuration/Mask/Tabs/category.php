<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
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
    Tab::VALIDATION->value => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
            'config.exclusiveKeys' => 6,
        ],
    ],
    Tab::APPEARANCE->value => [
        [
            'config.treeConfig.appearance.showHeader' => 6,
            'config.treeConfig.appearance.expandAll' => 6,
        ],
        [
            'config.treeConfig.appearance.nonSelectableLevels' => 6,
        ],
    ],
    Tab::LOCALIZATION->value => [
        [
            'config.behaviour.allowLanguageSynchronization' => 6,
        ],
    ],
    Tab::EXTENDED->value => [
        [
            'config.size' => 6,
        ],
    ],
];
