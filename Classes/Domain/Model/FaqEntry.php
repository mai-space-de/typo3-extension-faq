<?php

declare(strict_types=1);

namespace MaiSpace\Faq\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class FaqEntry extends AbstractEntity
{
    protected string $question = '';

    protected string $answer = '';

    protected ?FaqCategory $category = null;

    protected int $sorting = 0;

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getCategory(): ?FaqCategory
    {
        return $this->category;
    }

    public function setCategory(?FaqCategory $category): void
    {
        $this->category = $category;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }
}
