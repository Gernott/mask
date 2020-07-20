<?php

declare(strict_types=1);

namespace MASK\Mask\Form\FormDataProvider;

use MASK\Mask\CodeGenerator\TcaCodeGenerator;
use TYPO3\CMS\Backend\Configuration\TypoScript\ConditionMatching\ConditionMatcher;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaTypesShowitemMaskBeLayoutFields implements FormDataProviderInterface
{
    public function addData(array $result)
    {
        $tcaCodeGenerator = GeneralUtility::makeInstance(TcaCodeGenerator::class);
        $json = $tcaCodeGenerator->getStorageRepository()->load();
        if ($json['pages']['elements'] ?? false) {
            $conditionMatcher = GeneralUtility::makeInstance(ConditionMatcher::class, null, $result['vanillaUid'], $result['rootline']);
            foreach ($json['pages']['elements'] as $element) {
                $key = $element['key'];
                if ($conditionMatcher->match("[maskBeLayout($key)]")) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $tcaCodeGenerator->getPageTca($key);
                    break;
                }
            }
        }
        return $result;
    }
}
