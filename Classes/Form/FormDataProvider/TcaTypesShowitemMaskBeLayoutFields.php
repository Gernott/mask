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
use MASK\Mask\Domain\Repository\StorageRepository;
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
     * @var StorageRepository
     */
    protected $storageRepository;

    public function __construct(StorageRepository $storageRepository, TcaCodeGenerator $tcaCodeGenerator)
    {
        $this->tcaCodeGenerator = $tcaCodeGenerator;
        $this->storageRepository = $storageRepository;
    }

    public function addData(array $result)
    {
        if ($result['tableName'] != 'pages') {
            return $result;
        }
        $json = $this->storageRepository->load();
        if ($json['pages']['elements'] ?? false) {
            $conditionMatcher = GeneralUtility::makeInstance(ConditionMatcher::class, null, $result['vanillaUid'], $result['rootline']);
            foreach ($json['pages']['elements'] as $element) {
                $key = (string)$element['key'];
                if ($conditionMatcher->match("[maskBeLayout('$key')]")) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $this->tcaCodeGenerator->getPageTca($key);
                    $result['processedTca']['palettes'] = array_merge(($result['processedTca']['palettes'] ?? []), $this->tcaCodeGenerator->getPagePalettes($key));
                    break;
                }
            }
        }
        return $result;
    }
}
