<?php

declare(strict_types=1);

namespace Maispace\MaiFaq\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\AppendDataToPluginVariablesTrait;
use Maispace\MaiBase\Controller\Traits\PageRendererTrait;
use Maispace\MaiFaq\Domain\Repository\FaqRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\PageRenderer;

class FaqController extends AbstractActionController
{
    use AppendDataToPluginVariablesTrait;
    use PageRendererTrait;

    public function __construct(
        private readonly FaqRepository $faqRepository,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function injectPageRenderer(PageRenderer $pageRenderer): void
    {
        $this->pageRenderer = $pageRenderer;
    }

    public function injectAssetCollector(AssetCollector $assetCollector): void
    {
        $this->assetCollector = $assetCollector;
    }

    public function listAction(): ResponseInterface
    {
        $settings = $this->getSettings();

        $pageUids = $this->resolveStoragePageUids();
        $categoryUid = (int)($settings['categoryUid'] ?? 0);

        if ($pageUids !== [] && $categoryUid > 0) {
            $faqs = $this->faqRepository->findFromPagesByCategoryUid($pageUids, $categoryUid);
        } elseif ($pageUids !== []) {
            $faqs = $this->faqRepository->findFromPages($pageUids);
        } elseif ($categoryUid > 0) {
            $faqs = $this->faqRepository->findByCategoryUid($categoryUid);
        } else {
            $faqs = $this->faqRepository->findAll();
        }

        $categories = $this->resolveCategories($settings);

        $this->addJsFile(
            'mai_faq',
            'EXT:mai_faq/Resources/Public/JavaScript/faq.js',
        );

        $this->view->assignMultiple([
            'faqs' => $faqs,
            'categories' => $categories,
            'activeCategoryUid' => $categoryUid,
            'settings' => $settings,
        ]);

        return $this->htmlResponse();
    }

    private function resolveStoragePageUids(): array
    {
        $pages = $this->settings['pages'] ?? '';
        if (empty($pages)) {
            return [];
        }

        return array_filter(
            array_map('intval', explode(',', (string)$pages)),
            static fn(int $uid): bool => $uid > 0,
        );
    }

    private function resolveCategories(array $settings): array
    {
        $categoryUids = $settings['categoryUids'] ?? '';
        if (empty($categoryUids)) {
            return [];
        }

        $uids = array_filter(
            array_map('intval', explode(',', (string)$categoryUids)),
            static fn(int $uid): bool => $uid > 0,
        );

        $categories = [];
        foreach ($uids as $uid) {
            $qb = $this->connectionPool->getQueryBuilderForTable('sys_category');
            $row = $qb->select('uid', 'title')
                ->from('sys_category')
                ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid, Connection::PARAM_INT)))
                ->executeQuery()
                ->fetchAssociative();
            if ($row !== false) {
                $categories[] = $row;
            }
        }

        return $categories;
    }
}
