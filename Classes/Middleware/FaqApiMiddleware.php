<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Middleware;

use Maispace\MaiBase\Middleware\Api\AbstractApiMiddleware;
use Maispace\MaiFaq\Attribute\Route;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class FaqApiMiddleware extends AbstractApiMiddleware
{
    private const API_PATH = '/api/faq';

    /**
     * Route descriptors – formal definition of all available API endpoints.
     *
     * Each entry maps a path suffix to handler metadata:
     *   - handler:   public method to invoke on this instance
     *   - method:    allowed HTTP method
     *   - description: human-readable description of the endpoint
     *   - parameters:  query / body parameters accepted by the endpoint
     *
     * @var array<string, array{
     *     handler: non-empty-string,
     *     method: non-empty-string,
     *     description: string,
     *     parameters: array<string, string>,
     * }>
     */
    private const ROUTES = [
        '/items' => [
            'handler' => 'handleItems',
            'method' => 'GET',
            'description' => 'Fetch FAQ items, optionally filtered by category and page UIDs, with configurable sort order.',
            'parameters' => [
                'categoryUid' => 'int – filter by category UID (0 = all)',
                'pageUids' => 'string – comma-separated storage page UIDs',
                'sort' => 'string – sort field: sorting|question|uid (default: sorting)',
                'order' => 'string – sort direction: asc|desc (default: asc)',
            ],
        ],
        '/categories' => [
            'handler' => 'handleCategories',
            'method' => 'GET',
            'description' => 'Fetch sys_category rows by their UIDs.',
            'parameters' => [
                'categoryUids' => 'string – comma-separated category UIDs (required)',
            ],
        ],
    ];

    /** @var list<non-empty-string> Whitelist of allowed sort column names */
    private const ALLOWED_SORT_FIELDS = ['sorting', 'question', 'uid'];

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct($responseFactory, $streamFactory);
    }

    // -------------------------------------------------------------------------
    // ApiMiddlewareInterface
    // -------------------------------------------------------------------------

    public function shouldHandle(ServerRequestInterface $request): bool
    {
        return str_starts_with($request->getUri()->getPath(), self::API_PATH);
    }

    /**
     * Route the request to the matching endpoint handler, or return 404
     * when the path suffix is not found in the route descriptors.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $suffix = $this->resolveRouteSuffix($request);

        if ($suffix === null || !isset(self::ROUTES[$suffix])) {
            return $this->errorResponse('Not found', 404);
        }

        $route = self::ROUTES[$suffix];
        $handler = $route['handler'];

        return $this->{$handler}($request);
    }

    // -------------------------------------------------------------------------
    // Endpoint: GET /api/faq/items
    // -------------------------------------------------------------------------

    /**
     * Fetch FAQ items with optional category filter, page scope, and sort order.
     *
     * @param array<string, mixed>|null $queryParams  Optional overrides (used in tests)
     */
    #[Route(
        path: '/api/faq/items',
        method: 'GET',
        description: 'Fetch FAQ items, optionally filtered by category and page UIDs, with configurable sort order.',
        parameters: [
            'categoryUid' => 'int – filter by category UID (0 = all)',
            'pageUids' => 'string – comma-separated storage page UIDs',
            'sort' => 'string – sort field: sorting|question|uid (default: sorting)',
            'order' => 'string – sort direction: asc|desc (default: asc)',
        ],
    )]
    public function handleItems(ServerRequestInterface $request, ?array $queryParams = null): ResponseInterface
    {
        $params = $queryParams ?? $request->getQueryParams();
        $categoryUid = isset($params['categoryUid']) ? (int) $params['categoryUid'] : 0;
        $pageUids = $params['pageUids'] ?? '';
        $sort = $this->resolveSortField($params['sort'] ?? 'sorting');
        $order = $this->resolveSortOrder($params['order'] ?? 'asc');

        $pageUidList = array_filter(
            array_map('intval', explode(',', $pageUids)),
            static fn(int $uid): bool => $uid > 0,
        );

        $qb = $this->connectionPool->getQueryBuilderForTable('tx_maifaq_faq');

        $qb
            ->select('f.uid', 'f.question', 'f.answer', 'f.pid', 'f.sorting')
            ->from('tx_maifaq_faq', 'f')
            ->where(
                $qb->expr()->eq('f.hidden', 0),
                $qb->expr()->eq('f.deleted', 0),
            )
            ->orderBy('f.' . $sort, $order);

        if ($pageUidList !== []) {
            $qb->andWhere(
                $qb->expr()->in('f.pid', $qb->createNamedParameter($pageUidList, Connection::PARAM_INT_ARRAY)),
            );
        }

        if ($categoryUid > 0) {
            $this->addCategoryJoin($qb, $categoryUid);
        }

        $faqRows = $qb->executeQuery()->fetchAllAssociative();

        $items = [];
        foreach ($faqRows as $row) {
            $items[] = [
                'uid' => (int) $row['uid'],
                'question' => $row['question'],
                'answer' => $row['answer'],
            ];
        }

        if ($items !== []) {
            $items = $this->attachCategoryData($items);
        }

        return $this->jsonResponse(['items' => $items]);
    }

    // -------------------------------------------------------------------------
    // Endpoint: GET /api/faq/categories
    // -------------------------------------------------------------------------

    /**
     * Fetch sys_category rows by their UIDs.
     *
     * @param array<string, mixed>|null $queryParams  Optional overrides (used in tests)
     */
    #[Route(
        path: '/api/faq/categories',
        method: 'GET',
        description: 'Fetch sys_category rows by their UIDs.',
        parameters: [
            'categoryUids' => 'string – comma-separated category UIDs (required)',
        ],
    )]
    public function handleCategories(ServerRequestInterface $request, ?array $queryParams = null): ResponseInterface
    {
        $params = $queryParams ?? $request->getQueryParams();
        $categoryUids = $params['categoryUids'] ?? '';

        if (empty($categoryUids)) {
            return $this->jsonResponse(['categories' => []]);
        }

        $uids = array_filter(
            array_map('intval', explode(',', $categoryUids)),
            static fn(int $uid): bool => $uid > 0,
        );

        if ($uids === []) {
            return $this->jsonResponse(['categories' => []]);
        }

        $qb = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $rows = $qb
            ->select('uid', 'title')
            ->from('sys_category')
            ->where(
                $qb->expr()->in('uid', $qb->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $categories = array_map(
            static fn(array $row): array => [
                'uid' => (int) $row['uid'],
                'title' => $row['title'],
            ],
            $rows,
        );

        return $this->jsonResponse(['categories' => $categories]);
    }

    // -------------------------------------------------------------------------
    // Route descriptors introspection
    // -------------------------------------------------------------------------

    /**
     * Returns all route descriptors for external inspection
     * (e.g. documentation, health-check endpoints, integrator tooling).
     *
     * @return array<string, array{
     *     handler: non-empty-string,
     *     method: non-empty-string,
     *     description: string,
     *     parameters: array<string, string>,
     * }>
     */
    public static function getRouteDescriptors(): array
    {
        return self::ROUTES;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Extract the route suffix (everything after the API_PATH prefix).
     *
     * Returns null when the path equals the prefix (no suffix) or
     * does not start with the prefix at all (shouldHandle prevents this).
     */
    private function resolveRouteSuffix(ServerRequestInterface $request): ?string
    {
        $path = $request->getUri()->getPath();

        if (!str_starts_with($path, self::API_PATH)) {
            return null;
        }

        $suffix = substr($path, strlen(self::API_PATH));

        return $suffix !== '' ? $suffix : null;
    }

    /**
     * Validate and normalise the sort field against the whitelist.
     * Falls back to 'sorting' when the provided value is not allowed.
     */
    private function resolveSortField(string $field): string
    {
        $field = strtolower(trim($field));

        return in_array($field, self::ALLOWED_SORT_FIELDS, true) ? $field : 'sorting';
    }

    /**
     * Normalise sort direction to a safe SQL fragment.
     * Falls back to 'ASC' when the value is not 'desc'/'DESC'.
     */
    private function resolveSortOrder(string $order): string
    {
        return strtolower(trim($order)) === 'desc' ? 'DESC' : 'ASC';
    }

    /**
     * Add a JOIN to sys_category_record_mm to filter FAQ items by a single category UID.
     */
    private function addCategoryJoin(QueryBuilder $qb, int $categoryUid): void
    {
        $qb->join(
            'f',
            'sys_category_record_mm',
            'mm',
            (string) $qb->expr()->and(
                $qb->expr()->eq('mm.uid_foreign', 'f.uid'),
                $qb->expr()->eq(
                    'mm.tablenames',
                    $qb->createNamedParameter('tx_maifaq_faq'),
                ),
                $qb->expr()->eq(
                    'mm.fieldname',
                    $qb->createNamedParameter('categories'),
                ),
                $qb->expr()->eq(
                    'mm.uid_local',
                    $qb->createNamedParameter($categoryUid, Connection::PARAM_INT),
                ),
            ),
        );
    }

    /**
     * Attach category data (uid + title) to each FAQ item.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function attachCategoryData(array $items): array
    {
        $faqUids = array_column($items, 'uid');
        $catQb = $this->connectionPool->getQueryBuilderForTable('sys_category_record_mm');

        $catRows = $catQb
            ->select('mm.uid_foreign', 'c.uid', 'c.title')
            ->from('sys_category_record_mm', 'mm')
            ->join(
                'mm',
                'sys_category',
                'c',
                (string) $catQb->expr()->eq('mm.uid_local', 'c.uid'),
            )
            ->where(
                $catQb->expr()->in(
                    'mm.uid_foreign',
                    $catQb->createNamedParameter($faqUids, Connection::PARAM_INT_ARRAY),
                ),
                $catQb->expr()->eq(
                    'mm.tablenames',
                    $catQb->createNamedParameter('tx_maifaq_faq'),
                ),
                $catQb->expr()->eq(
                    'mm.fieldname',
                    $catQb->createNamedParameter('categories'),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $categoryMap = [];
        foreach ($catRows as $catRow) {
            $faqUid = (int) $catRow['uid_foreign'];
            $categoryMap[$faqUid][] = [
                'uid' => (int) $catRow['uid'],
                'title' => $catRow['title'],
            ];
        }

        foreach ($items as $index => $item) {
            $items[$index]['categories'] = $categoryMap[$item['uid']] ?? [];
        }

        return $items;
    }
}
