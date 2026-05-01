<?php declare(strict_types=1);

namespace PriceDropAlerts\Subscriber;

use PriceDropAlerts\Service\PriceDropMailService;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPriceWrittenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $alertRepository,
        private readonly EntityRepository $productRepository,
        private readonly PriceDropMailService $mailService,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductWritten',
        ];
    }

    public function onProductWritten(EntityWrittenEvent $event): void
    {
        $productIds = array_values(array_filter($event->getIds()));
        if ($productIds === []) {
            return;
        }

        $alertCriteria = new Criteria();
        $alertCriteria->addFilter(new EqualsAnyFilter('productId', $productIds));
        $alertCriteria->addFilter(new EqualsFilter('active', true));
        $alertCriteria->addAssociation('customer');
        $alertCriteria->addAssociation('product');
        $alerts = $this->alertRepository->search($alertCriteria, $event->getContext());

        if ($alerts->count() === 0) {
            return;
        }

        $productCriteria = new Criteria($productIds);
        $products = $this->productRepository->search($productCriteria, $event->getContext());

        $priceByProductId = [];
        foreach ($products as $product) {
            $priceCollection = $product->getPrice();
            if ($priceCollection === null || $priceCollection->count() === 0 || $priceCollection->first() === null) {
                continue;
            }

            $priceByProductId[$product->getUniqueIdentifier()] = (float) $priceCollection->first()->getGross();
        }

        foreach ($alerts as $alert) {
            $salesChannelId = (string) $alert->get('salesChannelId');
            if (!$this->isSalesChannelAllowed($salesChannelId)) {
                continue;
            }

            $productId = (string) $alert->get('productId');
            $newGross = $priceByProductId[$productId] ?? null;

            if ($newGross === null) {
                continue;
            }

            $previousGross = (float) $alert->get('lastKnownGrossPrice');
            if ($newGross >= $previousGross) {
                continue;
            }

            $customer = $alert->get('customer');
            $product = $alert->get('product');

            if ($customer === null || $product === null || !(string) $customer->getEmail()) {
                continue;
            }

            $sent = $this->mailService->send(
                $salesChannelId,
                [
                    'id' => $customer->getUniqueIdentifier(),
                    'firstName' => $customer->getFirstName(),
                    'lastName' => $customer->getLastName(),
                    'email' => $customer->getEmail(),
                ],
                [
                    'id' => $product->getUniqueIdentifier(),
                    'translated' => ['name' => $product->getTranslation('name') ?? $product->getName()],
                ],
                $newGross,
                $event->getContext()
            );

            if (!$sent) {
                continue;
            }

            $this->alertRepository->update([
                [
                    'id' => $alert->getUniqueIdentifier(),
                    'lastKnownGrossPrice' => $newGross,
                    'lastNotifiedAt' => new \DateTimeImmutable(),
                ],
            ], $event->getContext());
        }
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
