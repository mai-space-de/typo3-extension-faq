<?php

declare(strict_types=1);

use Maispace\MaiFaq\Middleware\FaqApiMiddleware;

return [
    'frontend' => [
        'maispace/mai-faq/faq-api' => [
            'target' => FaqApiMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
