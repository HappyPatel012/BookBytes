<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Service;

use SmartBundleRecommendations\Core\Content\BundleRule\BundleRuleEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BundleRecommendationService
{
    public function __construct(
        private readonly EntityRepository $bundleRuleRepository,
        private readonly EntityRepository $bundleCandidateRepository,
        private readonly EntityRepository $productRepository,
        private readonly CacheInterface $cache
    ) {
    }

    public function getRecommendations(string $productId, Context $context, int $limit = 3): array
    {
        $cacheKey = sprintf('smart_bundle_%s_%d', $productId, $limit);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $context, $limit): array {
            $item->expiresAfter(3600);

            $rule = $this->getActiveRule($context);
            if ($rule === null) {
                return [];
            }

            $weights = $rule->getWeights() ?? [];
            $filters = $rule->getFilters() ?? [];

            $criteria = (new Criteria())
                ->addFilter(new EqualsFilter('productId', $productId))
                ->addAssociation('relatedProduct')
                ->setLimit(max(1, $limit * 3))
                ->addSorting(new FieldSorting('score', FieldSorting::DESCENDING));

            $candidates = $this->bundleCandidateRepository->search($criteria, $context)->getEntities();
            $seedProduct = $this->loadProduct($productId, $context);

            $results = [];
            foreach ($candidates as $candidate) {
                /** @var ProductEntity|null $related */
                $related = $candidate->get('relatedProduct');
                if ($related === null || !$this->passesFilters($related, $filters)) {
                    continue;
                }

                $dynamicScore = $this->computeDynamicScore($seedProduct, $related, $weights);
                $results[] = [
                    'relatedProductId' => $candidate->get('relatedProductId'),
                    'score' => ((float) $candidate->get('score')) + $dynamicScore,
                    'source' => $candidate->get('source'),
                    'product' => $related,
                ];
            }

            usort($results, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

            return array_slice($results, 0, max(1, $limit));
        });
    }

    public function invalidateCache(): void
    {
        $this->cache->clear();
    }

    private function getActiveRule(Context $context): ?BundleRuleEntity
    {
        $ruleCriteria = (new Criteria())
            ->addFilter(new EqualsFilter('active', true))
            ->setLimit(1);

        /** @var BundleRuleEntity|null $rule */
        $rule = $this->bundleRuleRepository->search($ruleCriteria, $context)->first();

        return $rule;
    }

    private function loadProduct(string $productId, Context $context): ?ProductEntity
    {
        $criteria = (new Criteria([$productId]))->addAssociation('categories');

        return $this->productRepository->search($criteria, $context)->first();
    }

    private function passesFilters(ProductEntity $product, array $filters): bool
    {
        if (($filters['requireActive'] ?? true) && !$product->getActive()) {
            return false;
        }

        $minStock = (int) ($filters['minStock'] ?? 1);
        if ((int) $product->getAvailableStock() < $minStock) {
            return false;
        }

        $excluded = $filters['excludedProductIds'] ?? [];
        if (in_array($product->getId(), $excluded, true)) {
            return false;
        }

        return true;
    }

    private function computeDynamicScore(?ProductEntity $seed, ProductEntity $related, array $weights): float
    {
        if ($seed === null) {
            return 0.0;
        }

        $priceWeight = (float) ($weights['priceDistance'] ?? 0.1);
        $categoryWeight = (float) ($weights['category'] ?? 0.3);

        $seedPrice = $seed->getCalculatedPrice()?->getUnitPrice() ?? 0.0;
        $relatedPrice = $related->getCalculatedPrice()?->getUnitPrice() ?? 0.0;
        $priceDistance = $seedPrice > 0 ? abs($seedPrice - $relatedPrice) / $seedPrice : 1.0;

        $seedCategoryIds = $seed->getCategoryIds();
        $relatedCategoryIds = $related->getCategoryIds();
        $intersection = count(array_intersect($seedCategoryIds ?? [], $relatedCategoryIds ?? []));

        return (1.0 - min(1.0, $priceDistance)) * $priceWeight + ($intersection > 0 ? 1.0 : 0.0) * $categoryWeight;
    }
}
