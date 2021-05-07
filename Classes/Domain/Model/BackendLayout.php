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

/**
 * This model represents a back-end user.
 */
class BackendLayout extends AbstractEntity
{

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $title = '';

    /**
     * @var int
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title the user name to set, must not be empty
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * Gets the uid.
     *
     * @return int the uid, will not be empty
     */
    public function getUid(): ?int
    {
        return $this->uid;
    }

    /**
     * Sets the uid.
     *
     * @param int $uid the user name to set, must not be empty
     */
    public function setUid($uid): void
    {
        $this->uid = $uid;
    }

    /**
     * Gets the icon.
     *
     * @return string the icon
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Sets the icon.
     *
     * @param string $icon
     */
    public function setIcon($icon): void
    {
        $this->icon = $icon;
    }

    /**
     * Gets the description.
     *
     * @return string description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }
}
