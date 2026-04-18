<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Domain\Repository;

use Maispace\MaiFaq\Domain\Model\Faq;
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
            $query->contains('categories', $categoryUid)
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
            $query->contains('categories', $categoryUid)
        );

        return $query->execute();
    }
}
