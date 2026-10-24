<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Configuration;

use Maispace\MaiFaq\Middleware\FaqApiMiddleware;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestMiddlewaresRegistrationTest extends TestCase
{
    #[Test]
    public function faqApiIsRegisteredBeforePageResolver(): void
    {
        $config = require dirname(__DIR__, 3) . '/Configuration/RequestMiddlewares.php';

        self::assertArrayHasKey('frontend', $config);
        self::assertArrayHasKey('maispace/mai-faq/faq-api', $config['frontend']);

        $entry = $config['frontend']['maispace/mai-faq/faq-api'];
        self::assertSame(FaqApiMiddleware::class, $entry['target']);
        self::assertContains('typo3/cms-frontend/site', $entry['after'] ?? []);
        self::assertContains(
            'typo3/cms-frontend/page-resolver',
            $entry['before'] ?? [],
            'FAQ API must run before page-resolver or /api/faq/* becomes an HTML 404.',
        );
    }
}
