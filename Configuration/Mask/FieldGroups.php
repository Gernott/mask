<?php

use MASK\Mask\Enumeration\FieldType;

// Attention: the sorting is not defined here, but in the order of the
// FieldType enumeration.

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
    FieldType::CATEGORY => 'choice',
    FieldType::GROUP => 'choice',

    FieldType::COLORPICKER => 'special',
    FieldType::SLUG => 'special',

    FieldType::FILE => 'repeating',
    FieldType::MEDIA => 'repeating',
    FieldType::INLINE => 'repeating',
    FieldType::CONTENT => 'repeating',

    FieldType::TAB => 'structure',
    FieldType::PALETTE => 'structure',
    FieldType::LINEBREAK => 'structure',
];
