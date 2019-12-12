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

// Register Icons needed in the backend module
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\Imaging\IconRegistry");
$maskIcons = array(
    "Check",
    "Date",
    "Datetime",
    "File",
    "Float",
    "Inline",
    "Integer",
    "Link",
    "Radio",
    "Richtext",
    "Select",
    "String",
    "Tab",
    "Text",
    "Content"
);
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

// for conditions on the backend-layouts
if (!function_exists('user_mask_beLayout')) {

    function user_mask_beLayout($layout = null)
    {
        // get current page uid:
        if (is_array($_REQUEST["data"]["pages"])) { // after saving page
            $uid = intval(key($_REQUEST["data"]["pages"]));
        } elseif ($GLOBALS["SOBE"]->editconf["pages"]) { // after opening pages
            $uid = intval(key($GLOBALS["SOBE"]->editconf["pages"]));
        } else {
            if ($GLOBALS["_SERVER"]["HTTP_REFERER"] != "") {
                $url = $GLOBALS["_SERVER"]["HTTP_REFERER"];
                $queryString = parse_url($url, PHP_URL_QUERY);
                $result = array();
                parse_str($queryString, $result);
                if ($result["id"]) {
                    $uid = (int)$result["id"];
                }
            }
        }

        if ($uid) {
            /** @var \TYPO3\CMS\Core\Database\Connection $connection */
            $connection = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getConnectionForTable('pages');
            $query = $connection->createQueryBuilder();
            /** @var \TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction $deletedRestriction */
            $deletedRestriction = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction::class);
            $query->getRestrictions()->removeAll()->add($deletedRestriction);
            $data = $query->select('backend_layout', 'backend_layout_next_level')
                ->from('pages')
                ->where($query->expr()->eq('uid', $uid))
                ->execute()
                ->fetch(\Doctrine\DBAL\FetchMode::ASSOCIATIVE);

            $backend_layout = $data["backend_layout"];
            $backend_layout_next_level = $data["backend_layout_next_level"];

            if ($backend_layout !== "") { // If backend_layout is set on current page
                if (in_array($backend_layout,
                    [$layout, "pagets__" . $layout])) { // Check backend_layout of current page
                    return true;
                } else {
                    return false;
                }
            } elseif ($backend_layout_next_level !== "") { // If backend_layout_next_level is set on current page
                if (in_array($backend_layout_next_level,
                    [$layout, "pagets__" . $layout])) { // Check backend_layout_next_level of current page
                    return true;
                } else {
                    return false;
                }
            } else { // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
                $sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
                try {
                    $rootline = $sysPage->getRootLine($uid, '');
                } catch (Exception $e) {
                    $rootline = [];
                }
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

// set root line fields
if ($json['pages']['tca']) {
    $rootlineFields = explode(",", $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']);
    foreach ($json['pages']['tca'] as $fieldKey => $value) {
        $formType = $fieldHelper->getFormType($fieldKey, "", "pages");
        if ($formType !== "Tab") {
            // Add addRootLineFields for all page fields
            $rootlineFields[] = $fieldKey;
        }
    }
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(",", $rootlineFields);
}

// SQL inject:
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect('TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService', 'tablesDefinitionIsBeingBuilt',
    'MASK\\Mask\\CodeGenerator\\SqlCodeGenerator', 'addDatabaseTablesDefinition');

// Enhance Fluid Output with overridden FluidTemplateContentObject
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\FluidTemplateContentObject'] = array(
    'className' => 'MASK\\Mask\\Fluid\\FluidTemplateContentObject'
);

// Hook to override tt_content backend_preview
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$_EXTKEY] = \MASK\Mask\Hooks\PageLayoutViewDrawItem::class;
// Hook to override colpos check for unused tt_content elements
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['record_is_used'] [] = MASK\Mask\Hooks\PageLayoutViewHook::class . '->contentIsUsed';
