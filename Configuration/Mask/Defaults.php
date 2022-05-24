<?php

use MASK\Mask\Enumeration\FieldType;

return [
    FieldType::STRING => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.eval.null' => 0,
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
            'config.eval.null' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.eval.double2' => 1,
        ],
        'sql' => 'float DEFAULT \'0\' NOT NULL',
    ],
    FieldType::INTEGER => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.eval.null' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.eval.int' => 1,
        ],
        'sql' => 'int(11) DEFAULT \'0\' NOT NULL',
    ],
    FieldType::LINK => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.eval.null' => 0,
            'config.fieldControl.linkPopup.options.blindLinkOptions' => [],
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.renderType' => 'inputLink',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::COLORPICKER => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.valuePicker.mode' => '',
            'config.valuePicker.items' => [],
            'config.eval.null' => 0,
            'config.size' => 10,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.renderType' => 'colorpicker',
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
            'config.eval.null' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.dbType' => 'date',
            'config.renderType' => 'inputDateTime',
            'config.eval.date' => 1,
        ],
        'sql' => 'date',
    ],
    FieldType::DATETIME => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.eval.null' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.dbType' => 'datetime',
            'config.renderType' => 'inputDateTime',
            'config.eval.datetime' => 1,
        ],
        'sql' => 'datetime',
    ],
    FieldType::TIMESTAMP => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.eval' => 'date',
            'config.eval.null' => 0,
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.renderType' => 'inputDateTime',
            'config.eval.int' => 1,
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
            'config.eval.null' => 0,
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
            'config.internal_type' => 'db',
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
    FieldType::FILE => [
        'tca_in' => [
            'l10n_mode' => '',
            'imageoverlayPalette' => 1,
            'config.appearance.elementBrowserEnabled' => 1,
            'config.appearance.fileUploadAllowed' => 1,
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 0,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => 'sys_file_reference',
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
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.info' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
            'config.appearance.enabledControls.sort' => 0,
            'config.appearance.enabledControls.hide' => 1,
            'config.appearance.enabledControls.delete' => 1,
            'config.appearance.enabledControls.localize' => 1,
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => 'sys_file_reference',
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::INLINE => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.appearance.collapseAll' => 1,
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
            'config.appearance.collapseAll' => 1,
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
];
