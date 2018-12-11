<?php

namespace MASK\Mask\ViewHelpers;

use MASK\Mask\Helper\FieldHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class TcaViewHelper extends AbstractViewHelper
{
    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     */
    protected $fieldHelper;

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

    /**
     * @param FieldHelper $fieldHelper
     */
    public function __construct(FieldHelper $fieldHelper = null)
    {
        $this->fieldHelper = $fieldHelper ?? GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
    }

    public function initializeArguments()
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
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render()
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
                        $fieldType = $this->fieldHelper->getFormType($tcaField, '', $table);
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
