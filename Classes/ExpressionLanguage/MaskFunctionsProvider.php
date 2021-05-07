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

namespace MASK\Mask\ExpressionLanguage;

use MASK\Mask\Utility\AffixUtility;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\ExpressionLanguage\RequestWrapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class MaskFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            $this->maskBeLayout(),
            $this->maskContentType(),
        ];
    }

    /**
     * @return ExpressionFunction
     */
    protected function maskBeLayout(): ExpressionFunction
    {
        return new ExpressionFunction('maskBeLayout', static function ($param) {
        }, static function ($arguments, $param = null) {
            $layout = (string)$param;
            $backend_layout = (string)$arguments['page']['backend_layout'];
            $backend_layout_next_level = (string)$arguments['page']['backend_layout_next_level'];

            // If backend_layout is set on current page
            if (!empty($backend_layout)) {
                return in_array($backend_layout, [$layout, 'pagets__' . $layout], true);
            }

            // If backend_layout_next_level is set on current page
            if (!empty($backend_layout_next_level)) {
                return in_array($backend_layout_next_level, [$layout, 'pagets__' . $layout], true);
            }

            // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
            foreach ($arguments['tree']->rootLine as $page) {
                if (in_array(
                    (string)$page['backend_layout_next_level'],
                    [$layout, 'pagets__' . $layout],
                    true
                )) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * @return ExpressionFunction
     */
    protected function maskContentType(): ExpressionFunction
    {
        $getContentElementType = static function (int $uid): string {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tt_content');
            $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
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
            $uid = null;

            /** @var RequestWrapper $request */
            $request = $arguments['request'];
            $params = $request->getQueryParams();

            // if cType is directly in the params
            if (isset($params['recordTypeValue'])) {
                return $params['recordTypeValue'] === $value;
            }

            // if we have info about content element
            if (isset($params['edit']['tt_content']) &&
                is_array($params['edit']['tt_content'])
            ) {
                $contentType = null;
                // New record, content type (CType) given as request parameter
                if (isset($params['defVals']['tt_content']['CType']) && (string)current($params['edit']['tt_content']) === 'new') {
                    $contentType = (string)$params['defVals']['tt_content']['CType'];
                } else {
                    // Existing record, fetch content type (CType) from database
                    $uid = (int)key($params['edit']['tt_content']);
                    $contentType = $contentTypeMappingCache[$uid] ?? $getContentElementType($uid);
                }
                return $contentType === $value;
            }

            // if content element is loaded via ajax (inline), there are two ways to find the uid of the element
            $parsedBody = $request->getParsedBody();
            if (isset($parsedBody['ajax'][1])) {
                $uid = (int)$parsedBody['ajax'][1];
            }
            if (isset($parsedBody['ajax'][0])) {
                $uidTableString = $parsedBody['ajax'][0];
                $uidTableStringArray = explode('-', $uidTableString);
                $lastPart = array_pop($uidTableStringArray);
                if (MathUtility::canBeInterpretedAsInteger($lastPart)) {
                    $uid = (int)$lastPart;
                // If ajax is loading an inline tt_content record and the default CType is a mask element
                } elseif ($lastPart == 'tt_content' && AffixUtility::hasMaskPrefix(end($uidTableStringArray))) {
                    $context = json_decode($parsedBody['ajax']['context'], true, 512, 4194304);
                    $config = json_decode($context['config'], true, 512, 4194304);
                    if (AffixUtility::hasMaskCTypePrefix($config['overrideChildTca']['columns']['CType']['config']['default'] ?? '')) {
                        return true;
                    }
                } else {
                    return false;
                }
            }

            if ($uid) {
                // fetch content type (CType) from database
                $contentType = $contentTypeMappingCache[$uid] ?? $getContentElementType($uid);
                return $contentType === $value;
            }

            // if we have found nothing, then return that this is not a mask field
            return false;
        });
    }
}
