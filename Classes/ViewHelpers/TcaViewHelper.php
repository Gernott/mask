<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\DataStructure\FieldType;
use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
use MASK\Mask\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TcaViewHelper extends AbstractViewHelper
{
    /**
     * FieldHelper
     *
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    protected static $allowedFields = [
        'tt_content' => [
            'header',
            'header_layout',
            'header_position',
            'date',
            'header_link',
            'subheader',
            'bodytext',
            'assets',
            'image',
            'media',
            'imagewidth',
            'imageheight',
            'imageborder',
            'imageorient',
            'imagecols',
            'image_zoom',
            'bullets_type',
            'table_delimiter',
            'table_enclosure',
            'table_caption',
            'file_collections',
            'filelink_sorting',
            'filelink_sorting_direction',
            'target',
            'filelink_size',
            'uploads_description',
            'uploads_type',
            'pages',
            'selected_categories',
            'category_field',
        ]
    ];

    public function __construct(FieldHelper $fieldHelper, StorageRepository $storageRepository)
    {
        $this->fieldHelper = $fieldHelper;
        $this->storageRepository = $storageRepository;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('type', 'string', 'Field type', true);
        $this->registerArgument('table', 'string', 'Table name', true);
    }

    /**
     * Generates TCA Selectbox-Options-Array for a specific TCA-type.
     *
     * @return array all TCA elements of this attribut
     */
    public function render(): array
    {
        $table = $this->arguments['table'];
        $type = $this->arguments['type'];

        if (empty($GLOBALS['TCA'][$table])) {
            return [];
        }

        $fields = [];
        if ($type == FieldType::TAB) {
            $fields = $this->fieldHelper->getFieldsByType($type, $table);
        } elseif (!GeneralUtility::isMaskIrreTable($table)) {
            foreach ($GLOBALS['TCA'][$table]['columns'] as $tcaField => $tcaConfig) {
                $isMaskField = GeneralUtility::isMaskIrreTable($tcaField);
                if (!$isMaskField && !in_array($tcaField, self::$allowedFields[$table] ?? [])) {
                    continue;
                }
                if ($tcaField === 'bodytext' && $table === 'tt_content') {
                    $fieldType = FieldType::RICHTEXT;
                } else {
                    $fieldType = $this->storageRepository->getFormType($tcaField, '', $table);
                }
                if ($fieldType === $type) {
                    $key = $isMaskField ? 'mask' : 'core';
                    $label = $isMaskField ? (str_replace('tx_mask_', '', $tcaField)) : $tcaConfig['label'];
                    $fields[$key][] = [
                        'field' => $tcaField,
                        'label' => $label,
                    ];
                }
            }
        }
        return $fields;
    }
}
