<?php

use MASK\Mask\Enumeration\FieldType;

return [
    FieldType::STRING->value => 'input',
    FieldType::INTEGER->value => 'input',
    FieldType::FLOAT->value => 'input',
    FieldType::LINK->value => 'input',

    FieldType::TEXT->value => 'text',
    FieldType::RICHTEXT->value => 'text',

    FieldType::DATE->value => 'date',
    FieldType::DATETIME->value => 'date',
    FieldType::TIMESTAMP->value => 'date',

    FieldType::CHECK->value => 'choice',
    FieldType::RADIO->value => 'choice',
    FieldType::SELECT->value => 'choice',
    FieldType::GROUP->value => 'choice',

    FieldType::FILE->value => 'repeating',
    FieldType::INLINE->value => 'repeating',
    FieldType::CONTENT->value => 'repeating',

    FieldType::TAB->value => 'structure',
    FieldType::PALETTE->value => 'structure',
    FieldType::LINEBREAK->value => 'structure',
];
