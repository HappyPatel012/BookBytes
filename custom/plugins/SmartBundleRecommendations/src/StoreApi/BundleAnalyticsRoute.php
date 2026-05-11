<?php declare(strict_types=1);

namespace SmartBundleRecommendations\StoreApi;

use SmartBundleRecommendations\Service\BundleAnalyticsService;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\StoreApiResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE_STORE_API]])]
class BundleAnalyticsRoute
{
    public function __construct(private readonly BundleAnalyticsService $analyticsService)
    {
    }

    #[Route(path: '/store-api/smart-bundle/report', name: 'store-api.smart-bundle.report', methods: ['GET'])]
    public function report(SalesChannelContext $context): StoreApiResponse
    {
        return new StoreApiResponse(new ArrayStruct($this->analyticsService->getSummary()));
    }
}
