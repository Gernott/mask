<?php

return [
    'dependencies' => [
        'core',
        'backend',
    ],
    'tags' => [
        'backend.module',
    ],
    'imports' => [
        '@mask/mask' => 'EXT:mask/Resources/Public/JavaScript/mask.js',
    ],
];
