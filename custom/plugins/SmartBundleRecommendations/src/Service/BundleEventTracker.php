<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;

class BundleEventTracker
{
    public function __construct(private readonly EntityRepository $bundleEventRepository)
    {
    }

    public function track(
        string $eventType,
        string $productId,
        ?string $relatedProductId,
        ?string $salesChannelId,
        ?string $customerId,
        array $payload,
        Context $context
    ): void {
        $this->bundleEventRepository->create([
            [
                'id' => Uuid::randomHex(),
                'eventType' => $eventType,
                'productId' => $productId,
                'relatedProductId' => $relatedProductId,
                'salesChannelId' => $salesChannelId,
                'customerId' => $customerId,
                'payload' => $payload,
            ],
        ], $context);
    }
}
