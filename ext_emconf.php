<?php
$EM_CONF[$_EXTKEY] = array(
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
    'version' => '2.1.1',
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.0-8.2.99',
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
