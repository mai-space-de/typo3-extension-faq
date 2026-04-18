<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Faq extends AbstractEntity
{
    protected string $question = '';

    protected string $answer = '';

    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->categories = new ObjectStorage();
    }

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

    /**
     * @return ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category> $categories
     */
    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }
}
