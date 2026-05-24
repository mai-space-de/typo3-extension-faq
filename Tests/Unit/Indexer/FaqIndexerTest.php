<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Indexer;

use Maispace\MaiFaq\Domain\Model\Faq;
use Maispace\MaiFaq\Indexer\FaqIndexer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FaqIndexerTest extends TestCase
{
    private FaqIndexer $subject;

    protected function setUp(): void
    {
        $this->subject = new FaqIndexer();
    }

    #[Test]
    public function getTypeReturnsFaq(): void
    {
        self::assertSame('faq', $this->subject->getType());
    }

    #[Test]
    public function supportsFaqTable(): void
    {
        self::assertTrue($this->subject->supports('tx_maifaq_faq'));
    }

    #[Test]
    public function doesNotSupportOtherTables(): void
    {
        self::assertFalse($this->subject->supports('tx_mainews_news'));
        self::assertFalse($this->subject->supports('pages'));
        self::assertFalse($this->subject->supports('tt_content'));
    }

    #[Test]
    public function getIconReturnsExpectedValue(): void
    {
        self::assertSame('content-faq', $this->subject->getIcon('faq'));
    }

    #[Test]
    public function buildContentStripsHtmlTags(): void
    {
        $faq = new Faq();
        $faq->setQuestion('Test question?');
        $faq->setAnswer('<p>Answer with <strong>bold</strong> text.</p>');

        $content = $this->invokeBuildContent($faq);

        self::assertStringNotContainsString('<p>', $content);
        self::assertStringNotContainsString('<strong>', $content);
        self::assertStringContainsString('Answer with', $content);
        self::assertStringContainsString('bold', $content);
    }

    #[Test]
    public function buildContentReturnsEmptyStringForNonFaqRecord(): void
    {
        $content = $this->invokeBuildContent(new \stdClass());

        self::assertSame('', $content);
    }

    #[Test]
    public function formatResultReturnsSearchResultWithCorrectType(): void
    {
        $solrDoc = [
            'title_s' => 'How do I register?',
            'content_t' => 'You can register online.',
            'url_s' => '/faq',
            'score' => 3.0,
        ];

        $result = $this->subject->formatResult($solrDoc);

        self::assertSame('faq', $result->type);
        self::assertSame('How do I register?', $result->title);
        self::assertSame('/faq', $result->url);
        self::assertSame('content-faq', $result->icon);
        self::assertSame(3.0, $result->score);
    }

    #[Test]
    public function formatResultDefaultsToEmptyStringsWhenFieldsAreMissing(): void
    {
        $result = $this->subject->formatResult([]);

        self::assertSame('', $result->title);
        self::assertSame('', $result->url);
        self::assertSame(0.0, $result->score);
        self::assertNull($result->date);
    }

    private function invokeBuildContent(object $record): string
    {
        $reflection = new \ReflectionMethod($this->subject, 'buildContent');
        $reflection->setAccessible(true);

        /** @var string $result */
        return $reflection->invoke($this->subject, $record);
    }
}
