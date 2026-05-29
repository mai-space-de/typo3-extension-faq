<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Hook;

use Maispace\MaiFaq\Hook\FaqCacheInvalidationHook;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Extbase\Service\CacheService;

final class FaqCacheInvalidationHookTest extends TestCase
{
    private CacheService&MockObject $cacheService;

    private DataHandler&MockObject $dataHandler;

    protected function setUp(): void
    {
        $this->cacheService = $this->createMock(CacheService::class);
        $this->dataHandler = $this->createMock(DataHandler::class);
    }

    #[Test]
    public function faqRecordUpdateFlushesExtbaseCacheTagsTest(): void
    {
        $this->cacheService->expects(self::once())->method('clearCacheForRecord')->with('tx_maifaq_faq', 15);
        $this->cacheService->expects(self::once())->method('clearCachesOfRegisteredPageIds');

        (new FaqCacheInvalidationHook($this->cacheService))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_maifaq_faq',
            15,
            ['question' => 'Updated question'],
            $this->dataHandler,
        );
    }
}
