<?php
$EM_CONF[$_EXTKEY] = array(
    'title' => 'Mask',
    'description' => 'Create your own content elements and page templates. Easy to use, even without programming skills because of the comfortable drag&drop system. Stored in structured database tables. Style your frontend with Fluid tags. Ideal, if you want to switch from Templavoila.',
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
    'version' => '1.1.4',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.6.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'gridelements' => ''
        ),
    ),
    'autoload' =>
    array(
        'psr-4' =>
        array(
            "MASK\\Mask\\" => "Classes/"
        )
    )
);
