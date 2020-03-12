<?php
declare(strict_types=1);

namespace MASK\Mask\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\ExpressionLanguage\RequestWrapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MaskContentType
 *
 * @package MASK\Mask\ExpressionLanguage
 * @author Wolfgang Klinger <wk@plan2.net>
 */
class MaskContentTypeConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageVariables = [
            'request' => GeneralUtility::makeInstance(RequestWrapper::class, $GLOBALS['TYPO3_REQUEST'] ?? null),
        ];
        $this->expressionLanguageProviders[] = MaskContentTypeConditionFunctionsProvider::class;
    }
}
