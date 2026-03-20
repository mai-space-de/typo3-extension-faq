<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Controller;

use Maispace\MaiFaq\Domain\Repository\FaqCategoryRepository;
use Maispace\MaiFaq\Domain\Repository\FaqEntryRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FaqController extends ActionController
{
    public function __construct(
        protected readonly FaqEntryRepository $faqEntryRepository,
        protected readonly FaqCategoryRepository $faqCategoryRepository
    ) {
    }

    public function accordionAction(): ResponseInterface
    {
        $entries = $this->faqEntryRepository->findAll();
        $this->view->assign('entries', $entries);
        return $this->htmlResponse();
    }

    public function tabsAction(): ResponseInterface
    {
        $categories = $this->faqCategoryRepository->findAll();
        $entriesByCategory = [];
        foreach ($categories as $category) {
            $entriesByCategory[$category->getUid()] = [
                'category' => $category,
                'entries' => $this->faqEntryRepository->findByCategory($category),
            ];
        }
        $this->view->assign('categories', $categories);
        $this->view->assign('entriesByCategory', $entriesByCategory);
        return $this->htmlResponse();
    }

    public function searchAction(string $searchWord = ''): ResponseInterface
    {
        $entries = null;
        if ($searchWord !== '') {
            $entries = $this->faqEntryRepository->findBySearchWord($searchWord);
        }
        $this->view->assign('searchWord', $searchWord);
        $this->view->assign('entries', $entries);
        return $this->htmlResponse();
    }
}
