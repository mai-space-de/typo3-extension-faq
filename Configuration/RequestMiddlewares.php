<?php

declare(strict_types=1);

use Maispace\MaiFaq\Middleware\FaqApiMiddleware;

return [
    'frontend' => [
        // Same trap as consent/search: 'after: site' alone can place this after
        // page-resolver, which 404s /api/faq/* before the middleware runs.
        'maispace/mai-faq/faq-api' => [
            'target' => FaqApiMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
