<?php

namespace MASK\Mask\ExpressionLanguage;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\ExpressionLanguage\RequestWrapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

            /** @var RequestWrapper $request */
            $request = $arguments['request'];
            $requestParameters = $request->getQueryParams();


            $uid = null;
            $layout = (string)$param;
            // get current page uid:
            if (is_array($requestParameters['edit']['pages'])) { // after saving page
                $uid = (int)key($requestParameters['edit']['pages']);
            } elseif ($GLOBALS['SOBE']->editconf['pages']) { // after opening pages
                $uid = (int)key($GLOBALS['SOBE']->editconf['pages']);
            } else {
                if (!empty($GLOBALS['_SERVER']['HTTP_REFERER'])) {
                    $url = $GLOBALS['_SERVER']['HTTP_REFERER'];
                    $queryString = parse_url($url, PHP_URL_QUERY);
                    $result = [];
                    parse_str($queryString, $result);
                    if ($result['id']) {
                        $uid = (int)$result['id'];
                    }
                }
            }

            if ($uid) {
                /** @var Connection $connection */
                $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
                $query = $connection->createQueryBuilder();
                /** @var DeletedRestriction $deletedRestriction */
                $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
                $query->getRestrictions()->removeAll()->add($deletedRestriction);
                $data = $query->select(
                    'backend_layout',
                    'backend_layout_next_level'
                )->from('pages')
                    ->where(
                        $query->expr()->eq('uid', $uid)
                    )->execute()
                    ->fetch(FetchMode::ASSOCIATIVE);

                $backend_layout = (string)$data['backend_layout'];
                $backend_layout_next_level = (string)$data['backend_layout_next_level'];

                if (!empty($backend_layout)) { // If backend_layout is set on current page
                    return in_array($backend_layout, [$layout, 'pagets__' . $layout], true);
                }

                if (!empty($backend_layout_next_level)) { // If backend_layout_next_level is set on current page
                    return in_array($backend_layout_next_level, [$layout, 'pagets__' . $layout], true);
                }

                // If backend_layout and backend_layout_next_level is not set on current page, check backend_layout_next_level on rootline
                $sysPage = GeneralUtility::makeInstance(PageRepository::class);
                try {
                    $rootline = $sysPage->getRootLine($uid, '');
                } catch (\Exception $e) {
                    $rootline = [];
                }
                foreach ($rootline as $page) {
                    if (in_array((string)$page['backend_layout_next_level'], [$layout, 'pagets__' . $layout],
                        true)) {
                        return true;
                    }
                }
            }
            return false;
        });
    }

    /**
     * @return ExpressionFunction
     * @noinspection PhpComposerExtensionStubsInspection
     */
    protected function maskContentType(): ExpressionFunction
    {
        $getContentElementType = static function (int $uid): string {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tt_content');
            /** @var DeletedRestriction $deletedRestriction */
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
                $parsedContext = json_decode($parsedBody['ajax']['context'], true, 512, JSON_THROW_ON_ERROR);
                if (isset($parsedContext['config']['overrideChildTca']['columns']['CType']['config']['default'])) {
                    return $parsedContext['config']['overrideChildTca']['columns']['CType']['config']['default'] === 'mask_' . $value;
                }
            }
            return false;
        });
    }
}
