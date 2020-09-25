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

namespace MASK\Mask\Fluid;

use MASK\Mask\Helper\InlineHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

class FluidTemplateContentObject extends \TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject
{
    /**
     * Change variables for view
     *
     * @param array $conf Configuration
     * @return array
     * @throws Exception
     */
    protected function getContentObjectVariables(array $conf = []): array
    {
        // Call Parent Function to maintain core functions
        $variables = parent::getContentObjectVariables($conf);

        // Make some enhancements to data of pages
        if ($this->cObj->getCurrentTable() === 'pages') {
            $data = $variables['data'];
            $inlineHelper = GeneralUtility::makeInstance(InlineHelper::class);
            $inlineHelper->addFilesToData($data, 'pages');
            $inlineHelper->addIrreToData($data, 'pages');
            $variables['data'] = $data;
        }

        return $variables;
    }
}
