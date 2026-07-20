<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Subscriber;

use SmartBundleRecommendations\Service\BundleRecommendationService;
use SmartBundleRecommendations\Struct\BundleRecommendationStruct;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BundleRecommendationService $recommendationService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        $productId = $page->getProduct()->getId();

        $items = $this->recommendationService->getRecommendations($productId, $event->getContext(), 3);
        $page->addExtension('smartBundleRecommendations', new BundleRecommendationStruct($productId, $items));
    }
}
