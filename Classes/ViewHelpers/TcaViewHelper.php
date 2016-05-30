<?php

namespace MASK\Mask\ViewHelpers;

/**
 *
 * Example
 * {namespace mask=MASK\Mask\ViewHelpers}
 *
 * @package TYPO3
 * @subpackage mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class TcaViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * FieldHelper
     *
     * @var \MASK\Mask\Helper\FieldHelper
     * @inject
     */
    protected $fieldHelper;

    /**
     * Generates TCA Selectbox-Options-Array for a specific TCA-type.
     *
     * @param string $type TCA Type
     * @param string $table Tablename
     * @return array all TCA elements of this attribut
     * @author Gernot Ploiner <gp@webprofil.at>
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function render($type, $table)
    {
        $this->fieldHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\FieldHelper');
        // in tt_content allow all fields except $forbiddenFields
        if ($table == "tt_content") {
            $forbiddenFields = array(
                'starttime', 'endtime', 'hidden', 'sectionIndex', 'linkToTop', 'fe_group',
                'CType', 'doktype', 'title', 'TSconfig', 'php_tree_stop', 'storage_pid',
                'tx_impexp_origuid', 't3ver_label', 'editlock', 'url_scheme',
                'extendToSubpages', 'nav_title', 'nav_hide', 'subtitle', 'target', 'alias',
                'url', 'urltype', 'lastUpdated', 'newUntil', 'cache_timeout', 'cache_tags',
                'no_cache', 'no_search', 'shortcut', 'shortcut_mode', 'content_from_pid',
                'mount_pid', 'keywords', 'description', 'abstract', 'author',
                'author_email', 'is_siteroot', 'mount_pid_ol', 'module', 'fe_login_mode',
                'l18n_cfg', 'backend_layout', 'backend_layout_next_level', 'tx_gridelements_children'
            );
            foreach ($GLOBALS['TCA'][$table]['columns'] as $tcaField => $tcaConfig) {
                $fieldType = $this->fieldHelper->getFormType($tcaField, "", $table);
                if (
                    ($fieldType == $type ||
                    ($fieldType == "Text" &&
                    ($type == "Text" || $type == "Richtext")
                    )) && array_search($tcaField, $forbiddenFields) === FALSE
                ) {
                    $temp["field"] = $tcaField;
                    $temp["label"] = $tcaConfig["label"];
                    $content[] = $temp;
                }
            }
        } else {
            // in pages allow only fields already created by mask
            foreach ($GLOBALS['TCA'][$table]['columns'] as $tcaField => $tcaConfig) {
                $fieldType = $this->fieldHelper->getFormType($tcaField, "", $table);
                if (
                    ($fieldType == $type ||
                    ($fieldType == "Text" &&
                    ($type == "Text" || $type == "Richtext")
                    )) && strrpos($tcaField, "tx_mask_", -strlen($tcaField)) !== false
                ) {
                    $temp["field"] = $tcaField;
                    $temp["label"] = $tcaConfig["label"];
                    $content[] = $temp;
                }
            }
        }
        return $content;
    }
}
