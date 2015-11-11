<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			  'MASK.' . $_EXTKEY, 'tools', // Make module a submodule of 'admin'
			  'mask', // Submodule key
			  'top', // Position
			  array(
		 'WizardContent' => 'list, new, create, edit, update, delete, generate, showHtml, createMissingFolders',
		 'WizardPage' => 'list, new, create, edit, update, delete',
			  ), array(
		 'access' => 'admin',
		 'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module-mask_wizard.svg',
		 'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mask.xlf',
			  )
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
			  'WizardController::checkFieldKey', 'MASK\Mask\Controller\WizardController->checkFieldKey'
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
			  'WizardController::checkElementKey', 'MASK\Mask\Controller\WizardController->checkElementKey'
	);
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Mask');

// Load JSON-File from $_EXTCONF:
$extConf = unserialize($_EXTCONF);
if (file_exists(PATH_site . $extConf["json"])) {

	$json = json_decode(file_get_contents(PATH_site . $extConf["json"]), true);

	/* @var $objectManager TYPO3\CMS\Extbase\Object\ObjectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	$utility = new \MASK\Mask\Utility\MaskUtility($objectManager);

	// Generate TCA for Content-Elements
	$contentColumns = $utility->generateFieldsTca($json["tt_content"]["tca"]);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $contentColumns);
	$utility->setElementsTca($json["tt_content"]["elements"]);

	// Generate TCA for Pages
	$pagesColumns = $utility->generateFieldsTca($json["pages"]["tca"]);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesColumns);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages_language_overlay', $pagesColumns);
	$utility->setPageTca($json["pages"]["tca"], $TYPO3_CONF_VARS["FE"]);

	// Generate TCA for Inline-Fields
	$utility->setInlineTca($json);
}

// include css for styling of backend preview of mask content elements
$TBE_STYLES['skins']['mask']['name'] = 'mask';
$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = 'EXT:mask/Resources/Public/Styles/Backend/';
//$TBE_STYLES['skins']['mask']['stylesheetDirectories'][] = "/" . $extConf["backend"];


