<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Middleware;

use Doctrine\DBAL\Result;
use Maispace\MaiFaq\Middleware\FaqApiMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class FaqApiMiddlewareTest extends TestCase
{
    private FaqApiMiddleware $subject;
    private ConnectionPool&\PHPUnit\Framework\MockObject\MockObject $connectionPool;
    private QueryBuilder&\PHPUnit\Framework\MockObject\MockObject $faqQueryBuilder;
    private QueryBuilder&\PHPUnit\Framework\MockObject\MockObject $categoryMmQueryBuilder;
    private QueryBuilder&\PHPUnit\Framework\MockObject\MockObject $sysCategoryQueryBuilder;
    private ExpressionBuilder&\PHPUnit\Framework\MockObject\MockObject $expressionBuilder;
    private ?string $capturedJson = null;

    /** @var array{0: string, 1: string}|null Captured orderBy(field, direction) arguments */
    private ?array $capturedOrderBy = null;

    protected function setUp(): void
    {
        $this->capturedJson = null;
        $this->capturedOrderBy = null;

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->method('createResponse')->willReturnCallback(
            function (int $code = 200): ResponseInterface {
                $response = $this->createMock(ResponseInterface::class);
                $response->method('getStatusCode')->willReturn($code);
                $response->method('withHeader')->willReturnSelf();
                $response->method('withBody')->willReturnSelf();
                $response->method('getBody')->willReturnCallback(
                    function (): StreamInterface {
                        $stream = $this->createMock(StreamInterface::class);
                        $stream->method('__toString')->willReturn($this->capturedJson ?? '');
                        return $stream;
                    },
                );
                return $response;
            },
        );

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->method('createStream')->willReturnCallback(
            function (string $content): StreamInterface {
                $this->capturedJson = $content;
                $stream = $this->createMock(StreamInterface::class);
                $stream->method('__toString')->willReturn($content);
                return $stream;
            },
        );

        $this->expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $this->expressionBuilder->method('eq')->willReturnCallback(
            fn(string $a, mixed $b): string => $a . '=' . $b,
        );
        $this->expressionBuilder->method('and')->willReturnCallback(
            fn(string ...$parts): CompositeExpression => new CompositeExpression('AND', $parts),
        );
        $this->expressionBuilder->method('in')->willReturnCallback(
            fn(string $field, string $value): string => $field . ' IN (' . $value . ')',
        );

        $this->faqQueryBuilder = $this->createQueryBuilderMock();
        $this->categoryMmQueryBuilder = $this->createQueryBuilderMock();
        $this->sysCategoryQueryBuilder = $this->createQueryBuilderMock();

        $this->connectionPool = $this->createMock(ConnectionPool::class);
        $this->connectionPool
            ->method('getQueryBuilderForTable')
            ->willReturnMap([
                ['tx_maifaq_faq', $this->faqQueryBuilder],
                ['sys_category_record_mm', $this->categoryMmQueryBuilder],
                ['sys_category', $this->sysCategoryQueryBuilder],
            ]);

        $this->subject = new FaqApiMiddleware($responseFactory, $streamFactory, $this->connectionPool);
    }

    private function createUriMock(string $path): UriInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);
        return $uri;
    }

    private function decodeResponse(): array
    {
        $result = json_decode($this->capturedJson ?? '', true);
        return is_array($result) ? $result : [];
    }

    // -------------------------------------------------------------------------
    // shouldHandle
    // -------------------------------------------------------------------------

    #[Test]
    public function shouldHandleReturnsTrueForApiFaqPath(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUriMock('/api/faq/items'));

        self::assertTrue($this->subject->shouldHandle($request));
    }

    #[Test]
    public function shouldHandleReturnsFalseForNonMatchingPath(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUriMock('/some-other-path'));

        self::assertFalse($this->subject->shouldHandle($request));
    }

    // -------------------------------------------------------------------------
    // handle / routing
    // -------------------------------------------------------------------------

    #[Test]
    public function handleReturnsItemsResponseForItemsPath(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUriMock('/api/faq/items'));

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $response = $this->subject->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function handleReturnsCategoriesResponseForCategoriesPath(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUriMock('/api/faq/categories'));
        $request->method('getQueryParams')->willReturn(['categoryUids' => '1,2']);

        $this->configureSysCategoryResult([
            ['uid' => 1, 'title' => 'Cat 1'],
            ['uid' => 2, 'title' => 'Cat 2'],
        ]);

        $response = $this->subject->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function handleReturns404ForUnknownPath(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUriMock('/api/faq/unknown'));

        $response = $this->subject->handle($request);

        self::assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // handleItems
    // -------------------------------------------------------------------------

    #[Test]
    public function handleItemsReturnsAllItemsWhenNoCategoryFilter(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([
            ['uid' => 1, 'question' => 'Q1', 'answer' => '<p>A1</p>', 'pid' => 10, 'sorting' => 1],
            ['uid' => 2, 'question' => 'Q2', 'answer' => '<p>A2</p>', 'pid' => 10, 'sorting' => 2],
        ]);

        $this->configureCategoryMmResult([
            ['uid_foreign' => 1, 'uid' => 5, 'title' => 'Cat5'],
        ]);

        $response = $this->subject->handleItems($request, ['categoryUid' => 0, 'pageUids' => '']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $body['items']);
        self::assertSame('Q1', $body['items'][0]['question']);
        self::assertSame('Q2', $body['items'][1]['question']);
        self::assertCount(1, $body['items'][0]['categories']);
        self::assertSame(5, $body['items'][0]['categories'][0]['uid']);
    }

    #[Test]
    public function handleItemsFiltersByCategory(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([
            ['uid' => 1, 'question' => 'Q1', 'answer' => '<p>A1</p>', 'pid' => 10, 'sorting' => 1],
        ]);

        $this->configureCategoryMmResult([
            ['uid_foreign' => 1, 'uid' => 5, 'title' => 'Cat5'],
        ]);

        $response = $this->subject->handleItems($request, ['categoryUid' => 5, 'pageUids' => '']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $body['items']);
        self::assertSame(5, $body['items'][0]['categories'][0]['uid']);
    }

    #[Test]
    public function handleItemsReturnsEmptyArrayWhenNoItems(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);

        $response = $this->subject->handleItems($request, ['categoryUid' => 0, 'pageUids' => '']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $body['items']);
    }

    // -------------------------------------------------------------------------
    // handleCategories
    // -------------------------------------------------------------------------

    #[Test]
    public function handleCategoriesReturnsCategoriesForGivenUids(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureSysCategoryResult([
            ['uid' => 1, 'title' => 'Category A'],
            ['uid' => 2, 'title' => 'Category B'],
        ]);

        $response = $this->subject->handleCategories($request, ['categoryUids' => '1,2']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $body['categories']);
        self::assertSame('Category A', $body['categories'][0]['title']);
        self::assertSame('Category B', $body['categories'][1]['title']);
    }

    #[Test]
    public function handleCategoriesReturnsEmptyArrayForEmptyUids(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->subject->handleCategories($request, ['categoryUids' => '']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $body['categories']);
    }

    #[Test]
    public function handleCategoriesFiltersOutInvalidUids(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureSysCategoryResult([
            ['uid' => 10, 'title' => 'Category 10'],
        ]);

        $response = $this->subject->handleCategories($request, ['categoryUids' => '10,0,-5']);
        $body = $this->decodeResponse();

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(1, $body['categories']);
        self::assertSame(10, $body['categories'][0]['uid']);
    }

    // -------------------------------------------------------------------------
    // getRouteDescriptors
    // -------------------------------------------------------------------------

    #[Test]
    public function getRouteDescriptorsReturnsBothEndpoints(): void
    {
        $descriptors = FaqApiMiddleware::getRouteDescriptors();

        self::assertArrayHasKey('/items', $descriptors);
        self::assertArrayHasKey('/categories', $descriptors);
    }

    #[Test]
    public function routeDescriptorForItemsHasRequiredKeys(): void
    {
        $descriptors = FaqApiMiddleware::getRouteDescriptors();
        $items = $descriptors['/items'];

        self::assertSame('handleItems', $items['handler']);
        self::assertSame('GET', $items['method']);
        self::assertNotEmpty($items['description']);
        self::assertArrayHasKey('categoryUid', $items['parameters']);
        self::assertArrayHasKey('sort', $items['parameters']);
        self::assertArrayHasKey('order', $items['parameters']);
    }

    #[Test]
    public function routeDescriptorForCategoriesHasRequiredKeys(): void
    {
        $descriptors = FaqApiMiddleware::getRouteDescriptors();
        $categories = $descriptors['/categories'];

        self::assertSame('handleCategories', $categories['handler']);
        self::assertSame('GET', $categories['method']);
        self::assertNotEmpty($categories['description']);
        self::assertArrayHasKey('categoryUids', $categories['parameters']);
    }

    // -------------------------------------------------------------------------
    // handleItems: sort / order
    // -------------------------------------------------------------------------

    #[Test]
    public function handleItemsUsesDefaultSortingAscWhenNoSortParams(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, ['categoryUid' => 0, 'pageUids' => '']);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.sorting', $this->capturedOrderBy[0]);
        self::assertSame('ASC', $this->capturedOrderBy[1]);
    }

    #[Test]
    public function handleItemsSortsByQuestionAsc(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, [
            'categoryUid' => 0,
            'pageUids' => '',
            'sort' => 'question',
            'order' => 'asc',
        ]);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.question', $this->capturedOrderBy[0]);
        self::assertSame('ASC', $this->capturedOrderBy[1]);
    }

    #[Test]
    public function handleItemsSortsByQuestionDesc(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, [
            'categoryUid' => 0,
            'pageUids' => '',
            'sort' => 'question',
            'order' => 'desc',
        ]);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.question', $this->capturedOrderBy[0]);
        self::assertSame('DESC', $this->capturedOrderBy[1]);
    }

    #[Test]
    public function handleItemsSortsByUidDesc(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, [
            'categoryUid' => 0,
            'pageUids' => '',
            'sort' => 'uid',
            'order' => 'DESC',
        ]);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.uid', $this->capturedOrderBy[0]);
        self::assertSame('DESC', $this->capturedOrderBy[1]);
    }

    #[Test]
    public function handleItemsFallsBackToSortingForInvalidSortField(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, [
            'categoryUid' => 0,
            'pageUids' => '',
            'sort' => 'invalid_field_name',
            'order' => 'asc',
        ]);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.sorting', $this->capturedOrderBy[0]);
        self::assertSame('ASC', $this->capturedOrderBy[1]);
    }

    #[Test]
    public function handleItemsFallsBackToAscForInvalidSortOrder(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->configureFaqResult([]);
        $this->configureCategoryMmResult([]);

        $this->subject->handleItems($request, [
            'categoryUid' => 0,
            'pageUids' => '',
            'sort' => 'question',
            'order' => 'invalid',
        ]);

        self::assertNotNull($this->capturedOrderBy);
        self::assertSame('f.question', $this->capturedOrderBy[0]);
        self::assertSame('ASC', $this->capturedOrderBy[1]);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function configureFaqResult(array $data): void
    {
        $this->faqQueryBuilder
            ->method('executeQuery')
            ->willReturn($this->createResultMock($data));
    }

    private function configureCategoryMmResult(array $data): void
    {
        $this->categoryMmQueryBuilder
            ->method('executeQuery')
            ->willReturn($this->createResultMock($data));
    }

    private function configureSysCategoryResult(array $data): void
    {
        $this->sysCategoryQueryBuilder
            ->method('executeQuery')
            ->willReturn($this->createResultMock($data));
    }

    /**
     * @return QueryBuilder&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQueryBuilderMock(): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('expr')->willReturn($this->expressionBuilder);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('join')->willReturnSelf();
        $qb->method('orderBy')->willReturnCallback(
            function (string $field, string $direction): QueryBuilder {
                $this->capturedOrderBy = [$field, $direction];
                return $this->faqQueryBuilder;
            },
        );
        $qb->method('createNamedParameter')->willReturnCallback(
            function (mixed $value, mixed $type = null): string {
                if (is_array($value)) {
                    return implode(',', array_map(static fn(mixed $v): string => (string) $v, $value));
                }
                return (string) $value;
            },
        );

        return $qb;
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return Result&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createResultMock(array $data): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($data);
        return $result;
    }
}
