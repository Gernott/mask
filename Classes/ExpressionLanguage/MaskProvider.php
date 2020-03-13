<?php

namespace MASK\Mask\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\FunctionsProvider\Typo3ConditionFunctionsProvider;

class MaskProvider extends \TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            // 'foo' => 1,
            // 'bar' => 2,
        ];
        $this->expressionLanguageProviders = [
            // We use the existing Typo3ConditionsFunctions...
            Typo3ConditionFunctionsProvider::class,
            // ... and our custom function provider
            MaskFunctionsProvider::class
        ];
    }
}
