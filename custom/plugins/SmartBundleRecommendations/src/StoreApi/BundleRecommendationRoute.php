<?php declare(strict_types=1);

namespace SmartBundleRecommendations\StoreApi;

use SmartBundleRecommendations\Service\BundleRecommendationService;
use SmartBundleRecommendations\Service\BundleEventTracker;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\StoreApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE_STORE_API]])]
class BundleRecommendationRoute
{
    public function __construct(
        private readonly BundleRecommendationService $recommendationService,
        private readonly BundleEventTracker $tracker
    ) {
    }

    #[Route(path: '/store-api/smart-bundle/{productId}', name: 'store-api.smart-bundle.fetch', methods: ['GET'])]
    public function load(string $productId, Request $request, SalesChannelContext $context): StoreApiResponse
    {
        $limit = max(1, (int) $request->query->get('limit', 3));
        $items = $this->recommendationService->getRecommendations($productId, $context->getContext(), $limit);

        foreach ($items as $item) {
            $this->tracker->track(
                'impression',
                $productId,
                $item['relatedProductId'],
                $context->getSalesChannelId(),
                $context->getCustomerId(),
                ['route' => 'store-api'],
                $context->getContext()
            );
        }

        return new StoreApiResponse(new ArrayStruct([
            'items' => $items,
            'productId' => $productId,
        ]));
    }

    #[Route(path: '/store-api/smart-bundle/track', name: 'store-api.smart-bundle.track', methods: ['POST'])]
    public function track(Request $request, SalesChannelContext $context): StoreApiResponse
    {
        $payload = json_decode((string) $request->getContent(), true) ?? [];

        $this->tracker->track(
            (string) ($payload['eventType'] ?? 'click'),
            (string) ($payload['productId'] ?? ''),
            $payload['relatedProductId'] ?? null,
            $context->getSalesChannelId(),
            $context->getCustomerId(),
            $payload,
            $context->getContext()
        );

        return new StoreApiResponse(new ArrayStruct(['success' => true]));
    }
}
