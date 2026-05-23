<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Controller;

use Maispace\MaiFaq\Controller\FaqController;
use Maispace\MaiFaq\Domain\Repository\FaqRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class FaqControllerTest extends TestCase
{
    #[Test]
    public function resolveStoragePageUidsReturnsEmptyArrayWhenPagesSettingIsEmpty(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveStoragePageUids($controller, []);

        self::assertSame([], $result);
    }

    #[Test]
    public function resolveStoragePageUidsReturnsEmptyArrayWhenPagesSettingIsMissing(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveStoragePageUids($controller, ['other' => 'value']);

        self::assertSame([], $result);
    }

    #[Test]
    public function resolveStoragePageUidsReturnsArrayOfIntegersFromCommaSeparatedString(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveStoragePageUids($controller, ['pages' => '10,20,30']);

        self::assertSame([10, 20, 30], $result);
    }

    #[Test]
    public function resolveStoragePageUidsFiltersOutZeroAndNegativeValues(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveStoragePageUids($controller, ['pages' => '10,0,-5,20']);

        self::assertSame([10, 20], array_values($result));
    }

    #[Test]
    public function resolveStoragePageUidsHandlesEmptyCommaSegments(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveStoragePageUids($controller, ['pages' => '10,,20,30']);

        self::assertSame([10, 20, 30], array_values($result));
    }

    #[Test]
    public function resolveCategoriesReturnsEmptyArrayWhenCategoryUidsSettingIsEmpty(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveCategories($controller, []);

        self::assertSame([], $result);
    }

    #[Test]
    public function resolveCategoriesReturnsEmptyArrayWhenCategoryUidsSettingIsMissing(): void
    {
        $controller = $this->createController();
        $result = $this->callResolveCategories($controller, ['other' => 'value']);

        self::assertSame([], $result);
    }

    #[Test]
    public function resolveCategoriesReturnsArrayOfCategoryRows(): void
    {
        $connectionPool = $this->createConnectionPoolMock([
            ['uid' => 1, 'title' => 'Category 1'],
            ['uid' => 2, 'title' => 'Category 2'],
        ]);

        $controller = $this->createController($connectionPool);
        $result = $this->callResolveCategories($controller, ['categoryUids' => '1,2']);

        self::assertCount(2, $result);
        self::assertSame(['uid' => 1, 'title' => 'Category 1'], $result[0]);
        self::assertSame(['uid' => 2, 'title' => 'Category 2'], $result[1]);
    }

    #[Test]
    public function resolveCategoriesSkipsNonExistentCategories(): void
    {
        $connectionPool = $this->createConnectionPoolMock([
            ['uid' => 1, 'title' => 'Category 1'],
            false,
        ]);

        $controller = $this->createController($connectionPool);
        $result = $this->callResolveCategories($controller, ['categoryUids' => '1,999']);

        self::assertCount(1, $result);
        self::assertSame(['uid' => 1, 'title' => 'Category 1'], $result[0]);
    }

    #[Test]
    public function resolveCategoriesFiltersOutZeroAndNegativeUids(): void
    {
        $connectionPool = $this->createConnectionPoolMock([
            ['uid' => 10, 'title' => 'Category 10'],
        ]);

        $controller = $this->createController($connectionPool);
        $result = $this->callResolveCategories($controller, ['categoryUids' => '10,0,-5']);

        self::assertCount(1, $result);
        self::assertSame(['uid' => 10, 'title' => 'Category 10'], $result[0]);
    }

    private function createController(?ConnectionPool $connectionPool = null): FaqController
    {
        $faqRepository = $this->createMock(FaqRepository::class);
        $connectionPool = $connectionPool ?? $this->createMock(ConnectionPool::class);

        return new FaqController($faqRepository, $connectionPool);
    }

    private function createConnectionPoolMock(array $fetchResults): ConnectionPool
    {
        $connectionPool = $this->createMock(ConnectionPool::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $statement = $this->createMock(\Doctrine\DBAL\Result::class);

        $queryBuilder->method('expr')->willReturn($expressionBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('createNamedParameter')->willReturnCallback(fn($value) => (string) $value);
        $queryBuilder->method('executeQuery')->willReturn($statement);

        $expressionBuilder->method('eq')->willReturn('1=1');

        $statement->method('fetchAssociative')->willReturnOnConsecutiveCalls(...$fetchResults);

        $connectionPool->method('getQueryBuilderForTable')
            ->with('sys_category')
            ->willReturn($queryBuilder);

        return $connectionPool;
    }

    private function callResolveStoragePageUids(FaqController $controller, array $settings): array
    {
        $this->injectSettings($controller, $settings);

        $reflection = new \ReflectionMethod($controller, 'resolveStoragePageUids');
        return $reflection->invoke($controller);
    }

    private function callResolveCategories(FaqController $controller, array $settings): array
    {
        $this->injectSettings($controller, $settings);

        $reflection = new \ReflectionMethod($controller, 'resolveCategories');
        return $reflection->invoke($controller, $settings);
    }

    private function injectSettings(object $controller, array $settings): void
    {
        $reflection = new \ReflectionProperty($controller, 'settings');
        $reflection->setValue($controller, $settings);
    }
}
