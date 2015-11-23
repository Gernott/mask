<?php

// backwards compatibility for typo3 6.2
$version = \TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version();
$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger($version);

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		  'MASK.' . $_EXTKEY, 'ContentRenderer', array('Frontend' => 'contentelement'), array('Frontend' => '')
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mask/Configuration/TypoScript/page.txt">');

// Load JSON file
$extConf = unserialize($_EXTCONF);
if (file_exists(PATH_site . $extConf["json"])) {
	$json = json_decode(file_get_contents(PATH_site . $extConf["json"]), true);
}

// Icon registry
// backwards compatibility for typo3 6.2
if ($versionNumber >= 7005000) {
	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\Imaging\IconRegistry");
	$maskIcons = array("Check", "Date", "Datetime", "File", "Float", "Inline", "Integer", "Link", "Radio", "Richtext", "Select", "String", "Text");
	foreach ($maskIcons as $maskIcon) {
		$iconRegistry->registerIcon(
				  'mask-fieldtype-' . $maskIcon, 'TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider', array(
			 'source' => 'EXT:mask/Resources/Public/Icons/fieldtypes/' . $maskIcon . '.svg'
				  )
		);
	}
}

// generate page TSconfig
$content = "";
$temp = "";
// Load page.ts Template
$template = file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mask') . "Resources/Private/Mask/page.ts", true);
// make content-Elements
if ($json["tt_content"]["elements"]) {

	foreach ($json["tt_content"]["elements"] as $element) {
		// backwards compatibility for typo3 6.2
		if ($versionNumber >= 7005000) {
			// Register icons for contentelements
			$iconIdentifier = 'mask-ce-' . $element["key"];
			$iconRegistry->registerIcon(
					  $iconIdentifier, "MASK\Mask\Imaging\IconProvider\ContentElementIconProvider", array(
				 'contentElementKey' => $element["key"]
					  )
			);
			$temp = str_replace("###ICON###", "iconIdentifier = " . $iconIdentifier, $template);
		} else {
			$temp = str_replace("###ICON###", "icon = ../" . $extConf["preview"] . 'ce_' . $element["key"] . '.png', $template);
		}

		$temp = str_replace("###KEY###", $element["key"], $temp);
		$temp = str_replace("###LABEL###", $element["label"], $temp);
		$temp = str_replace("###DESCRIPTION###", $element["description"], $temp);
		$content.= $temp;

		// Labels
		$content .= "\n[userFunc = user_mask_contentType(CType|mask_" . $element["key"] . ")]\n";
		if ($element["columns"]) {
			foreach ($element["columns"] as $index => $column) {
				$content .= " TCEFORM.tt_content." . $column . ".label = " . $element["labels"][$index] . "\n";
			}
		}
		$content .= "[end]\n\n";
	}
}


// make pages
$pageColumns = array();
$disableColumns = "";
$pagesContent = "";
if ($json["pages"]["elements"]) {
	foreach ($json["pages"]["elements"] as $element) {
		// Labels for pages
		$pagesContent .= "\n[userFunc = user_mask_beLayout(" . $element["key"] . ")]\n";
		// if page has backendlayout with this element-key
		if ($element["columns"]) {
			foreach ($element["columns"] as $index => $column) {
				$pagesContent .= " TCEFORM.pages." . $column . ".label = " . $element["labels"][$index] . "\n";
				$pagesContent .= " TCEFORM.pages_language_overlay." . $column . ".label = " . $element["labels"][$index] . "\n";
			}
			$pagesContent .= "\n";
			foreach ($element["columns"] as $index => $column) {
				$pageColumns[] = $column;
				$pagesContent .= " TCEFORM.pages." . $column . ".disabled = 0\n";
				$pagesContent .= " TCEFORM.pages_language_overlay." . $column . ".disabled = 0\n";
			}
		}
		$pagesContent .= "[end]\n";
	}
}
// disable all fields by default and only activate by condition
foreach ($pageColumns as $column) {
	$disableColumns .= "TCEFORM.pages." . $column . ".disabled = 1\n";
	$disableColumns .= "TCEFORM.pages_language_overlay." . $column . ".disabled = 1\n";
}
$pagesContent = $disableColumns . "\n" . $pagesContent;
$content .= $pagesContent;

// put into page TSconfig:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($content);

// generate TypoScript setup
$setupContent = '
module.tx_mask {
	view {
		templateRootPaths {
			10 = EXT:mask/Resources/Private/Backend62/Templates/
		}
		partialRootPaths {
			10 = EXT:mask/Resources/Private/Backend62/Partials/
		}
		layoutRootPaths {
			10 = EXT:mask/Resources/Private/Backend62/Layouts/
		}
	}
	persistence{
		classes {
			MASK\Mask\Domain\Model\BackendLayout {
				mapping {
					tableName = backend_layout
					columns {
						uid.mapOnProperty = uid
						title.mapOnProperty = title
					}
				}
			}
		}
	}
}
[compatVersion = 7.0.0]
module.tx_mask {
	view {
		templateRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Templates/
		}
		partialRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Partials/
		}
		layoutRootPaths {
			10 = EXT:mask/Resources/Private/Backend/Layouts/
		}
	}
}
[end]
';
// Load setup.ts Template
$template = file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mask') . "Resources/Private/Mask/setup.ts", true);
// Fill setup.ts:
if ($json["tt_content"]["elements"]) {
	foreach ($json["tt_content"]["elements"] as $element) {
		$temp = str_replace("###KEY###", $element["key"], $template);
		$temp = str_replace("###PATH###", $extConf['content'] . $element["key"] . '.html', $temp);
		$setupContent.= $temp;
	}
}
// put into setup-field:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup($setupContent);

// for conditions on tt_content:
if (!function_exists('user_mask_contentType')) {

	function user_mask_contentType($param = "") {
		if (is_array($_REQUEST["edit"]["tt_content"])) {
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
			return false;
		}
	}

}

// for conditions on the backend-layouts
if (!function_exists('user_mask_beLayout')) {

	function user_mask_beLayout($layout) {
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
		} else { // after opening or creating pages_language_overlay
			$uid = $GLOBALS["SOBE"]->viewId;
		}

		if ($uid) {
			$sql = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					  "backend_layout, backend_layout_next_level", "pages", "uid = " . $uid
			);
			$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql);

			$backend_layout = $data["backend_layout"];
			$backend_layout_next_level = $data["backend_layout_next_level"];

			if ($backend_layout > 0) { // If backend_layout is set on current page
				if ($backend_layout == $layout) { // Check backend_layout of current page
					return true;
				} else {
					return false;
				}
			} elseif ($backend_layout_next_level > 0) { // If backend_layout_next_level is set on current page
				if ($backend_layout_next_level == $layout) { // Check backend_layout_next_level of current page
					return true;
				} else {
					return false;
				}
			} else { // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
				$sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
				$rootline = $sysPage->getRootLine($uid, '', TRUE);
				foreach ($rootline as $page) {
					if ($page["backend_layout_next_level"] == $layout) {
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

// SQL inject:
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService', 'tablesDefinitionIsBeingBuilt', 'MASK\\Mask\\Controller\\FrontendController', 'addDatabaseTablesDefinition');

// Hook for tt_content inline elements
//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:mask/Classes/Hooks/class.tx_mask_tcemainprocdm.php:tx_mask_tcemainprocdm';
// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\FluidTemplateContentObject'] = array(
	 'className' => 'MASK\\Mask\\Fluid\\FluidTemplateContentObject'
);

// Hook to override tt_content backend_preview
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/PageLayoutViewDrawItem.php:MASK\Mask\Hooks\PageLayoutViewDrawItem';
