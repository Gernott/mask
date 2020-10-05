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

namespace MASK\Mask\Domain\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Provides the settings users can make
 */
class SettingsService
{

    /**
     * Contains the settings of the current extension
     *
     * @var array
     * @api
     */
    protected $settings = [];

    /**
     * Contains the settings of the $_EXTCONF
     *
     * @var array
     */
    protected $extSettings;

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->extSettings = $extensionConfiguration->get('mask');
    }

    /**
     * Returns the settings
     * @return array
     */
    public function get(): array
    {
        return $this->extSettings;
    }
}
