<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mask',
    'description' => 'Create your own content elements and page templates. Easy to use, even without programming skills because of the comfortable drag and drop system. Stored in structured database tables. Style your frontend with Fluid tags. Ideal, if you want to switch from Templavoila.',
    'category' => 'plugin',
    'author' => 'WEBprofil - Gernot Ploiner e.U.',
    'author_email' => 'office@webprofil.at',
    'author_company' => 'WEBprofil - Gernot Ploiner e.U.',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '9.0.8',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'fluid' => '12.4.0-13.4.99',
            'fluid_styled_content' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'gridelements' => '',
            'container' => '',
        ],
    ],
];
