<?php declare(strict_types=1);

namespace PriceDropAlerts\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Wishlist\WishlistPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WishlistPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $alertRepository,
        private readonly SystemConfigService $systemConfigService
    )
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
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();

        if ($customer === null || !$this->isSalesChannelAllowed($salesChannelId)) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
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

    private function isSalesChannelAllowed(string $salesChannelId): bool
    {
        $allowedSalesChannels = $this->systemConfigService->get('PriceDropAlerts.config.enabledSalesChannels');
        if (!\is_array($allowedSalesChannels) || $allowedSalesChannels === []) {
            return true;
        }

        return \in_array($salesChannelId, $allowedSalesChannels, true);
    }
}
