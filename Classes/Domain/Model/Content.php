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

namespace MASK\Mask\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
