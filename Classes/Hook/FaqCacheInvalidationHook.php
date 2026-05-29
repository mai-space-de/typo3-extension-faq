<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Hook;

use Maispace\MaiBase\Hook\AbstractRecordCacheInvalidationHook;

/**
 * Flushes list page cache tags when an FAQ record is saved or deleted.
 */
final class FaqCacheInvalidationHook extends AbstractRecordCacheInvalidationHook
{
    protected function getWatchedTable(): string
    {
        return 'tx_maifaq_faq';
    }
}
