<?php

namespace MASK\Mask\Domain\Model;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
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
 *
 *
 * @package mask
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 *
 */
class Page extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Title of the Pagetemplate.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title;

    /**
     * Backend layout
     *
     * @var integer
     */
    protected $belayout;

    /**
     * Lowercase internal Key. Not Visible in TYPO3 Backend.
     *
     * @var string
     * @validate NotEmpty
     */
    protected $fieldkey;

    /**
     * Content for the HTML Head.
     *
     * @var string
     */
    protected $header;

    /**
     * Default Template if no selection.
     *
     * @var boolean
     */
    protected $defaulttemplate = FALSE;

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the belayout
     *
     * @return integer $belayout
     */
    public function getBelayout()
    {
        return $this->belayout;
    }

    /**
     * Sets the belayout
     *
     * @param integer $belayout
     * @return void
     */
    public function setBelayout($belayout)
    {
        $this->belayout = $belayout;
    }

    /**
     * Returns the fieldkey
     *
     * @return string $fieldkey
     */
    public function getFieldkey()
    {
        return $this->fieldkey;
    }

    /**
     * Sets the fieldkey
     *
     * @param string $fieldkey
     * @return void
     */
    public function setFieldkey($fieldkey)
    {
        $this->fieldkey = $fieldkey;
    }

    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Sets the header
     *
     * @param string $header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Returns the defaulttemplate
     *
     * @return boolean $defaulttemplate
     */
    public function getDefaulttemplate()
    {
        return $this->defaulttemplate;
    }

    /**
     * Sets the defaulttemplate
     *
     * @param boolean $defaulttemplate
     * @return void
     */
    public function setDefaulttemplate($defaulttemplate)
    {
        $this->defaulttemplate = $defaulttemplate;
    }

    /**
     * Returns the boolean state of defaulttemplate
     *
     * @return boolean
     */
    public function isDefaulttemplate()
    {
        return $this->getDefaulttemplate();
    }
}

?>