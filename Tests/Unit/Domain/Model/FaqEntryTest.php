<?php

declare(strict_types=1);

namespace MaiSpace\Faq\Tests\Unit\Domain\Model;

use MaiSpace\Faq\Domain\Model\FaqCategory;
use MaiSpace\Faq\Domain\Model\FaqEntry;
use PHPUnit\Framework\TestCase;

class FaqEntryTest extends TestCase
{
    protected FaqEntry $subject;

    protected function setUp(): void
    {
        $this->subject = new FaqEntry();
    }

    /**
     * @test
     */
    public function getQuestionReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getQuestion());
    }

    /**
     * @test
     */
    public function setQuestionSetsQuestion(): void
    {
        $this->subject->setQuestion('What is TYPO3?');
        self::assertSame('What is TYPO3?', $this->subject->getQuestion());
    }

    /**
     * @test
     */
    public function getAnswerReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getAnswer());
    }

    /**
     * @test
     */
    public function setAnswerSetsAnswer(): void
    {
        $this->subject->setAnswer('<p>TYPO3 is a CMS.</p>');
        self::assertSame('<p>TYPO3 is a CMS.</p>', $this->subject->getAnswer());
    }

    /**
     * @test
     */
    public function getCategoryReturnsInitialNull(): void
    {
        self::assertNull($this->subject->getCategory());
    }

    /**
     * @test
     */
    public function setCategorySetsCategory(): void
    {
        $category = new FaqCategory();
        $this->subject->setCategory($category);
        self::assertSame($category, $this->subject->getCategory());
    }

    /**
     * @test
     */
    public function setCategoryAcceptsNull(): void
    {
        $this->subject->setCategory(null);
        self::assertNull($this->subject->getCategory());
    }

    /**
     * @test
     */
    public function getSortingReturnsInitialValue(): void
    {
        self::assertSame(0, $this->subject->getSorting());
    }

    /**
     * @test
     */
    public function setSortingSetsSorting(): void
    {
        $this->subject->setSorting(10);
        self::assertSame(10, $this->subject->getSorting());
    }
}
