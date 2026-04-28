<?php declare(strict_types=1);

namespace SereniGrin\Subscriber;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductReviewCountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingResultEvent::class => 'onListingResult',
            ProductSearchResultEvent::class => 'onListingResult',
            ProductSuggestResultEvent::class => 'onListingResult',
            'sales_channel.product.loaded' => 'onSalesChannelProductLoaded',
            'sales_channel.product.partial_loaded' => 'onSalesChannelProductLoaded',
        ];
    }

    public function onListingResult(ProductListingResultEvent $event): void
    {
        $products = $event->getResult()->getEntities();
        $this->hydrateReviewCounts($products->getElements());
    }

    public function onSalesChannelProductLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        $this->hydrateReviewCounts($event->getEntities());
    }

    /**
     * @param array<int, object> $products
     */
    private function hydrateReviewCounts(array $products): void
    {
        if ($products === []) {
            return;
        }

        $productIds = [];
        foreach ($products as $product) {
            if (!method_exists($product, 'getId')) {
                continue;
            }

            $id = $product->getId();
            if (\is_string($id) && $id !== '') {
                $productIds[] = $id;
            }
        }

        $productIds = array_values(array_unique($productIds));
        if ($productIds === []) {
            return;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT LOWER(HEX(product_id)) AS product_id,
                    COUNT(DISTINCT COALESCE(
                        NULLIF(LOWER(HEX(customer_id)), ""),
                        NULLIF(LOWER(external_email), ""),
                        NULLIF(LOWER(external_user), ""),
                        LOWER(HEX(id))
                    )) AS review_count
             FROM product_review
             WHERE status = 1
               AND LOWER(HEX(product_id)) IN (:productIds)
             GROUP BY product_id',
            ['productIds' => $productIds],
            ['productIds' => ArrayParameterType::STRING]
        );

        $countsByProductId = [];
        foreach ($rows as $row) {
            $productId = (string) ($row['product_id'] ?? '');
            if ($productId === '') {
                continue;
            }

            $countsByProductId[$productId] = (int) ($row['review_count'] ?? 0);
        }

        foreach ($products as $product) {
            if (!method_exists($product, 'getId') || !method_exists($product, 'addExtension')) {
                continue;
            }

            $productId = (string) $product->getId();
            $reviewCount = $countsByProductId[$productId] ?? 0;

            $product->addExtension('sgReviewStats', new ArrayStruct([
                'reviewCount' => $reviewCount,
            ]));
        }
    }
}
