<?php

namespace MASK\Mask\Domain\Model;

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

/**
 * This model represents a back-end user.
 *
 * @api
 */
class BackendLayout extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     * @validate notEmpty
     */
    protected $title = '';

    /**
     * @var int
     * @validate notEmpty
     */
    protected $uid = '';

    /**
     * @var string
     */
    protected $icon = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * Gets the title.
     *
     * @return string the title, will not be empty
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title the user name to set, must not be empty
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Gets the uid.
     *
     * @return int the uid, will not be empty
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Sets the uid.
     *
     * @param int $uid the user name to set, must not be empty
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Gets the icon.
     *
     * @return string the icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon.
     *
     * @param string $icon
     * @return void
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Gets the description.
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}

?>