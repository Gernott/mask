<?php

namespace MASK\Mask\Fluid;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

class FluidTemplateContentObject extends \TYPO3\CMS\Frontend\ContentObject\FluidTemplateContentObject
{

    /**
     * InlineHelper
     *
     * @var \MASK\Mask\Helper\InlineHelper
     * @inject
     */
    protected $inlineHelper;

    /**
     * storageRepository
     *
     * @var \MASK\Mask\Domain\Repository\StorageRepository
     * @inject
     */
    protected $storageRepository;

    /**
     * Change variables for view
     *
     * @param array $conf Configuration
     * @author Benjamin Butschell <bb@webprofil.at>
     * @return void
     */
    protected function getContentObjectVariables(array $conf = array())
    {
        // Call Parent Function to maintain core functions
        $variables = parent::getContentObjectVariables($conf);

        $this->storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Domain\\Repository\\StorageRepository');
        $this->inlineHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('MASK\\Mask\\Helper\\InlineHelper', $this->storageRepository);

        // Make some enhancements to data
        $data = $variables['data'];
        $this->inlineHelper->addFilesToData($data, "pages");
        $this->inlineHelper->addIrreToData($data, "pages");
        $variables['data'] = $data;

        return $variables;
    }
}
