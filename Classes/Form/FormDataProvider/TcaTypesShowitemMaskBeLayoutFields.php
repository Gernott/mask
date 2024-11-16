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

namespace MASK\Mask\Form\FormDataProvider;

use MASK\Mask\CodeGenerator\TcaCodeGenerator;
use MASK\Mask\Definition\TableDefinitionCollection;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class TcaTypesShowitemMaskBeLayoutFields implements FormDataProviderInterface
{
    public function __construct(
        protected TableDefinitionCollection $tableDefinitionCollection,
        protected TcaCodeGenerator $tcaCodeGenerator,
    ) {}

    public function addData(array $result)
    {
        if ($result['tableName'] !== 'pages') {
            return $result;
        }
        if (!$this->tableDefinitionCollection->hasTable('pages')) {
            return $result;
        }
        $pages = $this->tableDefinitionCollection->getTable('pages');
        if (!empty($pages->elements)) {
            $rootline = BackendUtility::BEgetRootLine($result['databaseRow']['uid'], '', true);
            $layoutIdentifier = $this->getLayoutIdentifierForPage($result['databaseRow'], $rootline);
            if ($layoutIdentifier === 'default') {
                return $result;
            }
            if (str_starts_with($layoutIdentifier, 'pagets__') === true) {
                $layoutIdentifier = substr($layoutIdentifier, 8);
            }
            foreach ($pages->elements as $element) {
                $layout = $element->key;
                if ($layoutIdentifier === $layout) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $this->tcaCodeGenerator->getPageShowItem($element->key);
                    $result['processedTca']['palettes'] = array_merge(($result['processedTca']['palettes'] ?? []), $this->tcaCodeGenerator->getPagePalettes($element->key));
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param array{backend_layout?: string} $page
     * @param array<array{backend_layout_next_level?: string}> $rootLine
     * @todo Copied from Core PageLayoutResolver to work independently in v12 and v13.
     */
    protected function getLayoutIdentifierForPage(array $page, array $rootLine): string
    {
        $selectedLayout = $page['backend_layout'] ?? '';

        // If it is set to "none" - don't use any
        if ($selectedLayout === '-1') {
            return 'none';
        }

        if ($selectedLayout === '' || $selectedLayout === '0') {
            // If it not set check the root-line for a layout on next level and use this
            // Remove first element, which is the current page
            // See also \TYPO3\CMS\Backend\View\BackendLayoutView::getSelectedCombinedIdentifier()
            array_shift($rootLine);
            foreach ($rootLine as $rootLinePage) {
                $selectedLayout = (string)($rootLinePage['backend_layout_next_level'] ?? '');
                // If layout for "next level" is set to "none" - don't use any and stop searching
                if ($selectedLayout === '-1') {
                    $selectedLayout = 'none';
                    break;
                }
                if ($selectedLayout !== '' && $selectedLayout !== '0') {
                    // Stop searching if a layout for "next level" is set
                    break;
                }
            }
        }
        if ($selectedLayout === '0' || $selectedLayout === '') {
            $selectedLayout = 'default';
        }
        return $selectedLayout;
    }
}
