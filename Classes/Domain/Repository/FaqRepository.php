<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Domain\Repository;

use Maispace\MaiFaq\Domain\Model\Faq;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class FaqRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findByCategoryUid(int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->contains('categories', $categoryUid),
        );

        return $query->execute();
    }

    public function findFromPages(array $pageUids): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);

        return $query->execute();
    }

    public function findFromPagesByCategoryUid(array $pageUids, int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);
        $query->matching(
            $query->contains('categories', $categoryUid),
        );

        return $query->execute();
    }

    public function createQueryBuilderForPagination(array $pageUids = [], int $categoryUid = 0): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_maifaq_faq');

        $queryBuilder
            ->select('*')
            ->from('tx_maifaq_faq')
            ->orderBy('sorting', 'ASC');

        if ($pageUids !== []) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->in(
                        'pid',
                        $queryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY)
                    )
                );
        }

        if ($categoryUid > 0) {
            $queryBuilder
                ->leftJoin(
                    'tx_maifaq_faq',
                    'sys_category_record_mm',
                    'mm',
                    $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->quoteIdentifier('tx_maifaq_faq.uid'))
                )
                ->andWhere(
                    $queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->createNamedParameter($categoryUid, \PDO::PARAM_INT))
                )
                ->andWhere(
                    $queryBuilder->expr()->eq('mm.tablenames', $queryBuilder->createNamedParameter('tx_maifaq_faq'))
                )
                ->andWhere(
                    $queryBuilder->expr()->eq('mm.fieldname', $queryBuilder->createNamedParameter('categories'))
                );
        }

        return $queryBuilder;
    }
}
