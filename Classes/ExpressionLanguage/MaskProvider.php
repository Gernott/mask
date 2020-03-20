<?php

namespace MASK\Mask\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\ExpressionLanguage\FunctionsProvider\Typo3ConditionFunctionsProvider;

class MaskProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            Typo3ConditionFunctionsProvider::class,
            MaskFunctionsProvider::class
        ];
    }
}
