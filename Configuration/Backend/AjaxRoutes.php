<?php

use MASK\Mask\Controller\AjaxController;
use MASK\Mask\Controller\FieldsController;

return [
    'mask_check_field_key' => [
        'path' => '/mask/checkFieldKey',
        'target' => AjaxController::class . '::checkFieldKey',
    ],
    'mask_check_element_key' => [
        'path' => '/mask/checkElementKey',
        'target' => AjaxController::class . '::checkElementKey',
    ],
    'mask_fieldtypes' => [
        'path' => '/mask/fieldTypes',
        'target' => AjaxController::class . '::fieldTypes',
    ],
    'mask_icons' => [
        'path' => '/mask/icons',
        'target' => AjaxController::class . '::icons',
    ],
    'mask_existing_tca' => [
        'path' => '/mask/existingTca',
        'target' => AjaxController::class . '::existingTca',
    ],
    'mask_tca_fields' => [
        'path' => '/mask/tcaFields',
        'target' => AjaxController::class . '::tcaFields',
    ],
    'mask_tabs' => [
        'path' => '/mask/tabs',
        'target' => AjaxController::class . '::tabs',
    ],
    'mask_language' => [
        'path' => '/mask/language',
        'target' => AjaxController::class . '::language',
    ],
    'mask_richtext_configuration' => [
        'path' => '/mask/richtextConfiguration',
        'target' => AjaxController::class . '::richtextConfiguration',
    ],
    'mask_link_handler' => [
        'path' => '/mask/linkHandler',
        'target' => AjaxController::class . '::linkHandler',
    ],
    'mask_online_media' => [
        'path' => '/mask/onlineMedia',
        'target' => AjaxController::class . '::availableOnlineMedia',
    ],
    'mask_ctypes' => [
        'path' => '/mask/ctypes',
        'target' => AjaxController::class . '::cTypes',
    ],
    'mask_elements' => [
        'path' => '/mask/elements',
        'target' => AjaxController::class . '::elements',
    ],
    'mask_load_element' => [
        'path' => '/mask/loadElement',
        'target' => FieldsController::class . '::loadElement',
    ],
    'mask_load_field' => [
        'path' => '/mask/loadField',
        'target' => FieldsController::class . '::loadField',
    ],
    'mask_save' => [
        'path' => '/mask/save',
        'target' => AjaxController::class . '::save',
    ],
    'mask_multiuse' => [
        'path' => '/mask/multiuse',
        'target' => AjaxController::class . '::multiuse',
    ],
    'mask_all_multiuse' => [
        'path' => '/mask/allMultiuse',
        'target' => AjaxController::class . '::loadAllMultiUse',
    ],
    'mask_missing' => [
        'path' => '/mask/missing',
        'target' => AjaxController::class . '::missingFilesOrFolders',
    ],
    'mask_fix_missing' => [
        'path' => '/mask/fixmissing',
        'target' => AjaxController::class . '::fixMissingFilesOrFolders',
    ],
    'mask_delete' => [
        'path' => '/mask/delete',
        'target' => AjaxController::class . '::delete',
    ],
    'mask_toggle_visibility' => [
        'path' => '/mask/toggle',
        'target' => AjaxController::class . '::toggleVisibility',
    ],
    'mask_html' => [
        'path' => '/mask/html',
        'target' => AjaxController::class . '::showHtmlAction',
    ],
    'mask_backend_layouts' => [
        'path' => '/mask/backendlayouts',
        'target' => AjaxController::class . '::backendLayouts',
    ],
    'mask_field_groups' => [
        'path' => '/mask/fieldGroups',
        'target' => AjaxController::class . '::fieldGroups',
    ],
    'mask_versions' => [
        'path' => '/mask/versions',
        'target' => AjaxController::class . '::versions',
    ],
    'mask_optional_extension_status' => [
        'path' => '/mask/extensions',
        'target' => AjaxController::class . '::optionalExtensionStatus',
    ],
    'mask_setup_complete' => [
        'path' => '/mask/setupComplete',
        'target' => AjaxController::class . '::setupComplete',
    ],
    'mask_setup_autoconfigure' => [
        'path' => '/mask/autoConfigure',
        'target' => AjaxController::class . '::autoConfigureSetup',
    ],
    'mask_tables' => [
        'path' => '/mask/tables',
        'target' => AjaxController::class . '::tables',
    ],
    'mask_migrations_done' => [
        'path' => '/mask/migrationsDone',
        'target' => AjaxController::class . '::migrationsDone',
    ],
    'mask_persist_definition' => [
        'path' => '/mask/persistDefinition',
        'target' => AjaxController::class . '::persistMaskDefinition',
    ],
];
