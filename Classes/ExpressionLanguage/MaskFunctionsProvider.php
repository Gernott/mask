<?php

namespace MASK\Mask\ExpressionLanguage;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
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

    protected function maskBeLayout(): ExpressionFunction
    {
        return new ExpressionFunction('maskBeLayout', static function ($param) {
            // Not implemented, we only use the evaluator
        }, static function ($arguments, $param = null) {
            $uid = null;
            $layout = $param;
            // get current page uid:
            if (is_array($_REQUEST['data']['pages'])) { // after saving page
                $uid = (int)key($_REQUEST['data']['pages']);
            } elseif ($GLOBALS['SOBE']->editconf['pages']) { // after opening pages
                $uid = (int)key($GLOBALS['SOBE']->editconf['pages']);
            } else {
                if ($GLOBALS['_SERVER']['HTTP_REFERER'] !== '') {
                    $url = $GLOBALS['_SERVER']['HTTP_REFERER'];
                    $queryString = parse_url($url, PHP_URL_QUERY);
                    $result = array();
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

                $backend_layout = $data['backend_layout'];
                $backend_layout_next_level = $data['backend_layout_next_level'];

                if ($backend_layout !== '') { // If backend_layout is set on current page
                    return in_array($backend_layout, [$layout, 'pagets__' . $layout], true);
                }

                if ($backend_layout_next_level !== '') { // If backend_layout_next_level is set on current page
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
                    if (in_array($page['backend_layout_next_level'], [$layout, 'pagets__' . $layout], true)) {
                        return true;
                    }
                }
            }
            return false;
        });
    }

    protected function maskContentType(): ExpressionFunction
    {
        return new ExpressionFunction('maskContentType', static function ($param) {
            // Not implemented, we only use the evaluator
        }, static function ($arguments, $param) {
            static $cTypeCache = [];
            if (isset($_REQUEST['edit']['tt_content']) && is_array($_REQUEST['edit']['tt_content'])) {
                $field = explode('|', $param);
                $request = $_REQUEST;
                $first = array_shift($request['edit']['tt_content']);

                if ($first === 'new') { // if new element
                    return $_REQUEST['defVals']['tt_content']['CType'] === $field[1];
                }
                // if element exists
                $uid = (int)key($_REQUEST['edit']['tt_content']);

                if (!isset($cTypeCache[$uid])) {
                    /** @var ConnectionPool $connection */
                    $connection = GeneralUtility::makeInstance(ConnectionPool::class);
                    $queryBuilder = $connection->getQueryBuilderForTable('tt_content');
                    /** @var DeletedRestriction $deletedRestriction */
                    $deletedRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
                    $queryBuilder->getRestrictions()->removeAll()->add($deletedRestriction);
                    $cTypeCache[$uid] = $queryBuilder->select($field[0])->from('tt_content')->where($queryBuilder->expr()->eq('uid',
                        $uid))->execute()->fetchColumn(0);
                }
                return (string)$cTypeCache[$uid] === (string)$field[1];
            }
            // if content element is loaded by ajax, then it's ok
            return is_array($_REQUEST['ajax']);
        });
    }
}
