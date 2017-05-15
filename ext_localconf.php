<?php
if (!defined('TYPO3_MODE')) {
   die('Access denied.');
}

// initialize mask utility for various things
$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
$fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
$typoScriptCodeGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\CodeGenerator\\TyposcriptCodeGenerator');
$settingsService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Service\\SettingsService');
$configuration = $storageRepository->load();
$settings = $settingsService->get();

// Register Plugin to render content in the frontend
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'MASK.' . $_EXTKEY, 'ContentRenderer', array('Frontend' => 'contentelement'), array('Frontend' => '')
);

// Register Icons needed in the backend module
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\Imaging\IconRegistry");
$maskIcons = array("Check", "Date", "Datetime", "File", "Float", "Inline", "Integer", "Link", "Radio", "Richtext", "Select", "String", "Tab", "Text", "Content");
foreach ($maskIcons as $maskIcon) {
   $iconRegistry->registerIcon(
	   'mask-fieldtype-' . $maskIcon, 'TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider', array(
	   'source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . $maskIcon . '.svg'
	   )
   );
}

// Add all the typoscript we need in the correct files
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/Configuration/TypoScript/page.txt">');
$tsConfig = $typoScriptCodeGenerator->generateTsConfig($configuration);
$pageTs = $typoScriptCodeGenerator->generatePageTyposcript($configuration);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($tsConfig);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTs);

$setupTs = $typoScriptCodeGenerator->generateSetupTyposcript($configuration, $settings);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($setupTs);

// for conditions on tt_content
if (!function_exists('user_mask_contentType')) {

   function user_mask_contentType($param = "")
   {
	  if (isset($_REQUEST["edit"]["tt_content"]) && is_array($_REQUEST["edit"]["tt_content"])) {
		 $field = explode("|", $param);
		 $request = $_REQUEST;
		 $first = array_shift($request["edit"]["tt_content"]);

		 if ($first == "new") { // if new element
			if ($_REQUEST["defVals"]["tt_content"]["CType"] == $field[1]) {
			   return true;
			} else {
			   return false;
			}
		 } else { // if element exists
			$uid = intval(key($_REQUEST["edit"]["tt_content"]));
			$sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$field[0], "tt_content", "uid = " . $uid
			);
			$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);
			if ($data[$field[0]] == $field[1]) {
			   return true;
			} else {
			   return false;
			}
		 }
	  } else {
		 // if content element is loaded by ajax, then it's ok
		 if (is_array($_REQUEST["ajax"])) {
			return true;
		 } else {
			return false;
		 }
	  }
   }
}

// for conditions on the backend-layouts
if (!function_exists('user_mask_beLayout')) {

   function user_mask_beLayout($layout = null)
   {
	  // get current page uid:
	  if (is_array($_REQUEST["data"]["pages"])) { // after saving page
		 $uid = intval(key($_REQUEST["data"]["pages"]));
	  } elseif (is_array($_REQUEST["data"]["pages_language_overlay"])) {
		 $po_uid = key($_REQUEST["data"]["pages_language_overlay"]);
		 if ($_REQUEST["data"]["pages_language_overlay"][$po_uid]["pid"]) { // after saving a new pages_language_overlay
			$uid = $_REQUEST["data"]["pages_language_overlay"][$po_uid]["pid"];
		 } else { // after saving an existing pages_language_overlay
			$po_uid = intval($po_uid);
			$sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				"pid", "pages_language_overlay", "uid = " . $po_uid
			);
			$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);
			$uid = $data["pid"];
		 }
	  } elseif ($GLOBALS["SOBE"]->editconf["pages"]) { // after opening pages
		 $uid = intval(key($GLOBALS["SOBE"]->editconf["pages"]));
	  } elseif ($GLOBALS["SOBE"]->viewId) { // after opening or creating pages_language_overlay
		 $uid = $GLOBALS["SOBE"]->viewId;
	  } else {
		 if ($GLOBALS["_SERVER"]["HTTP_REFERER"] != "") {
			$url = $GLOBALS["_SERVER"]["HTTP_REFERER"];
			$queryString = parse_url($url, PHP_URL_QUERY);
			$result = array();
			parse_str($queryString, $result);
			if ($result["id"]) {
			   $uid = (int) $result["id"];
			}
		 }
	  }

	  if ($uid) {
		 $sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			 "backend_layout, backend_layout_next_level", "pages", "uid = " . $uid
		 );
		 $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);

		 $backend_layout = $data["backend_layout"];
		 $backend_layout_next_level = $data["backend_layout_next_level"];

		 if ($backend_layout !== "") { // If backend_layout is set on current page
			if (in_array($backend_layout, [$layout, "pagets__" . $layout])) { // Check backend_layout of current page
			   return true;
			} else {
			   return false;
			}
		 } elseif ($backend_layout_next_level !== "") { // If backend_layout_next_level is set on current page
			if (in_array($backend_layout_next_level, [$layout, "pagets__" . $layout])) { // Check backend_layout_next_level of current page
			   return true;
			} else {
			   return false;
			}
		 } else { // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
			$sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			$rootline = $sysPage->getRootLine($uid, '', TRUE);
			foreach ($rootline as $page) {
			   if (in_array($page["backend_layout_next_level"], [$layout, "pagets__" . $layout])) {
				  return true;
			   }
			}
			return false;
		 }
	  } else {
		 return false;
	  }
   }
}

// set rootlinefields and pageoverlayfields
if ($json['pages']['tca']) {
   $rootlineFields = explode(",", $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
   $pageOverlayFields = explode(",", $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields']);
   foreach ($json['pages']['tca'] as $fieldKey => $value) {
	  $formType = $fieldHelper->getFormType($fieldKey, "", "pages");
	  if ($formType !== "Tab") {
		 // Add addRootLineFields and pageOverlayFields for all pagefields
		 $rootlineFields[] = $fieldKey;
		 $pageOverlayFields[] = $fieldKey;
	  }
   }
   $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(",", $rootlineFields);
   $GLOBALS['TYPO3_CONF_VARS']['FE']['pageOverlayFields'] = implode(",", $pageOverlayFields);
}

// SQL inject:
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService', 'tablesDefinitionIsBeingBuilt', 'MASK\\Mask\\CodeGenerator\\SqlCodeGenerator', 'addDatabaseTablesDefinition');

// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\FluidTemplateContentObject'] = array(
	'className' => 'MASK\\Mask\\Fluid\\FluidTemplateContentObject'
);

// Hook to override tt_content backend_preview
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/PageLayoutViewDrawItem.php:MASK\Mask\Hooks\PageLayoutViewDrawItem';
