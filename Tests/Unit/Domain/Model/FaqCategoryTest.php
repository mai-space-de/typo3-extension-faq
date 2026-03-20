<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Tests\Unit\Domain\Model;

use Maispace\MaiFaq\Domain\Model\FaqCategory;
use PHPUnit\Framework\TestCase;

class FaqCategoryTest extends TestCase
{
    protected FaqCategory $subject;

    protected function setUp(): void
    {
        $this->subject = new FaqCategory();
    }

    /**
     * @test
     */
    public function getTitleReturnsInitialValue(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('General');
        self::assertSame('General', $this->subject->getTitle());
    }
}
