<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Attribute;

/**
 * Route attribute for self-documenting API endpoint handler methods.
 *
 * This attribute provides metadata about API routes at the method level
 * for documentation and introspection purposes. The actual routing logic
 * remains in the ROUTES constant of FaqApiMiddleware.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * @param non-empty-string      $path        The route path (e.g., '/api/faq/items')
     * @param non-empty-string      $method      HTTP method (e.g., 'GET', 'POST')
     * @param string                $description Human-readable description of the endpoint
     * @param array<string, string> $parameters  Query/body parameters with descriptions
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly string $description = '',
        public readonly array $parameters = [],
    ) {}
}
