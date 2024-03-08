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
use TYPO3\CMS\Frontend\Page\PageLayoutResolver;

class TcaTypesShowitemMaskBeLayoutFields implements FormDataProviderInterface
{
    public function __construct(
        protected TableDefinitionCollection $tableDefinitionCollection,
        protected TcaCodeGenerator $tcaCodeGenerator,
        protected PageLayoutResolver $pageLayoutResolver,
    ) {
    }

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
            foreach ($pages->elements as $element) {
                $layout = $element->key;
                $currentLayout = $this->pageLayoutResolver->getLayoutForPage($result['databaseRow'], $result['rootline']);
                if (str_starts_with($currentLayout, 'pagets__') === true) {
                    $currentLayout = substr($currentLayout, 8);
                }
                if ($currentLayout === $layout) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $this->tcaCodeGenerator->getPageShowItem($element->key);
                    $result['processedTca']['palettes'] = array_merge(($result['processedTca']['palettes'] ?? []), $this->tcaCodeGenerator->getPagePalettes($element->key));
                    break;
                }
            }
        }
        return $result;
    }
}
