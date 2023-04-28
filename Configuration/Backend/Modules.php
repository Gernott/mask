<?php

return [
    'mask_module' => [
        'parent' => 'tools',
        'position' => ['after' => 'extensionmanager'],
        'access' => 'user',
        'workspaces' => 'online',
        'path' => '/module/mask',
        'icon' => 'EXT:mask/Resources/Public/Icons/module-mask_wizard.svg', // @todo iconIdentifier
        'labels' => 'LLL:EXT:mask/Resources/Private/Language/locallang_mask.xlf',
        'routes' => [
            '_default' => [
                'target' => \MASK\Mask\Controller\MaskController::class . '::mainAction',
            ],
        ],
    ],
];
