<?php

use MASK\Mask\Enumeration\FieldType;

return [
    FieldType::STRING => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::FLOAT => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'number',
            'config.format' => 'decimal',
        ],
        'sql' => 'float DEFAULT \'0\' NOT NULL',
    ],
    FieldType::INTEGER => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'number',
        ],
        'sql' => 'int(11) DEFAULT \'0\' NOT NULL',
    ],
    FieldType::LINK => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.nullable' => 0,
            'config.allowedTypes' => [],
        ],
        'tca_out' => [
            'config.type' => 'link',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::COLORPICKER => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.nullable' => 0,
            'config.size' => 10,
        ],
        'tca_out' => [
            'config.type' => 'color',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::SLUG => [
        'tca_in' => [
            'config.size' => 10,
            'config.eval.slug' => 'uniqueInPid',
            'config.generatorOptions.replacements' => [],
            'config.generatorOptions.fieldSeparator' => '/',
            'config.fallbackCharacter' => '-',
        ],
        'tca_out' => [
            'config.type' => 'slug',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::DATE => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'datetime',
            'config.dbType' => 'date',
            'config.format' => 'date',
        ],
        'sql' => 'date',
    ],
    FieldType::DATETIME => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'datetime',
            'config.dbType' => 'datetime',
            'config.format' => 'datetime',
        ],
        'sql' => 'datetime',
    ],
    FieldType::TIMESTAMP => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.format' => 'date',
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'datetime',
        ],
        'sql' => 'int(10) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::TEXT => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.wrap' => 'virtual',
            'config.format' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'text',
        ],
        'sql' => 'mediumtext',
    ],
    FieldType::RICHTEXT => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.richtextConfiguration' => '',
        ],
        'tca_out' => [
            'config.type' => 'text',
            'config.enableRichtext' => 1,
        ],
        'sql' => 'mediumtext',
    ],
    FieldType::CHECK => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.renderType' => '',
            'config.items' => [],
        ],
        'tca_out' => [
            'config.type' => 'check',
        ],
        'sql' => 'int(11) DEFAULT \'0\' NOT NULL',
    ],
    FieldType::SELECT => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.renderType' => 'selectSingle',
            'config.itemGroups' => [],
            'config.sortItems' => [],
            'config.items' => [],
            'config.fieldWizard.selectIcons.disabled' => 1,
        ],
        'tca_out' => [
            'config.type' => 'select',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::CATEGORY => [
        'tca_in' => [
            'config.relationship' => 'manyToMany',
            'config.treeConfig.appearance.showHeader' => 1,
            'config.treeConfig.appearance.expandAll' => 1,
            'config.treeConfig.appearance.nonSelectableLevels' => '0',
        ],
        'tca_out' => [
            'config.type' => 'category',
        ],
    ],
    FieldType::RADIO => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.items' => [],
        ],
        'tca_out' => [
            'config.type' => 'radio',
        ],
        'sql' => 'int(11) DEFAULT \'0\' NOT NULL',
    ],
    FieldType::GROUP => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.allowed' => '',
            'config.fieldControl.editPopup.disabled' => 1,
            'config.fieldControl.addRecord.disabled' => 1,
            'config.fieldControl.listModule.disabled' => 1,
        ],
        'tca_out' => [
            'config.type' => 'group',
        ],
        'sql' => 'text',
    ],
    FieldType::FOLDER => [
        'tca_in' => [
            'l10n_mode' => '',
        ],
        'tca_out' => [
            'config.type' => 'folder',
        ],
        'sql' => 'text',
    ],
    FieldType::FILE => [
        'tca_in' => [
            'l10n_mode' => '',
            'imageoverlayPalette' => 1,
            'config.appearance.elementBrowserEnabled' => 1,
            'config.appearance.fileUploadAllowed' => 1,
            'config.appearance.collapseAll' => '',
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 0,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::MEDIA => [
        'tca_in' => [
            'l10n_mode' => '',
            'onlineMedia' => ['youtube', 'vimeo'],
            'config.appearance.elementBrowserEnabled' => 1,
            'config.appearance.fileUploadAllowed' => 1,
            'config.appearance.fileByUrlAllowed' => 1,
            'config.appearance.collapseAll' => '',
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 0,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::INLINE => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.appearance.collapseAll' => '',
            'config.appearance.levelLinksPosition' => 'top',
            'config.appearance.showPossibleLocalizationRecords' => 1,
            'config.appearance.showAllLocalizationLink' => 1,
            'config.appearance.showNewRecordLink' => 1,
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.new' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 1,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => '--inlinetable--',
            'config.foreign_field' => 'parentid',
            'config.foreign_table_field' => 'parenttable',
            'config.foreign_sortby' => 'sorting',
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::CONTENT => [
        'tca_in' => [
            'l10n_mode' => '',
            'cTypes' => [],
            'config.appearance.levelLinksPosition' => 'top',
            'config.appearance.showPossibleLocalizationRecords' => 1,
            'config.appearance.showAllLocalizationLink' => 1,
            'config.appearance.showNewRecordLink' => 1,
            'config.appearance.collapseAll' => '',
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.new' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 1,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => 'tt_content',
            'config.overrideChildTca.columns.colPos.config.default' => 999,
            'config.foreign_sortby' => 'sorting',
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::TAB => [
        'tca_out' => [
            'config.type' => 'tab',
        ],
    ],
    FieldType::PALETTE => [
        'tca_out' => [
            'config.type' => 'palette',
        ],
    ],
    FieldType::LINEBREAK => [
        'tca_out' => [
            'config.type' => 'linebreak',
        ],
    ],
    FieldType::EMAIL => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.nullable' => 0,
        ],
        'tca_out' => [
            'config.type' => 'email',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
];
