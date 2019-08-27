<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Mask',
    'description' => 'Create your own content elements and page templates. Easy to use, even without programming skills because of the comfortable drag and drop system. Stored in structured database tables. Style your frontend with Fluid tags. Ideal, if you want to switch from Templavoila.',
    'category' => 'plugin',
    'author' => 'WEBprofil - Gernot Ploiner e.U.',
    'author_email' => 'office@webprofil.at',
    'author_company' => 'WEBprofil - Gernot Ploiner e.U.',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'version' => '4.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '9.3.0-9.5.99',
            'extbase' => '9.3.0-9.5.99',
            'fluid' => '9.3.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'gridelements' => ''
        ],
    ],
    'autoload' =>
        [
            'psr-4' =>
                [
                    "MASK\\Mask\\" => "Classes/"
                ]
        ]
];
