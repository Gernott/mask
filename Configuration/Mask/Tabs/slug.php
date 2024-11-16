<?php

use MASK\Mask\Enumeration\Tab;

return [
    Tab::GENERAL->value => [
        [
            'config.eval.slug' => 12,
        ],
    ],
    Tab::GENERATOR->value => [
        [
            'config.generatorOptions.fields' => 12,
            'config.generatorOptions.replacements' => 12,
        ],
        [
            'config.generatorOptions.fieldSeparator' => 6,
            'config.fallbackCharacter' => 6,
        ],
        [
            'config.prependSlash' => 6,
        ],
    ],
];
