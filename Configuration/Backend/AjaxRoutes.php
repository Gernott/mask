<?php

return [
    'mask_check_field_key' => [
        'path' => '/mask/checkFieldKey',
        'target' => \MASK\Mask\Controller\WizardController::class . '::checkFieldKey'
    ],
    'mask_check_element_key' => [
        'path' => '/mask/checkElementKey',
        'target' => \MASK\Mask\Controller\WizardController::class . '::checkElementKey'
    ],
];
