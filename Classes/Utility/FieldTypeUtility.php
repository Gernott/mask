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

namespace MASK\Mask\Utility;

use InvalidArgumentException;
use MASK\Mask\Enumeration\FieldType;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreGeneralUtility;

class FieldTypeUtility
{
    public static function getFieldType(array $tca, string $fieldKey): string
    {
        // If TCA is still empty, error out.
        if (empty($tca)) {
            throw new InvalidArgumentException(sprintf('The TCA for the field "%s" must not be empty.', $fieldKey), 1629484158);
        }

        // The tca "type" attribute has to be set. Can also be a fake one like "palette" or "linebreak".
        $tcaType = $tca['config']['type'] ?? '';
        if ($tcaType === '') {
            throw new InvalidArgumentException(sprintf('The TCA type attribute of the field "%s" must not be empty.', $fieldKey), 1629485122);
        }

        // Decide by different tca settings which field type it is.
        switch ($tcaType) {
            case 'input':
                $evals = [];
                if (isset($tca['config']['eval'])) {
                    $evals = explode(',', $tca['config']['eval']);
                }
                if (($tca['config']['dbType'] ?? '') === 'date') {
                    return FieldType::DATE;
                }
                if (($tca['config']['dbType'] ?? '') === 'datetime') {
                    return FieldType::DATETIME;
                }
                if (($tca['config']['renderType'] ?? '') === 'inputDateTime') {
                    return FieldType::TIMESTAMP;
                }
                if (in_array('int', $evals, true)) {
                    return FieldType::INTEGER;
                }
                if (in_array('double2', $evals, true)) {
                    return FieldType::FLOAT;
                }
                if (($tca['config']['renderType'] ?? '') === 'inputLink') {
                    return FieldType::LINK;
                }
                if (($tca['config']['renderType'] ?? '') === 'colorpicker') {
                    return FieldType::COLORPICKER;
                }
                return FieldType::STRING;
            case 'text':
                if (isset($tca['config']['enableRichtext'])) {
                    return FieldType::RICHTEXT;
                }
                return FieldType::TEXT;
            case 'inline':
                if (($tca['config']['foreign_table'] ?? '') === 'sys_file_reference') {
                    // Check if the allowed list contains online media types.
                    $allowedList = $tca['config']['overrideChildTca']['columns']['uid_local']['config']['appearance']['elementBrowserAllowed'] ?? '';
                    $allowedList = CoreGeneralUtility::trimExplode(',', $allowedList, true);
                    $onlineMediaHelperRegistry = CoreGeneralUtility::makeInstance(OnlineMediaHelperRegistry::class);
                    $onlineMediaTypes = $onlineMediaHelperRegistry->getSupportedFileExtensions();
                    if (!empty(array_intersect($allowedList, $onlineMediaTypes))) {
                        return FieldType::MEDIA;
                    }
                    return FieldType::FILE;
                }
                if (($tca['config']['foreign_table'] ?? '') === 'tt_content') {
                    return FieldType::CONTENT;
                }
                return FieldType::INLINE;
            case 'category':
                return FieldType::CATEGORY;
            case 'slug':
                return FieldType::SLUG;
            default:
                // Check if fake tca type is valid.
                try {
                    return (string)FieldType::cast($tcaType);
                } catch (InvalidEnumerationValueException $e) {
                    throw new \InvalidArgumentException(sprintf('Could not resolve the field type of "%s". Please check, if your TCA is correct.', $fieldKey), 1629484452);
                }
        }
    }
}
