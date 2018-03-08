<?php

namespace MASK\Mask\Utility;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Benjamin Butschell <bb@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * General useful methods
 *
 * @author Benjamin Butschell <bb@webprofil.at>
 */
class GeneralUtility
{

    /**
     * StorageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     */
    protected $storageRepository;

    /**
     * @param \MASK\Mask\Domain\Repository\StorageRepository $storageRepository
     */
    public function __construct(\MASK\Mask\Domain\Repository\StorageRepository $storageRepository = NULL)
    {
        if (!$storageRepository) {
            $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        } else {
            $this->storageRepository = $storageRepository;
        }
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @param string $fieldKey TCA Type
     * @param string $evalValue value to search for
     * @param string $type elementtype
     * @return boolean $evalValue is set
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function isEvalValueSet($fieldKey, $evalValue, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();
        $found = FALSE;
        if ($storage[$type]["tca"][$fieldKey]["config"]["eval"] != "") {
            $evals = explode(",", $storage[$type]["tca"][$fieldKey]["config"]["eval"]);
            foreach ($evals as $index => $eval) {
                $evals[$index] = strtolower($eval);
            }
            $found = array_search(strtolower($evalValue), $evals) !== FALSE;
        }
        return $found;
    }

    /**
     * Returns the rte_transform properties
     *
     * @param string $fieldKey TCA Type
     * @param string $type elementtype
     * @return string $rte_transform
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getRteTransformMode($fieldKey, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();
        $transformMode = "";
        $matches = array();
        if ($storage[$type]["tca"][$fieldKey]["defaultExtras"] != "") {
            $re = "/(rte_transform\\[([a-z=_]+)\\])/";
            preg_match($re, $storage[$type]["tca"][$fieldKey]["defaultExtras"], $matches);
            $transformMode = end($matches);
        }
        return $transformMode;
    }

    /**
     * Returns value for jsopenparams property
     *
     * @param string $fieldKey TCA Type
     * @param string $property value to search for
     * @param string $type elementtype
     * @return boolean $evalValue is set
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function getJsOpenParamValue($fieldKey, $property, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();
        $value = null;
        if ($storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["JSopenParams"] != "") {
            $properties = explode(",", $storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["JSopenParams"]);
            foreach ($properties as $setProperty) {
                $keyPair = explode("=", $setProperty);
                if ($property == $keyPair[0]) {
                    $value = $keyPair[1];
                    break;
                }
            }
        }

        // if nothing was found, set the default values
        if ($value == null) {
            switch ($property) {
                case "height":
                    $value = 300;
                    break;
                case "width":
                    $value = 500;
                    break;
                case "status":
                    $value = 0;
                    break;
                case "menubar":
                    $value = 0;
                    break;
                case "scrollbars":
                    $value = 1;
                    break;
                default:
                    $value = null;
            }
        }
        return $value;
    }

    /**
     * Checks if a $evalValue is set in a field
     *
     * @param string $fieldKey TCA Type
     * @param string $evalValue value to search for
     * @param string $type elementtype
     * @return boolean $evalValue is set
     * @author Benjamin Butschell <bb@webprofil.at>
     */
    public function isBlindLinkOptionSet($fieldKey, $evalValue, $type = "tt_content")
    {
        $storage = $this->storageRepository->load();
        $found = FALSE;
        if ($storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["params"]["blindLinkOptions"] != "") {
            $evals = explode(",", $storage[$type]["tca"][$fieldKey]["config"]["wizards"]["link"]["params"]["blindLinkOptions"]);
            $found = array_search(strtolower($evalValue), $evals) !== FALSE;
        }
        return $found;
    }

    /**
     * replace keys
     *
     * @author Gernot Ploiner <gp@webprofil.at>
     * @return array
     */
    public function replaceKey($data, $replace_key, $key = "--key--")
    {
        foreach ($data as $elem_key => $elem) {
            if (is_array($elem)) {
                $data[$elem_key] = $this->replaceKey($elem, $replace_key);
            } else {
                if ($data[$elem_key] == $key) {
                    $data[$elem_key] = $replace_key;
                }
            }
        }
        return $data;
    }

    /**
     * Searches an array of strings and returns the first string, that is not a tab
     * @param array $fields
     * @return $string
     */
    public function getFirstNoneTabField($fields)
    {
        if (count($fields)) {
            $potentialFirst = $fields[0];
            if (strpos($potentialFirst, "--div--") !== FALSE) {
                unset($fields[0]);
                return $this->getFirstNoneTabField($fields);
            } else {
                return $potentialFirst;
            }
        } else {
            return "";
        }
    }

    /**
     * Removes all the blank options from the tca
     * @param array $haystack
     * @return array
     */
    public function removeBlankOptions($haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->removeBlankOptions($haystack[$key]);
            }
            if( (is_array($haystack[$key]) && empty($haystack[$key])) || (is_string($haystack[$key]) && !strlen($haystack[$key])) ) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }
}
