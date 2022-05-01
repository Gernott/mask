<?php

use MASK\Mask\Enumeration\FieldType;

return [
    FieldType::STRING => [
        'tca_in' => [
            'l10n_mode' => '',
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
        ],
        'tca_out' => [
            'config.type' => 'input',
            'config.renderType' => 'inputLink',
            'config.softref' => 'typolink',
        ],
        'sql' => 'varchar(1024) DEFAULT \'\' NOT NULL',
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
        ],
        'tca_out' => [
            'config.type' => 'select',
        ],
        'sql' => 'varchar(255) DEFAULT \'\' NOT NULL',
    ],
    FieldType::RADIO => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.items' => '',
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
            'config.appearance.fileUploadAllowed' => 1,
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
            'config.appearance.showRemovedLocalizationRecords' => 1,
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => '--inlinetable--',
            'config.foreign_field' => 'parentid',
            'config.foreign_table_field' => 'parenttable',
            'config.foreign_sortby' => 'sorting',
            'config.appearance.enabledControls.dragdrop' => 1,
        ],
        'sql' => 'int(11) unsigned DEFAULT \'0\' NOT NULL',
    ],
    FieldType::CONTENT => [
        'tca_in' => [
            'l10n_mode' => '',
            'config.appearance.levelLinksPosition' => 'top',
        ],
        'tca_out' => [
            'config.type' => 'inline',
            'config.foreign_table' => 'tt_content',
            'config.overrideChildTca.columns.colPos.config.default' => 999,
            'config.foreign_sortby' => 'sorting',
            'config.appearance.collapseAll' => 1,
            'config.appearance.levelLinksPosition' => 'top',
            'config.appearance.showPossibleLocalizationRecords' => 1,
            'config.appearance.showAllLocalizationLink' => 1,
            'config.appearance.showRemovedLocalizationRecords' => 1,
            'config.appearance.useSortable' => 1,
            'config.appearance.enabledControls.dragdrop' => 1,
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
