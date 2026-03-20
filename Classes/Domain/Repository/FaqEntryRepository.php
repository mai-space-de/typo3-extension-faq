<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Domain\Repository;

use Maispace\MaiFaq\Domain\Model\FaqCategory;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class FaqEntryRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
        'question' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findByCategory(FaqCategory $category): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('category', $category));
        return $query->execute();
    }

    public function findBySearchWord(string $searchWord): QueryResultInterface
    {
        $query = $this->createQuery();
        $searchWord = '%' . $searchWord . '%';
        $query->matching(
            $query->logicalOr(
                $query->like('question', $searchWord),
                $query->like('answer', $searchWord)
            )
        );
        return $query->execute();
    }
}
