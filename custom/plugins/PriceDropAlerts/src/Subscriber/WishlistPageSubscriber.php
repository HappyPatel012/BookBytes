<?php declare(strict_types=1);

namespace PriceDropAlerts\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Wishlist\WishlistPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WishlistPageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityRepository $alertRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WishlistPageLoadedEvent::class => 'onWishlistLoaded',
        ];
    }

    public function onWishlistLoaded(WishlistPageLoadedEvent $event): void
    {
        $customer = $event->getSalesChannelContext()->getCustomer();
        if ($customer === null) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $event->getSalesChannelContext()->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->setLimit(500);

        $result = $this->alertRepository->search($criteria, $event->getContext());

        $productIds = [];
        foreach ($result as $alert) {
            $productIds[] = (string) $alert->get('productId');
        }

        $event->getPage()->addExtension('bookbytesPriceDropAlerts', new ArrayStruct([
            'productIds' => array_values(array_unique($productIds)),
        ]));
    }
}
