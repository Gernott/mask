<?php

$EM_CONF[$_EXTKEY] = array(
	 'title' => 'Mask',
	 'description' => 'Page- and Contentmasks.',
	 'category' => 'plugin',
	 'author' => 'WEBprofil Team',
	 'author_email' => 'office@webprofil.at',
	 'author_company' => 'WEBprofil - Gernot Ploiner e.U.',
	 'shy' => '',
	 'priority' => '',
	 'module' => '',
	 'state' => 'beta',
	 'internal' => '',
	 'uploadfolder' => '0',
	 'createDirs' => '',
	 'modify_tables' => '',
	 'clearCacheOnLoad' => 1,
	 'lockType' => '',
	 'version' => '1.0.0dev',
	 'constraints' => array(
		  'depends' => array(
				'extbase' => '6.2.0-6.2.99',
				'fluid' => '6.2.0-6.2.99',
				'typo3' => '6.2.0-6.2.99',
		  ),
		  'conflicts' => array(
		  ),
		  'suggests' => array(
				'gridelements' => ''
		  ),
	 ),
);
?>