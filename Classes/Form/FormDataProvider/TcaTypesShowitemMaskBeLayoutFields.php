<?php

declare(strict_types=1);

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
        $json = $this->storageRepository->load();
        if ($json['pages']['elements'] ?? false) {
            $conditionMatcher = GeneralUtility::makeInstance(ConditionMatcher::class, null, $result['vanillaUid'], $result['rootline']);
            foreach ($json['pages']['elements'] as $element) {
                $key = $element['key'];
                if ($conditionMatcher->match("[maskBeLayout('$key')]")) {
                    $result['processedTca']['types'][$result['recordTypeValue']]['showitem'] .= $this->tcaCodeGenerator->getPageTca($key);
                    break;
                }
            }
        }
        return $result;
    }
}
