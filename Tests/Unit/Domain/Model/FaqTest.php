<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Domain\Model;

use Maispace\MaiFaq\Domain\Model\Faq;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class FaqTest extends TestCase
{
    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultQuestionIsEmptyString(): void
    {
        $faq = new Faq();
        self::assertSame('', $faq->getQuestion());
    }

    #[Test]
    public function defaultAnswerIsEmptyString(): void
    {
        $faq = new Faq();
        self::assertSame('', $faq->getAnswer());
    }

    #[Test]
    public function constructorInitializesCategoriesAsObjectStorage(): void
    {
        $faq = new Faq();
        self::assertInstanceOf(ObjectStorage::class, $faq->getCategories());
    }

    #[Test]
    public function constructorCreatesFreshEmptyObjectStorage(): void
    {
        $faq = new Faq();
        self::assertCount(0, $faq->getCategories());
    }

    // ── initializeObject ────────────────────────────────────────────────────

    #[Test]
    public function initializeObjectCreatesFreshObjectStorage(): void
    {
        $faq = new Faq();
        $original = $faq->getCategories();
        $faq->initializeObject();
        self::assertInstanceOf(ObjectStorage::class, $faq->getCategories());
        self::assertNotSame($original, $faq->getCategories());
    }

    // ── question getter / setter ────────────────────────────────────────────

    #[Test]
    public function setQuestionStoresTheValue(): void
    {
        $faq = new Faq();
        $faq->setQuestion('What is TYPO3?');
        self::assertSame('What is TYPO3?', $faq->getQuestion());
    }

    #[Test]
    public function setQuestionOverwritesPreviousValue(): void
    {
        $faq = new Faq();
        $faq->setQuestion('First question');
        $faq->setQuestion('Second question');
        self::assertSame('Second question', $faq->getQuestion());
    }

    #[Test]
    public function setQuestionAcceptsEmptyString(): void
    {
        $faq = new Faq();
        $faq->setQuestion('Non-empty');
        $faq->setQuestion('');
        self::assertSame('', $faq->getQuestion());
    }

    // ── answer getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setAnswerStoresTheValue(): void
    {
        $faq = new Faq();
        $faq->setAnswer('TYPO3 is an open-source CMS.');
        self::assertSame('TYPO3 is an open-source CMS.', $faq->getAnswer());
    }

    #[Test]
    public function setAnswerOverwritesPreviousValue(): void
    {
        $faq = new Faq();
        $faq->setAnswer('First answer');
        $faq->setAnswer('Second answer');
        self::assertSame('Second answer', $faq->getAnswer());
    }

    #[Test]
    public function setAnswerAcceptsEmptyString(): void
    {
        $faq = new Faq();
        $faq->setAnswer('Non-empty');
        $faq->setAnswer('');
        self::assertSame('', $faq->getAnswer());
    }

    // ── categories getter / setter ──────────────────────────────────────────

    #[Test]
    public function setCategoriesStoresTheObjectStorage(): void
    {
        $faq = new Faq();
        $storage = new ObjectStorage();
        $faq->setCategories($storage);
        self::assertSame($storage, $faq->getCategories());
    }

    #[Test]
    public function getCategoriesReturnsSameInstanceAfterSet(): void
    {
        $faq = new Faq();
        $storage = new ObjectStorage();
        $faq->setCategories($storage);
        self::assertSame($storage, $faq->getCategories());
    }

    #[Test]
    public function twoFaqInstancesHaveIndependentCategoryStorages(): void
    {
        $faq1 = new Faq();
        $faq2 = new Faq();
        self::assertNotSame($faq1->getCategories(), $faq2->getCategories());
    }
}
