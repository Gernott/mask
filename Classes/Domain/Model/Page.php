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

class Page extends AbstractEntity
{

    /**
     * Title of the Pagetemplate.
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title;

    /**
     * Backend layout
     *
     * @var int
     */
    protected $belayout;

    /**
     * Lowercase internal Key. Not Visible in TYPO3 Backend.
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
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
     * @var bool
     */
    protected $defaulttemplate = false;

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
     * Returns the belayout
     *
     * @return int $belayout
     */
    public function getBelayout(): int
    {
        return $this->belayout;
    }

    /**
     * Sets the belayout
     *
     * @param int $belayout
     */
    public function setBelayout($belayout): void
    {
        $this->belayout = $belayout;
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

    /**
     * Returns the header
     *
     * @return string $header
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Sets the header
     *
     * @param string $header
     */
    public function setHeader($header): void
    {
        $this->header = $header;
    }

    /**
     * Returns the defaulttemplate
     *
     * @return bool $defaulttemplate
     */
    public function getDefaulttemplate(): bool
    {
        return $this->defaulttemplate;
    }

    /**
     * Sets the defaulttemplate
     *
     * @param bool $defaulttemplate
     */
    public function setDefaulttemplate($defaulttemplate): void
    {
        $this->defaulttemplate = $defaulttemplate;
    }

    /**
     * Returns the boolean state of defaulttemplate
     *
     * @return bool
     */
    public function isDefaulttemplate(): bool
    {
        return $this->getDefaulttemplate();
    }
}
