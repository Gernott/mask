<?php

use MASK\Mask\Enumeration\FieldType;

return [
        FieldType::STRING => 'input',
        FieldType::INTEGER => 'input',
        FieldType::FLOAT => 'input',
        FieldType::LINK => 'input',

        FieldType::TEXT => 'text',
        FieldType::RICHTEXT => 'text',

        FieldType::DATE => 'date',
        FieldType::DATETIME => 'date',
        FieldType::TIMESTAMP => 'date',

        FieldType::CHECK => 'choice',
        FieldType::RADIO => 'choice',
        FieldType::SELECT => 'choice',
        FieldType::GROUP => 'choice',

        FieldType::FILE => 'repeating',
        FieldType::INLINE => 'repeating',
        FieldType::CONTENT => 'repeating',

        FieldType::TAB => 'structure',
        FieldType::PALETTE => 'structure',
        FieldType::LINEBREAK => 'structure',
];
