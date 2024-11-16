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

namespace MASK\Mask\EventListeners;

use MASK\Mask\Enumeration\FieldType;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RegisterIcons
{
    public function __construct(
        protected IconRegistry $iconRegistry
    ) {}

    public function __invoke(BootCompletedEvent $event): void
    {
        // Register Icons needed in the backend module
        foreach (FieldType::cases() as $maskIcon) {
            $this->iconRegistry->registerIcon(
                'mask-fieldtype-' . $maskIcon->value,
                SvgIconProvider::class,
                ['source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . GeneralUtility::underscoredToUpperCamelCase($maskIcon->value) . '.svg']
            );
        }
    }
}
