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

namespace MASK\Mask\Imaging\IconProvider;

use MASK\Mask\Imaging\PreviewIconResolver;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;

class ContentElementIconProvider implements IconProviderInterface
{
    /**
     * @var PreviewIconResolver
     */
    protected $previewIconResolver;

    public function __construct(PreviewIconResolver $previewIconResolver)
    {
        $this->previewIconResolver = $previewIconResolver;
    }

    public function prepareIconMarkup(Icon $icon, array $options = []): void
    {
        if (empty($options['key'])) {
            throw new \InvalidArgumentException('The option "key" is required and must not be empty', 1440754978);
        }
        $icon->setMarkup($this->generateMarkup($options));
    }

    /**
     * Renders the actual icon
     */
    protected function generateMarkup(array $options): string
    {
        $styles = [];
        $previewIconAvailable = $this->previewIconResolver->isPreviewIconAvailable($options['key']);
        $fontAwesomeKeyAvailable = trim($options['icon']) !== '';

        // decide what kind of icon to render
        $color = $this->getColor($options['color']);
        if ($fontAwesomeKeyAvailable && !$previewIconAvailable) {
            if ($color !== '') {
                $styles[] = 'color: #' . $color;
            }

            if (empty($styles)) {
                return '<span class="icon-unify" ><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($options['color'])) . '"></i></span>';
            }
            return '<span class="icon-unify" style="' . implode('; ', $styles) . '"><i class="fa fa-' . htmlspecialchars($this->getFontAwesomeKey($options['icon'])) . '"></i></span>';
        }

        if ($previewIconAvailable) {
            return '<img src="' . str_replace(Environment::getPublicPath(), '', $this->previewIconResolver->getPreviewIconPath($options['key'])) . '" alt="' . $options['label'] . '" title="' . $options['label'] . '"/>';
        }

        if ($color !== '') {
            $styles[] = 'background-color: #' . $color;
        }
        $styles[] = 'color: #fff';

        return '<span class="icon-unify mask-default-icon" style="' . implode('; ', $styles) . '">' . mb_substr($options['label'], 0, 1) . '</span>';
    }

    /**
     * returns trimmed and unified font-awesome key
     */
    protected function getFontAwesomeKey(string $icon): string
    {
        return trim(str_replace('fa-', '', $icon));
    }

    /**
     * returns trimmed and unified hex-code
     */
    protected function getColor(string $color): string
    {
        return trim(str_replace('#', '', $color));
    }
}
