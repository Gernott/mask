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
use TYPO3\CMS\Backend\Configuration\TypoScript\ConditionMatching\ConditionMatcher;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaTypesShowitemMaskBeLayoutFields implements FormDataProviderInterface
{
    /**
     * @var TcaCodeGenerator
     */
    protected $tcaCodeGenerator;

    /**
     * @var TableDefinitionCollection
     */
    protected $tableDefinitionCollection;

    public function __construct(TableDefinitionCollection $tableDefinitionCollection, TcaCodeGenerator $tcaCodeGenerator)
    {
        $this->tcaCodeGenerator = $tcaCodeGenerator;
        $this->tableDefinitionCollection = $tableDefinitionCollection;
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
            $conditionMatcher = GeneralUtility::makeInstance(ConditionMatcher::class, null, $result['vanillaUid'], $result['rootline']);
            foreach ($pages->elements as $element) {
                if ($conditionMatcher->match("[maskBeLayout('$element->key')]")) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $this->tcaCodeGenerator->getPageShowItem($element->key);
                    $result['processedTca']['palettes'] = array_merge(($result['processedTca']['palettes'] ?? []), $this->tcaCodeGenerator->getPagePalettes($element->key));
                    break;
                }
            }
        }
        return $result;
    }
}
