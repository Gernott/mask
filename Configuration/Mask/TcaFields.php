<?php

use MASK\Mask\Enumeration\FieldType;

return [
    'config.default' => [
        'collision' => true,
        'other' => [
            'type' => 'variable',
            'rows' => 5,
            'label' => 'tx_mask.field.string.default',
            'description' => 'tx_mask.field.string.default.description',
            'code' => 'default',
            'documentation' => [
                10 => 'ColumnsConfig/Type/inputDefault.html#default',
                11 => 'ColumnsConfig/CommonProperties/Default.html#tca-property-default',
            ],
        ],
        FieldType::CHECK => [
            'type' => 'number',
            'min' => 0,
            'max' => 31,
            'code' => 'default',
            'label' => 'tx_mask.field.string.default',
            'description' => 'tx_mask.field.check.default.description',
            'documentation' => [
                10 => 'ColumnsConfig/Type/checkDefault.html#default',
                11 => 'ColumnsConfig/Type/Check/Properties/Default.html#columns-check-properties-default',
            ],
        ],
    ],
    'config.placeholder' => [
        'type' => 'variable',
        'types' => [
            FieldType::TEXT => 'textarea',
            FieldType::RICHTEXT => 'textarea',
        ],
        'rows' => 5,
        'label' => 'tx_mask.field.string.placeholder',
        'description' => 'tx_mask.field.string.placeholder.description',
        'code' => 'placeholder',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#placeholder',
            11 => 'ColumnsConfig/CommonProperties/Placeholder.html#tca-property-placeholder',
        ],
    ],
    'config.size' => [
        'collision' => true,
        'other' => [
            'type' => 'number',
            'label' => 'tx_mask.field.string.size',
            'description' => 'tx_mask.field.string.size.description',
            'code' => 'size',
            'documentation' => [
                10 => 'ColumnsConfig/Type/inputDefault.html#size',
                11 => 'ColumnsConfig/Type/Input/Properties/Size.html#columns-input-properties-size',
            ],
            'min' => 10,
            'max' => 50,
            'step' => 5,
        ],
        FieldType::CATEGORY => [
            'type' => 'number',
            'min' => 1,
            'label' => 'tx_mask.field.category.size',
            'description' => 'tx_mask.field.category.size.description',
            'code' => 'size',
            'documentation' => [
                11 => 'ColumnsConfig/CommonProperties/Size.html#tca-property-size',
            ],
        ],
        FieldType::SELECT => [
            'type' => 'number',
            'min' => 1,
            'label' => 'tx_mask.field.select.size',
            'description' => 'tx_mask.field.select.size.description',
            'code' => 'size',
            'documentation' => [
                10 => 'ColumnsConfig/Type/selectSingle.html#size',
                11 => 'ColumnsConfig/CommonProperties/Size.html#tca-property-size',
            ],
        ],
        FieldType::GROUP => [
            'type' => 'number',
            'min' => 1,
            'label' => 'tx_mask.field.select.size',
            'description' => 'tx_mask.field.select.size.description',
            'code' => 'size',
            'documentation' => [
                10 => 'ColumnsConfig/Type/selectSingle.html#size',
                11 => 'ColumnsConfig/CommonProperties/Size.html#tca-property-size',
            ],
        ],
    ],
    'config.max' => [
        'type' => 'number',
        'label' => 'tx_mask.field.float.max',
        'description' => 'tx_mask.field.float.max.description',
        'code' => 'max',
        'min' => 0,
        'max' => 512,
        'step' => 10,
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#max',
            11 => 'ColumnsConfig/Type/Input/Properties/Max.html#columns-input-properties-max',
        ],
    ],
    'config.is_in' => [
        'type' => 'text',
        'label' => 'tx_mask.field.string.is_in',
        'description' => 'tx_mask.field.string.is_in.description',
        'code' => 'is_in',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#is-in',
            11 => 'ColumnsConfig/Type/Input/Properties/IsIn.html#columns-input-properties-is-in',
        ],
    ],
    // This is for timestamp only to define the date format
    'config.eval' => [
        'type' => 'radio',
        'label' => 'tx_mask.field.timestamp_eval',
        'description' => 'tx_mask.field.timestamp.eval',
        'code' => 'eval',
        'items' => [
            'date' => 'tx_mask.field.date_selection',
            'datetime' => 'tx_mask.field.datetime_selection',
            'time' => 'tx_mask.field.time_selection',
            'timesec' => 'tx_mask.field.timesec_selection',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDateTime.html#eval',
            11 => 'ColumnsConfig/Type/Input/DateTime/Properties/Eval.html',
        ],
    ],
    // This is for slug only to define slug config
    'config.eval.slug' => [
        'type' => 'radio',
        'label' => 'tx_mask.field.slug_eval',
        'description' => 'tx_mask.field.slug_eval.description',
        'code' => 'eval',
        'items' => [
            'unique' => 'tx_mask.field.slug_unique',
            'uniqueInSite' => 'tx_mask.field.slug_unique_in_site',
            'uniqueInPid' => 'tx_mask.field.slug_unique_in_pid',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#eval',
            11 => 'ColumnsConfig/Type/Slug/Properties/Eval.html#columns-slug-properties-eval',
        ],
    ],
    'config.generatorOptions.fields' => [
        'type' => 'text',
        'label' => 'tx_mask.field.slug.generatorOptions.fields',
        'description' => 'tx_mask.field.slug.generatorOptions.fields.description',
        'code' => 'generatorOptions.fields',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#generatoroptions',
            11 => 'ColumnsConfig/Type/Slug/Properties/GeneratorOptions.html#confval-generatorOptions:fields',
        ],
    ],
    'config.generatorOptions.fieldSeparator' => [
        'type' => 'text',
        'label' => 'tx_mask.field.slug.generatorOptions.fieldSeparator',
        'description' => 'tx_mask.field.slug.generatorOptions.fieldSeparator.description',
        'code' => 'generatorOptions.fieldSeparator',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#generatoroptions',
            11 => 'ColumnsConfig/Type/Slug/Properties/GeneratorOptions.html#confval-generatorOptions:fieldSeparator',
        ],
    ],
    'config.generatorOptions.replacements' => [
        'type' => 'keyValue',
        'label' => 'tx_mask.field.slug.generatorOptions.replacements',
        'description' => 'tx_mask.field.slug.generatorOptions.replacements.description',
        'code' => 'generatorOptions.replacements',
        'keyValueLabels' => [
            'key' => 'tx_mask.field.slug.generatorOptions.key',
            'value' => 'tx_mask.field.slug.generatorOptions.value',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#generatoroptions',
            11 => 'ColumnsConfig/Type/Slug/Properties/GeneratorOptions.html#confval-generatorOptions:replacements',
        ],
    ],
    'config.prependSlash' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.slug.prependSlash',
        'description' => 'tx_mask.field.slug.prependSlash.description',
        'code' => 'prependSlash',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#prependslash',
            11 => 'ColumnsConfig/Type/Slug/Properties/PrependSlash.html#columns-slug-properties-prependslash',
        ],
    ],
    'config.fallbackCharacter' => [
        'type' => 'text',
        'label' => 'tx_mask.field.slug.fallbackCharacter',
        'description' => 'tx_mask.field.slug.fallbackCharacter.description',
        'code' => 'fallbackCharacter',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Slug.html#fallbackcharacter',
            11 => 'ColumnsConfig/Type/Slug/Properties/FallbackCharacter.html#columns-slug-properties-fallbackcharacter',
        ],
    ],
    'config.eval.required' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.required',
        'description' => 'tx_mask.field.required.description',
        'code' => 'required',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=required#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.trim' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.trim',
        'description' => 'tx_mask.field.trim.description',
        'code' => 'trim',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=trim#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.alpha' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.alpha.label',
        'description' => 'tx_mask.field.alpha',
        'code' => 'alpha',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=alpha#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.num' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.num.label',
        'description' => 'tx_mask.field.num',
        'code' => 'num',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.alphanum' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.alphanum.label',
        'description' => 'tx_mask.field.alphanum',
        'code' => 'alphanum',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=alphanum#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.alphanum_x' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.alphanum_x.label',
        'description' => 'tx_mask.field.alphanum_x',
        'code' => 'alphanum_x',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=alphanum_x#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.domainname' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.domainname.label',
        'description' => 'tx_mask.field.domainname',
        'code' => 'domainname',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=domainname#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.email' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.email.label',
        'description' => 'tx_mask.field.email',
        'code' => 'email',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=email#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.lower' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.lower.label',
        'description' => 'tx_mask.field.lower',
        'code' => 'lower',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=lower#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.upper' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.upper.label',
        'description' => 'tx_mask.field.upper',
        'code' => 'upper',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=upper#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.unique' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.unique.label',
        'description' => 'tx_mask.field.unique',
        'code' => 'unique',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=unique#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.uniqueInPid' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.uniqueInPid.label',
        'description' => 'tx_mask.field.uniqueInPid',
        'code' => 'uniqueInPid',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=uniqueInPid#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.nospace' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.nospace.label',
        'description' => 'tx_mask.field.nospace',
        'code' => 'nospace',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=nospace#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.md5' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.md5.label',
        'description' => 'tx_mask.field.md5',
        'code' => 'md5',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=md5#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.null' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.null.label',
        'description' => 'tx_mask.field.null',
        'code' => 'null',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=null#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.eval.password' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.password.label',
        'description' => 'tx_mask.field.password',
        'code' => 'password',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=password#eval',
            11 => 'ColumnsConfig/Type/Input/Properties/Eval.html#columns-input-properties-eval',
        ],
    ],
    'config.mode' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.string.mode.label',
        'description' => 'tx_mask.field.string.mode',
        'code' => 'mode',
        'dependsOn' => 'config.eval.null',
        'valueOn' => 'useOrOverridePlaceholder',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#mode',
            11 => 'ColumnsConfig/CommonProperties/Mode.html#tca-property-mode',
        ],
    ],
    'config.autocomplete' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.string.autocomplete.label',
        'description' => 'tx_mask.field.string.autocomplete',
        'code' => 'autocomplete',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#autocomplete',
            11 => 'ColumnsConfig/Type/Input/Properties/Autocomplete.html#columns-input-properties-autocomplete',
        ],
    ],
    'config.behaviour.allowLanguageSynchronization' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.allowLanguageSynchronization',
        'description' => 'tx_mask.allowLanguageSynchronization.description',
        'code' => 'allowLanguageSynchronization',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#allowlanguagesynchronization',
            11 => 'ColumnsConfig/CommonProperties/BehaviourAllowLanguageSynchronization.html#tca-property-behaviour-allowlanguagesynchronization',
        ],
    ],
    'config.range.lower' => [
        'type' => 'variable',
        'label' => 'tx_mask.field.float.range.lower',
        'code' => 'range.lower',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#range',
            11 => 'ColumnsConfig/Type/Input/Properties/Range.html#columns-input-properties-range',
        ],
    ],
    'config.range.upper' => [
        'type' => 'variable',
        'label' => 'tx_mask.field.float.range.upper',
        'code' => 'range.upper',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#range',
            11 => 'ColumnsConfig/Type/Input/Properties/Range.html#columns-input-properties-range',
        ],
    ],
    'config.slider.step' => [
        'type' => 'number',
        'label' => 'tx_mask.config.slider.step',
        'description' => 'tx_mask.config.slider.step.description',
        'code' => 'slider.step',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=step#slider',
            11 => 'ColumnsConfig/Type/Input/Properties/Slider.html#columns-input-properties-slider',
        ],
    ],
    'config.slider.width' => [
        'type' => 'number',
        'label' => 'tx_mask.config.slider.width',
        'description' => 'tx_mask.config.slider.width.description',
        'min' => 200,
        'step' => 50,
        'code' => 'slider.width',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html?highlight=width#slider',
            11 => 'ColumnsConfig/Type/Input/Properties/Slider.html#columns-input-properties-slider',
        ],
    ],
    'config.valuePicker.mode' => [
        'type' => 'select',
        'label' => 'tx_mask.config.valuePicker.mode.label',
        'description' => 'tx_mask.config.valuePicker.mode.description',
        'code' => 'valuePicker.mode',
        'items' => [
            '' => 'tx_mask.config.valuePicker.mode.blank',
            'append' => 'tx_mask.config.valuePicker.mode.append',
            'prepend' => 'tx_mask.config.valuePicker.mode.prepend',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#valuepicker',
            11 => 'ColumnsConfig/Type/Input/Properties/ValuePicker.html',
        ],
    ],
    'config.valuePicker.items' => [
        'type' => 'itemList',
        'label' => 'tx_mask.config.valuePicker.items.label',
        'description' => 'tx_mask.config.valuePicker.items.description',
        'code' => 'valuePicker.items',
        'properties' => [
            0 => [
                'label' => 'tx_mask.config.valuePicker.items.label.label',
                'type' => 'text',
            ],
            1 => [
                'label' => 'tx_mask.config.valuePicker.items.value.label',
                'type' => 'text',
            ],
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputDefault.html#valuepicker',
            11 => 'ColumnsConfig/Type/Input/Properties/ValuePicker.html',
        ],
    ],
    'config.fieldControl.linkPopup.options.allowedExtensions' => [
        'type' => 'text',
        'label' => 'tx_mask.field.link.wizard.allowed_extensions',
        'description' => 'tx_mask.field.link.wizard.allowed_extensions.description',
        'code' => 'allowedExtensions',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputLink.html?highlight=allowedExtensions#linkpopup',
            11 => 'ColumnsConfig/Type/Input/Properties/LinkPopup.html#tca-property-fieldcontrol-linkpopup',
        ],
    ],
    'config.fieldControl.linkPopup.options.blindLinkOptions' => [
        'type' => 'linkHandler',
        'label' => 'tx_mask.blindLinkOptions',
        'description' => 'tx_mask.blindLinkOptions.description',
        'code' => 'blindLinkOptions',
        'documentation' => [
            10 => 'ColumnsConfig/Type/inputLink.html?highlight=blindLinkOptions#linkpopup',
            11 => 'ColumnsConfig/Type/Input/Properties/LinkPopup.html#tca-property-fieldcontrol-linkpopup',
        ],
    ],
    'config.cols' => [
        'collision' => true,
        FieldType::CHECK => [
            'type' => 'text',
            'label' => 'tx_mask.content.check.columns',
            'description' => 'tx_mask.content.check.columns.description',
            'code' => 'cols',
            'documentation' => [
                10 => 'ColumnsConfig/Type/checkDefault.html#cols',
                11 => 'ColumnsConfig/Type/Check/Properties/Cols.html',
            ],
        ],
        'other' => [
            'type' => 'number',
            'label' => 'tx_mask.field.text.cols',
            'description' => 'tx_mask.field.text.cols.description',
            'code' => 'cols',
            'min' => 10,
            'max' => 50,
            'step' => 5,
            'documentation' => [
                10 => 'ColumnsConfig/Type/textDefault.html#cols',
                11 => 'ColumnsConfig/Type/Text/Properties/Cols.html#columns-text-properties-cols',
            ],
        ],
    ],
    'config.rows' => [
        'type' => 'number',
        'label' => 'tx_mask.field.text.rows',
        'description' => 'tx_mask.field.text.rows.description',
        'code' => 'rows',
        'min' => 2,
        'max' => 20,
        'step' => 2,
        'documentation' => [
            10 => 'ColumnsConfig/Type/textDefault.html#rows',
            11 => 'ColumnsConfig/Type/Text/Properties/Rows.html',
        ],
    ],
    'config.format' => [
        'type' => 'radio',
        'label' => 'tx_mask.field.text.format',
        'code' => 'format',
        'items' => [
            '' => 'tx_mask.field.text.format.none',
            'html' => 'tx_mask.field.text.format.html',
            'typoscript' => 'tx_mask.field.text.format.typoscript',
            'javascript' => 'tx_mask.field.text.format.javascript',
            'css' => 'tx_mask.field.text.format.css',
            'xml' => 'tx_mask.field.text.format.xml',
            'php' => 'tx_mask.field.text.format.php',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/textT3editor.html#format',
            11 => 'ColumnsConfig/Type/Text/Properties/Format.html',
        ],
    ],
    'config.wrap' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.text.wrap',
        'code' => 'wrap',
        'valueOff' => 'off',
        'valueOn' => 'virtual',
        'documentation' => [
            10 => 'ColumnsConfig/Type/textDefault.html#wrap',
            11 => 'ColumnsConfig/Type/Text/Properties/Wrap.html',
        ],
    ],
    'config.fixedFont' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.config.fixedFont',
        'description' => 'tx_mask.config.fixedFont.description',
        'code' => 'fixedFont',
        'documentation' => [
            10 => 'ColumnsConfig/Type/textDefault.html#fixedfont',
            11 => 'ColumnsConfig/Type/Text/Properties/FixedFont.html',
        ],
    ],
    'config.enableTabulator' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.config.enableTabulator',
        'description' => 'tx_mask.config.enableTabulator.description',
        'code' => 'enableTabulator',
        'documentation' => [
            10 => 'ColumnsConfig/Type/textDefault.html#enabletabulator',
            11 => 'ColumnsConfig/Type/Text/Properties/EnableTabulator.html',
        ],
    ],
    'config.richtextConfiguration' => [
        'type' => 'radio',
        'label' => 'tx_mask.config.richtextConfiguration',
        'code' => 'richtextConfiguration',
        'documentation' => [
            10 => 'ColumnsConfig/Type/textDefault.html#richtextconfiguration',
            11 => 'ColumnsConfig/Type/Text/Properties/RichtextConfiguration.html',
        ],
    ],
    'config.items' => [
        'collision' => true,
        FieldType::CHECK => [
            'type' => 'itemList',
            'label' => 'tx_mask.content.check.items',
            'description' => 'tx_mask.content.check.items.description',
            'placeholder' => 'tx_mask.content.check.items.placeholder',
            'code' => 'items',
            'maxItems' => 31,
            'properties' => [
                0 => [
                    'label' => 'tx_mask.field.select.items.label',
                    'type' => 'text',
                ],
                'invertStateDisplay' => [
                    'label' => 'tx_mask.field.check.items.invertStateDisplay',
                    'type' => 'checkbox',
                ],
                'iconIdentifierChecked' => [
                    'label' => 'tx_mask.field.check.items.iconIdentifierChecked',
                    'type' => 'text',
                    'renderType' => '',
                ],
                'iconIdentifierUnchecked' => [
                    'label' => 'tx_mask.field.check.items.iconIdentifierUnchecked',
                    'type' => 'text',
                    'renderType' => '',
                ],
                'labelChecked' => [
                    'label' => 'tx_mask.field.check.items.labelChecked',
                    'type' => 'text',
                    'renderType' => 'checkboxLabeledToggle',
                ],
                'labelUnchecked' => [
                    'label' => 'tx_mask.field.check.items.labelUnchecked',
                    'type' => 'text',
                    'renderType' => 'checkboxLabeledToggle',
                ],
            ],
            'documentation' => [
                10 => 'ColumnsConfig/Type/checkDefault.html#items',
                11 => 'ColumnsConfig/Type/Check/Properties/Items.html',
            ],
        ],
        FieldType::RADIO => [
            'type' => 'itemList',
            'code' => 'items',
            'label' => 'tx_mask.content.check.items',
            'description' => 'tx_mask.field.radio.items',
            'properties' => [
                0 => [
                    'label' => 'tx_mask.field.select.items.label',
                    'type' => 'text',
                ],
                1 => [
                    'label' => 'tx_mask.field.radio.items.value',
                    'type' => 'text',
                ],
            ],
            'placeholder' => 'tx_mask.content.radio.items.placeholder',
            'documentation' => [
                10 => 'ColumnsConfig/Type/Radio.html#items',
                11 => 'ColumnsConfig/Type/Radio/Properties/Items.html',
            ],
        ],
        FieldType::SELECT => [
            'type' => 'itemList',
            'code' => 'items',
            'properties' => [
                0 => [
                    'label' => 'tx_mask.field.select.items.label',
                    'type' => 'text',
                ],
                1 => [
                    'label' => 'tx_mask.field.select.items.value',
                    'type' => 'text',
                ],
                2 => [
                    'label' => 'tx_mask.field.select.items.icon',
                    'type' => 'text',
                ],
                3 => [
                    'label' => 'tx_mask.field.select.items.group',
                    'type' => 'group',
                ],
                4 => [
                    'type' => 'text',
                    'label' => 'tx_mask.field.select.items.description',
                    'renderType' => 'selectCheckBox',
                ],
            ],
            'label' => 'tx_mask.content.check.items',
            'description' => 'tx_mask.field.select.items',
            'placeholder' => 'tx_mask.content.select.items.placeholder',
            'documentation' => [
                10 => 'ColumnsConfig/Type/selectSingle.html#items',
                11 => 'ColumnsConfig/Type/Select/Properties/Items.html',
            ],
        ],
    ],
    'config.itemGroups' => [
        'type' => 'keyValue',
        'code' => 'itemGroups',
        'label' => 'tx_mask.content.select.itemGroups.label',
        'description' => 'tx_mask.content.select.itemGroups.description',
        'keyValueLabels' => [
            'key' => 'tx_mask.content.select.itemGroups.key',
            'value' => 'tx_mask.content.select.itemGroups.value',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectCheckBox.html#itemgroups',
            11 => 'ColumnsConfig/Type/Select/Properties/ItemGroups.html',
        ],
    ],
    'config.sortItems' => [
        'type' => 'keyValue',
        'code' => 'sortItems',
        'label' => 'tx_mask.content.select.sortItems.label',
        'description' => 'tx_mask.content.select.sortItems.description',
        'keyValueLabels' => [
            'key' => 'tx_mask.content.select.sortItems.key',
            'value' => 'tx_mask.content.select.sortItems.value',
        ],
        'keyValueSelectItems' => [
            'key' => [
                [
                    'value' => 'label',
                    'label' => 'tx_mask.content.select.sortItems.labels.label',
                ],
                [
                    'value' => 'value',
                    'label' => 'tx_mask.content.select.sortItems.labels.value',
                ],
            ],
            'value' => [
                [
                    'value' => 'asc',
                    'label' => 'tx_mask.content.select.sortItems.labels.asc',
                ],
                [
                    'value' => 'desc',
                    'label' => 'tx_mask.content.select.sortItems.labels.desc',
                ],
            ],
        ],
        'maxItems' => 1,
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectCheckBox.html#sortitems',
            11 => 'ColumnsConfig/Type/Select/Properties/SortItems.html',
        ],
    ],
    'config.renderType' => [
        'collision' => true,
        FieldType::SELECT => [
            'type' => 'select',
            'label' => 'tx_mask.field.check.renderType',
            'description' => 'tx_mask.field.check.renderType.description',
            'code' => 'renderType',
            'items' => [
                'selectSingle' => 'tx_mask.field.select.renderType.selectSingle',
                'selectSingleBox' => 'tx_mask.field.select.renderType.selectSingleBox',
                'selectCheckBox' => 'tx_mask.field.select.renderType.selectCheckBox',
                'selectMultipleSideBySide' => 'tx_mask.field.select.renderType.selectMultipleSideBySide',
            ],
            'documentation' => [
                10 => 'ColumnsConfig/Type/Select.html',
                11 => 'ColumnsConfig/Type/Select/Index.html',
            ],
        ],
        FieldType::CHECK => [
            'type' => 'select',
            'label' => 'tx_mask.field.check.renderType',
            'description' => 'tx_mask.field.check.renderType.description',
            'code' => 'renderType',
            'items' => [
                '' => 'tx_mask.field.check',
                'checkboxToggle' => 'tx_mask.field.check.renderType.checkboxToggle',
                'checkboxLabeledToggle' => 'tx_mask.field.check.renderType.checkboxLabeledToggle',
            ],
            'documentation' => [
                10 => 'ColumnsConfig/Type/Check.html',
                11 => 'ColumnsConfig/Type/Check/Index.html',
            ],
        ],
    ],
    'config.foreign_table' => [
        'type' => 'foreign_table',
        'label' => 'tx_mask.field.select.foreign_table',
        'description' => 'tx_mask.field.select.foreign_table.description',
        'code' => 'foreign_table',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#foreign-table',
            11 => 'ColumnsConfig/Type/Select/Properties/ForeignTable.html',
        ],
    ],
    'config.foreign_table_where' => [
        'type' => 'text',
        'label' => 'tx_mask.field.select.foreign_table_where',
        'description' => 'tx_mask.field.select.foreign_table_where.description',
        'code' => 'foreign_table_where',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#foreign-table-where',
            11 => 'ColumnsConfig/Type/Select/Properties/ForeignTableWhere.html',
        ],
    ],
    'config.fileFolder' => [
        'type' => 'text',
        'label' => 'tx_mask.field.select.file_folder',
        'description' => 'tx_mask.field.select.file_folder.description',
        'code' => 'fileFolder',
        'version' => 10,
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#filefolder',
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder',
        ],
    ],
    'config.fileFolder_extList' => [
        'type' => 'text',
        'label' => 'tx_mask.field.select.file_folder_ext_list',
        'description' => 'tx_mask.field.select.file_folder_ext_list.description',
        'code' => 'fileFolder_extList',
        'version' => 10,
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#filefolder-extlist',
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder-extlist',
        ],
    ],
    'config.fileFolder_recursions' => [
        'type' => 'number',
        'min' => 0,
        'max' => 99,
        'label' => 'tx_mask.field.select.file_folder_recursions',
        'description' => 'tx_mask.field.select.file_folder_recursions.description',
        'code' => 'fileFolder_recursions',
        'version' => 10,
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#filefolder-recursions',
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder-recursions',
        ],
    ],
    'config.fileFolderConfig.folder' => [
        'type' => 'text',
        'label' => 'tx_mask.field.select.file_folder_ext_list',
        'description' => 'tx_mask.field.select.file_folder_ext_list.description',
        'code' => 'folder',
        'version' => '>= 11',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder',
        ],
    ],
    'config.fileFolderConfig.allowedExtensions' => [
        'type' => 'text',
        'label' => 'tx_mask.field.select.file_folder_ext_list',
        'description' => 'tx_mask.field.select.file_folder_ext_list.description',
        'code' => 'allowedExtensions',
        'version' => '>= 11',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder-extlist',
        ],
    ],
    'config.fileFolderConfig.depth' => [
        'type' => 'number',
        'min' => 0,
        'max' => 99,
        'label' => 'tx_mask.field.select.file_folder_recursions',
        'description' => 'tx_mask.field.select.file_folder_recursions.description',
        'code' => 'depth',
        'version' => '>= 11',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Select/Properties/FileFolderConfig.html#columns-select-properties-filefolder-recursions',
        ],
    ],
    'config.autoSizeMax' => [
        'type' => 'number',
        'min' => 1,
        'label' => 'tx_mask.field.select.autosizemax',
        'description' => 'tx_mask.field.select.autosizemax.description',
        'code' => 'autoSizeMax',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingleBox.html?#autosizemax',
            11 => 'ColumnsConfig/CommonProperties/AutoSizeMax.html#tca-property-autosizemax',
        ],
    ],
    'config.minitems' => [
        'type' => 'number',
        'min' => 1,
        'label' => 'tx_mask.field.select.minitems',
        'description' => 'tx_mask.field.select.minitems.description',
        'code' => 'minitems',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingleBox.html#minitems',
            11 => 'ColumnsConfig/CommonProperties/Minitems.html#tca-property-minitems',
        ],
    ],
    'config.maxitems' => [
        'type' => 'number',
        'min' => 1,
        'label' => 'tx_mask.field.select.maxitems',
        'description' => 'tx_mask.field.select.maxitems.description',
        'code' => 'maxitems',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingleBox.html#maxitems',
            11 => 'ColumnsConfig/CommonProperties/Maxitems.html#tca-property-maxitems',
        ],
    ],
    'config.internal_type' => [
        'type' => 'radio',
        'label' => 'tx_mask.field.group.internal_type',
        'description' => 'tx_mask.field.group.internal_type.description',
        'code' => 'internal_type',
        'items' => [
            'db' => 'tx_mask.field.group.internalType.db',
            'folder' => 'tx_mask.field.group.internalType.folder',
        ],
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#internal-type',
            11 => 'ColumnsConfig/Type/Group/Properties/InternalType.html#columns-group-properties-internal-type',
        ],
    ],
    'config.allowed' => [
        'type' => 'text',
        'label' => 'tx_mask.field.group.allowed',
        'description' => 'tx_mask.field.group.allowed.description',
        'code' => 'allowed',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#allowed',
            11 => 'ColumnsConfig/Type/Group/Properties/InternalType.html#columns-group-properties-internal-type',
        ],
    ],
    'config.fieldControl.editPopup.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.editPopup',
        'description' => 'tx_mask.group.editPopup.description',
        'code' => 'editPopup',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol-editpopup',
            11 => 'ColumnsConfig/CommonProperties/FieldControl/EditPopup.html#tca-property-fieldcontrol-editpopup',
        ],
    ],
    'config.fieldControl.addRecord.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.addRecord',
        'description' => 'tx_mask.group.addRecord.description',
        'code' => 'addRecord',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol-addrecord',
            11 => 'ColumnsConfig/CommonProperties/FieldControl/AddRecord.html#tca-property-fieldcontrol-addrecord',
        ],
    ],
    'config.fieldControl.listModule.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.listModule',
        'description' => 'tx_mask.group.listModule.description',
        'code' => 'listModule',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol-listmodule',
            11 => 'ColumnsConfig/CommonProperties/FieldControl/ListModule.html#tca-property-fieldcontrol-listmodule',
        ],
    ],
    'config.fieldControl.elementBrowser.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.elementBrowser',
        'description' => 'tx_mask.group.elementBrowser.description',
        'code' => 'elementBrowser',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol-elementbrowser',
            11 => 'ColumnsConfig/Type/Group/Properties/FieldControl.html#columns-group-properties-elementbrowser',
        ],
    ],
    'config.fieldControl.insertClipboard.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.insertClipboard',
        'description' => 'tx_mask.group.insertClipboard.description',
        'code' => 'insertClipboard',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol-insertclipboard',
            11 => 'ColumnsConfig/Type/Group/Properties/FieldControl.html#tca-property-fieldcontrol-insertclipboard',
        ],
    ],
    'config.fieldControl' => [
        'type' => 'plainText',
        'label' => 'tx_mask.fieldControl',
        'description' => 'tx_mask.fieldControl.description',
        'code' => 'fieldControl',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldcontrol',
            11 => 'ColumnsConfig/CommonProperties/FieldControl.html',
        ],
    ],
    'config.fieldWizard.recordsOverview.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.recordsOverview',
        'description' => 'tx_mask.group.recordsOverview.description',
        'code' => 'recordsOverview',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldwizard-recordsoverview',
            11 => 'ColumnsConfig/CommonProperties/FieldWizard/RecordsOverview.html#tca-property-fieldwizard-recordsoverview',
        ],
    ],
    'config.fieldWizard.tableList.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.tableList',
        'description' => 'tx_mask.group.tableList.description',
        'code' => 'tableList',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldwizard-tablelist',
            11 => 'ColumnsConfig/CommonProperties/FieldWizard/TableList.html#tca-property-fieldwizard-tablelist',
        ],
    ],
    'config.fieldWizard.selectIcons.disabled' => [
        'type' => 'checkbox',
        'invert' => true,
        'label' => 'tx_mask.group.selectIcons',
        'description' => 'tx_mask.group.selectIcons.description',
        'code' => 'selectIcons',
        'documentation' => [
            10 => 'ColumnsConfig/Type/selectSingle.html#selecticons',
            11 => 'ColumnsConfig/CommonProperties/FieldWizard/SelectIcons.html#tca-property-fieldwizard-selecticons',
        ],
    ],
    'config.fieldWizard' => [
        'type' => 'plainText',
        'label' => 'tx_mask.fieldWizard',
        'description' => 'tx_mask.fieldWizard.description',
        'code' => 'fieldWizard',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#fieldwizard',
            11 => 'ColumnsConfig/CommonProperties/FieldWizard.html',
        ],
    ],
    'config.multiple' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.group.multiple',
        'description' => 'tx_mask.group.multiple.description',
        'code' => 'multiple',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Group.html#multiple',
            11 => 'ColumnsConfig/CommonProperties/Multiple.html#tca-property-multiple',
        ],
    ],
    'config.appearance.collapseAll' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.collapse_all.label',
        'description' => 'tx_mask.field.inline.collapse_all',
        'code' => 'collapseAll',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=collapseall#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.expandSingle' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.expand_single.label',
        'description' => 'tx_mask.field.inline.expand_single',
        'code' => 'expandSingle',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=expandsingle#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.useSortable' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.use_sortable.label',
        'description' => 'tx_mask.field.inline.use_sortable',
        'code' => 'useSortable',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=usesortable#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls' => [
        'type' => 'plainText',
        'label' => 'tx_mask.field.inline.enabledControls.label',
        'description' => 'tx_mask.field.inline.enabledControls.description',
        'code' => 'enabledControls',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.info' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.info.label',
        'code' => 'info',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.new' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.new.label',
        'code' => 'new',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.dragdrop' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.dragdrop.label',
        'code' => 'dragdrop',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.sort' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.sort.label',
        'code' => 'sort',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.hide' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.hide.label',
        'code' => 'hide',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.delete' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.delete.label',
        'code' => 'delete',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.enabledControls.localize' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.enabledControls.localize.label',
        'code' => 'localize',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=enabledControls#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.elementBrowserEnabled' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.elementBrowserEnabled.label',
        'description' => 'tx_mask.field.inline.elementBrowserEnabled.description',
        'code' => 'elementBrowserEnabled',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=elementBrowserEnabled#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.fileUploadAllowed' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.file_upload_allowed.label',
        'description' => 'tx_mask.field.inline.file_upload_allowed',
        'code' => 'fileUploadAllowed',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=fileuploadallowed#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.fileByUrlAllowed' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.file_by_url_allowed.label',
        'description' => 'tx_mask.field.inline.file_by_url_allowed.description',
        'code' => 'fileByUrlAllowed',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'onlineMedia' => [
        'type' => 'onlineMedia',
        'label' => 'tx_mask.online_media.label',
        'description' => 'tx_mask.online_media.description',
        'code' => 'onlineMedia',
    ],
    'config.appearance.showSynchronizationLink' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.show_synchronization_link',
        'description' => 'tx_mask.field.inline.show_synchronization_link_description',
        'dependsOn' => 'config.appearance.showPossibleLocalizationRecords',
        'code' => 'showSynchronizationLink',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=showsynchronizationlink#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.showPossibleLocalizationRecords' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.show_possible_localization_records',
        'description' => 'tx_mask.field.inline.show_possible_localization_records.description',
        'code' => 'showPossibleLocalizationRecords',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=showpossiblelocalizationrecords#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.showAllLocalizationLink' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.show_all_localization_link',
        'description' => 'tx_mask.field.inline.show_all_localization_link.description',
        'dependsOn' => 'config.appearance.showPossibleLocalizationRecords',
        'code' => 'showAllLocalizationLink',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=showalllocalizationlink#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.newRecordLinkTitle' => [
        'type' => 'text',
        'label' => 'tx_mask.field.inline.new_record_link_title.label',
        'description' => 'tx_mask.field.inline.new_record_link_title',
        'code' => 'newRecordLinkTitle',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=newrecordlinktitle#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.createNewRelationLinkTitle' => [
        'type' => 'text',
        'label' => 'tx_mask.field.inline.createNewRelationLinkTitle.label',
        'description' => 'tx_mask.field.inline.createNewRelationLinkTitle.description',
        'code' => 'createNewRelationLinkTitle',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=createNewRelationLinkTitle#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html',
        ],
    ],
    'config.appearance.levelLinksPosition' => [
        'type' => 'select',
        'items' => [
            'top' => 'tx_mask.field.inline.level_links_position.top',
            'bottom' => 'tx_mask.field.inline.level_links_position.bottom',
            'both' => 'tx_mask.field.inline.level_links_position.both',
            'none' => 'tx_mask.field.inline.level_links_position.none',
        ],
        'label' => 'tx_mask.field.inline.level_links_position.label',
        'description' => 'tx_mask.field.inline.level_links_position',
        'code' => 'levelLinksPosition',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=levellinksposition#appearance',
            11 => 'ColumnsConfig/Type/Inline/Properties/Appearance.html#columns-inline-properties-appearance',
        ],
    ],
    'config.appearance.showNewRecordLink' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.inline.show_new_record_link.label',
        'description' => 'tx_mask.field.inline.show_new_record_link.description',
        'code' => 'showNewRecordLink',
        'version' => '>= 11',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Inline/Properties/ShowNewRecordLink.html',
        ],
    ],
    'config.relationship' => [
        'type' => 'radio',
        'items' => [
            'oneToOne' => 'tx_mask.field.category.relationship.oneToOne',
            'oneToMany' => 'tx_mask.field.category.relationship.oneToMany',
            'manyToMany' => 'tx_mask.field.category.relationship.manyToMany',
        ],
        'label' => 'tx_mask.field.category.relationship.label',
        'description' => 'tx_mask.field.category.relationship.description',
        'code' => 'relationship',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/Relationship.html',
        ],
    ],
    'config.exclusiveKeys' => [
        'type' => 'text',
        'label' => 'tx_mask.field.category.exclusiveKeys.label',
        'description' => 'tx_mask.field.category.exclusiveKeys.description',
        'code' => 'exclusiveKeys',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/ExclusiveKeys.html',
        ],
    ],
    'config.treeConfig.startingPoints' => [
        'type' => 'text',
        'label' => 'tx_mask.field.category.treeConfig.startingPoints.label',
        'description' => 'tx_mask.field.category.treeConfig.startingPoints.description',
        'code' => 'startingPoints',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/TreeConfig.html',
        ],
    ],
    'config.treeConfig.appearance.showHeader' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.category.treeConfig.appearance.showHeader.label',
        'description' => 'tx_mask.field.category.treeConfig.appearance.showHeader.description',
        'code' => 'showHeader',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/TreeConfig.html',
        ],
    ],
    'config.treeConfig.appearance.expandAll' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.category.treeConfig.appearance.expandAll.label',
        'description' => 'tx_mask.field.category.treeConfig.appearance.expandAll.description',
        'code' => 'expandAll',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/TreeConfig.html',
        ],
    ],
    'config.treeConfig.appearance.nonSelectableLevels' => [
        'type' => 'text',
        'label' => 'tx_mask.field.category.treeConfig.appearance.nonSelectableLevels.label',
        'description' => 'tx_mask.field.category.treeConfig.appearance.nonSelectableLevels.description',
        'code' => 'nonSelectableLevels',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Category/Properties/TreeConfig.html',
        ],
    ],
    'config.appearance.expandAll' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.select.appearance.expandAll.label',
        'description' => 'tx_mask.field.select.appearance.expandAll.description',
        'code' => 'expandAll',
        'version' => '>= 11',
        'documentation' => [
            11 => 'ColumnsConfig/Type/Select/Properties/CheckBoxAppearance.html',
        ],
    ],
    'ctrl.label' => [
        'type' => 'text',
        'label' => 'tx_mask.all.label',
        'description' => 'tx_mask.field.inline.inline_label',
        'code' => 'label',
        'documentation' => [
            10 => 'Ctrl/Index.html#label',
            11 => 'Ctrl/Properties/Label.html#ctrl-reference-label',
        ],
    ],
    'ctrl.iconfile' => [
        'type' => 'text',
        'label' => 'tx_mask.field.inline.inline_icon.label',
        'description' => 'tx_mask.field.inline.inline_icon',
        'code' => 'iconfile',
        'documentation' => [
            10 => 'Ctrl/Index.html#iconfile',
            11 => 'Ctrl/Properties/Iconfile.html#ctrl-reference-iconfile',
        ],
    ],
    'cTypes' => [
        'type' => 'cTypes',
        'label' => 'tx_mask.allowed_content',
        'description' => 'tx_mask.allowed_content.description',
        'code' => 'cTypes',
    ],
    'allowedFileExtensions' => [
        'type' => 'text',
        'label' => 'tx_mask.field.inline.allowed_file_extensions',
        'description' => 'tx_mask.field.inline.elementBrowserAllowed.description',
        'code' => 'allowedFileExtensions',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html?highlight=elementbrowserallowed#appearance',
            11 => 'ColumnsConfig/Type/Group/Properties/Appearance.html#columns-group-properties-appearance',
        ],
    ],
    'imageoverlayPalette' => [
        'type' => 'checkbox',
        'label' => 'tx_mask.field.imageoverlayPalette',
        'description' => 'tx_mask.field.imageoverlayPalette.description',
        'code' => 'imageoverlayPalette',
        'documentation' => [
            10 => 'ColumnsConfig/Type/Inline.html#file-abstraction-layer',
            11 => 'ColumnsConfig/Type/Inline/Examples.html#columns-inline-examples-fal',
        ],
    ],
    'l10n_mode' => [
        'type' => 'radio',
        'label' => 'tx_mask.field.inline.localization_mode',
        'code' => 'l10n_mode',
        'documentation' => [
            10 => 'Columns/Index.html#l10n-mode',
            11 => 'Columns/Properties/L10nMode.html#columns-properties-l10n-mode',
        ],
        'items' => [
            '' => 'tx_mask.field.inline.l10n_mode.default',
            'exclude' => 'tx_mask.field.inline.l10n_mode.exclude',
            'prefixLangTitle' => 'tx_mask.field.inline.l10n_mode.prefixLangTitle',
        ],
    ],
];
