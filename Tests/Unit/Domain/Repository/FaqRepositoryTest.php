<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Domain\Repository;

use Maispace\MaiFaq\Domain\Repository\FaqRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class FaqRepositoryTest extends TestCase
{
    #[Test]
    public function repositoryExtendsTYPO3BaseRepository(): void
    {
        self::assertTrue(
            is_subclass_of(FaqRepository::class, Repository::class),
            FaqRepository::class . ' must extend ' . Repository::class,
        );
    }

    #[Test]
    public function defaultOrderingsSortBySortingAscending(): void
    {
        $reflection = new \ReflectionClass(FaqRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertArrayHasKey('defaultOrderings', $defaults);
        self::assertIsArray($defaults['defaultOrderings']);
        self::assertArrayHasKey('sorting', $defaults['defaultOrderings']);
        self::assertSame(QueryInterface::ORDER_ASCENDING, $defaults['defaultOrderings']['sorting']);
    }

    #[Test]
    public function defaultOrderingsContainExactlyOneSortKey(): void
    {
        $reflection = new \ReflectionClass(FaqRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertCount(1, $defaults['defaultOrderings']);
    }
}
