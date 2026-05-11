<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Struct;

use Shopware\Core\Framework\Struct\Struct;

class BundleRecommendationStruct extends Struct
{
    public function __construct(
        protected readonly string $productId,
        protected readonly array $items
    ) {
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
