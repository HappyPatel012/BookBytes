<?php declare(strict_types=1);

namespace PriceDropAlerts\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class PriceResolver
{
    public function __construct(private readonly EntityRepository $productRepository)
    {
    }

    public function resolveGrossPrice(string $productId, Context $context): ?float
    {
        $product = $this->productRepository->search(new Criteria([$productId]), $context)->first();

        if ($product === null || !method_exists($product, 'getPrice')) {
            return null;
        }

        $priceCollection = $product->getPrice();
        if ($priceCollection === null || $priceCollection->count() === 0) {
            return null;
        }

        $first = $priceCollection->first();
        if ($first === null) {
            return null;
        }

        return (float) $first->getGross();
    }
}
