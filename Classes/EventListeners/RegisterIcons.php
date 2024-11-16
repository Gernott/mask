<?php

declare(strict_types=1);

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
        foreach (FieldType::getConstants() as $maskIcon) {
            $this->iconRegistry->registerIcon(
                'mask-fieldtype-' . $maskIcon,
                SvgIconProvider::class,
                ['source' => 'EXT:mask/Resources/Public/Icons/Fieldtypes/' . GeneralUtility::underscoredToUpperCamelCase($maskIcon) . '.svg']
            );
        }
    }
}
