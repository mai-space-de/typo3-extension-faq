<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FaqListTemplateA11yTest extends TestCase
{
    private string $templatePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templatePath = dirname(__DIR__, 3)
            . '/Resources/Private/Templates/Faq/List.html';
    }

    #[Test]
    public function listTemplateDefinesAriaLiveStatusRegion(): void
    {
        $html = file_get_contents($this->templatePath);
        self::assertNotFalse($html);
        self::assertStringContainsString('data-faq-status="1"', $html);
        self::assertStringContainsString('aria-live="polite"', $html);
        self::assertStringContainsString('aria-atomic="true"', $html);
        self::assertStringContainsString('role="status"', $html);
    }

    #[Test]
    public function listTemplateLinksTabsToTabpanel(): void
    {
        $html = file_get_contents($this->templatePath);
        self::assertNotFalse($html);
        self::assertStringContainsString('id="mai-faq-tabpanel"', $html);
        self::assertStringContainsString('role="tabpanel"', $html);
        self::assertStringContainsString('aria-controls="mai-faq-tabpanel"', $html);
        self::assertStringContainsString('aria-labelledby="mai-faq-tab-all"', $html);
    }

    #[Test]
    public function listTemplateExposesTranslatedAnnouncementMessages(): void
    {
        $html = file_get_contents($this->templatePath);
        self::assertNotFalse($html);
        self::assertStringContainsString('data-faq-msg-results=', $html);
        self::assertStringContainsString("key: 'a11y.resultsShown'", $html);
        self::assertStringContainsString('data-faq-msg-no-results=', $html);
    }
}
