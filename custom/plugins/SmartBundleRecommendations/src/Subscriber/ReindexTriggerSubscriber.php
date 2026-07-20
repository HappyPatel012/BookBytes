<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Subscriber;

use SmartBundleRecommendations\Service\BundleIndexer;
use Shopware\Core\Checkout\Order\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReindexTriggerSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BundleIndexer $bundleIndexer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onRelevantWrite',
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    public function onRelevantWrite(EntityWrittenEvent $event): void
    {
        $this->bundleIndexer->rebuild();
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $this->bundleIndexer->rebuild();
    }
}
