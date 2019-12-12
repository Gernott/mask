<?php
declare(strict_types=1);

namespace MASK\Mask\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool as ConnectionPoolAlias;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction as DeletedRestrictionAlias;
use TYPO3\CMS\Core\ExpressionLanguage\RequestWrapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MaskContentTypeConditionFunctionsProvider
 *
 * @package MASK\Mask\ExpressionLanguage
 * @author Wolfgang Klinger <wk@plan2.net>
 */
class MaskContentTypeConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            $this->getMaskContentElementFunction()
        ];
    }

    /**
     * @return ExpressionFunction
     */
    protected function getMaskContentElementFunction(): ExpressionFunction
    {
        $getContentElementType = static function (int $uid): string {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPoolAlias::class)
                ->getQueryBuilderForTable('tt_content');
            /** @var DeletedRestriction $deletedRestriction */
            $deletedRestriction = GeneralUtility::makeInstance(DeletedRestrictionAlias::class);
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add($deletedRestriction);

            return (string)$queryBuilder->select('CType')
                ->from('tt_content')
                ->where($queryBuilder->expr()->eq('uid', $uid))
                ->execute()
                ->fetchColumn();
        };

        return new ExpressionFunction('isMaskContentType', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($arguments, $value) use ($getContentElementType) {
            static $contentTypeMappingCache = [];

            /** @var RequestWrapper $request */
            $request = $arguments['request'];
            $requestParameters = $request->getQueryParams();
            if (isset($requestParameters['edit']['tt_content']) &&
                is_array($requestParameters['edit']['tt_content'])
            ) {
                $formType = (string)current($requestParameters['edit']['tt_content']);
                $contentType = null;
                // New record, content type (CType) given as request parameter
                if ($formType === 'new' && isset($requestParameters['defVals']['tt_content']['CType'])) {
                    $contentType = (string)$requestParameters['defVals']['tt_content']['CType'];
                } else {
                    // Existing record, fetch content type (CType) from database
                    $uid = (int)key($requestParameters['edit']['tt_content']);
                    $contentType = $contentTypeMappingCache[$uid] ?? $getContentElementType($uid);
                }

                return $contentType === 'mask_' . $value;
            }

            // Content element is loaded via ajax (inline)
            $parsedBody = $request->getParsedBody();
            if (isset($parsedBody['ajax']['context'])) {
                $parsedContext = json_decode($parsedBody['ajax']['context'], true);
                if (isset($parsedContext['config']['overrideChildTca']['columns']['CType']['config']['default'])) {
                    return $parsedContext['config']['overrideChildTca']['columns']['CType']['config']['default'] === 'mask_' . $value;
                }
            }

            return false;
        });
    }
}
