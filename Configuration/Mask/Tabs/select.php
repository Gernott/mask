<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.renderType' => 6,
            'config.default' => 6,
        ],
        [
            'config.items' => 12,
        ],
    ],
    Tab::ITEM_GROUP_SORTING->value => [
        [
            'config.itemGroups' => 12,
            'config.sortItems' => 12,
        ],
    ],
    Tab::VALIDATION->value => [
        [
            'config.minitems' => 6,
            'config.maxitems' => 6,
        ],
    ],
    Tab::DATABASE->value => [
        [
            'config.foreign_table' => 12,
        ],
        [
            'config.foreign_table_where' => 12,
        ],
    ],
    Tab::FILES->value => [
        [
            'config.fileFolder' => 6,
            'config.fileFolder_extList' => 6,
            'config.fileFolder_recursions' => 6,
            'config.fileFolderConfig.folder' => 6,
            'config.fileFolderConfig.allowedExtensions' => 6,
            'config.fileFolderConfig.depth' => 6,
            'config.fieldWizard.selectIcons.disabled' => 6,
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
            'config.size' => 6,
            'config.autoSizeMax' => 6,
        ],
        [
            'config.appearance.expandAll' => 6,
        ],
    ],
];
