<?php

declare(strict_types=1);

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 2 or later
 */
class Content extends AbstractEntity
{

    /**
     * Title for the New-Contentelement-Wizard.
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title;

    /**
     * Description for the New-Contentelement-Wizard.
     *
     * @var string
     */
    protected $description;

    /**
     * Short Title for the Selectbox in Content-Edit-Mode.
     *
     * @var string
     */
    protected $shorttitle;

    /**
     * Lowercase internal Key. Not Visible in TYPO3 Backend.
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $fieldkey;

    /**
     * contentType
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $contentType;

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param $contentType
     * @return Content
     */
    public function setContentType($contentType): Content
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * Returns the shorttitle
     *
     * @return string $shorttitle
     */
    public function getShorttitle(): string
    {
        return $this->shorttitle;
    }

    /**
     * Sets the shorttitle
     *
     * @param string $shorttitle
     */
    public function setShorttitle($shorttitle): void
    {
        $this->shorttitle = $shorttitle;
    }

    /**
     * Returns the fieldkey
     *
     * @return string $fieldkey
     */
    public function getFieldkey(): string
    {
        return $this->fieldkey;
    }

    /**
     * Sets the fieldkey
     *
     * @param string $fieldkey
     */
    public function setFieldkey($fieldkey): void
    {
        $this->fieldkey = $fieldkey;
    }
}
