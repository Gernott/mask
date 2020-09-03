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

use MASK\Mask\Domain\Repository\StorageRepository;
use MASK\Mask\Helper\FieldHelper;
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

    /**
     * @var array
     */
    protected static $forbiddenFields = [
        'starttime',
        'endtime',
        'hidden',
        'sectionIndex',
        'linkToTop',
        'fe_group',
        'CType',
        'doktype',
        'title',
        'TSconfig',
        'php_tree_stop',
        'storage_pid',
        'tx_impexp_origuid',
        't3ver_label',
        'editlock',
        'url_scheme',
        'extendToSubpages',
        'nav_title',
        'nav_hide',
        'subtitle',
        'target',
        'alias',
        'url',
        'urltype',
        'lastUpdated',
        'newUntil',
        'cache_timeout',
        'cache_tags',
        'no_cache',
        'no_search',
        'shortcut',
        'shortcut_mode',
        'content_from_pid',
        'mount_pid',
        'keywords',
        'description',
        'abstract',
        'author',
        'author_email',
        'is_siteroot',
        'mount_pid_ol',
        'module',
        'fe_login_mode',
        'l18n_cfg',
        'backend_layout',
        'backend_layout_next_level',
        'tx_gridelements_children',
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
     * @author Gernot Ploiner <gp@webprofil.at>
     */
    public function render(): array
    {
        $table = $this->arguments['table'];
        $type = $this->arguments['type'];

        if (empty($GLOBALS['TCA'][$table])) {
            return [];
        }

        $fields = [];
        if ($type === 'Tab') {
            $fields = $this->fieldHelper->getFieldsByType($type, $table);
        } else {
            if (in_array($table, ['tt_content', 'pages'])) {
                foreach ($GLOBALS['TCA'][$table]['columns'] as $tcaField => $tcaConfig) {
                    if ($table === 'tt_content' || ($table === 'pages' && strpos($tcaField, 'tx_mask_') === 0)) {
                        $fieldType = $this->storageRepository->getFormType($tcaField, '', $table);
                        if (($fieldType === $type || ($fieldType === 'Text' && $type === 'Richtext'))
                            && !in_array($tcaField, self::$forbiddenFields, true)
                        ) {
                            $fields[] = [
                                'field' => $tcaField,
                                'label' => $tcaConfig['label'],
                            ];
                        }
                    }
                }
            }
        }
        return $fields;
    }
}
