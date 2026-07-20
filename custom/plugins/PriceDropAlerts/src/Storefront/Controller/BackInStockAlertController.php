<?php declare(strict_types=1);

namespace PriceDropAlerts\Storefront\Controller;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class BackInStockAlertController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $alertRepository,
        private readonly SalesChannelRepository $salesChannelProductRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    #[Route(path: '/back-in-stock-alert/toggle', name: 'frontend.back_in_stock_alert.toggle', methods: ['POST'])]
    public function toggle(Request $request, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        $customer = $salesChannelContext->getCustomer();

        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $productId = (string) $request->request->get('productId');
        $redirectTo = (string) $request->request->get('redirectTo', 'frontend.wishlist.page');

        if (!Uuid::isValid($productId)) {
            $this->addFlash('danger', 'Invalid product selected for stock alert.');
            return $this->redirectToRoute($redirectTo);
        }

        if (!$this->isSalesChannelAllowed($salesChannelContext->getSalesChannelId())) {
            $this->addFlash('danger', 'Back-in-stock alerts are not available for this sales channel.');
            return $this->redirectToRoute($redirectTo);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelContext->getSalesChannelId()));
        $criteria->setLimit(1);

        $existing = $this->alertRepository->search($criteria, $salesChannelContext->getContext())->first();

        if ($existing !== null) {
            $newActive = !(bool) $existing->get('active');
            $this->alertRepository->update([
                [
                    'id' => $existing->getUniqueIdentifier(),
                    'active' => $newActive,
                ],
            ], $salesChannelContext->getContext());

            $this->addFlash('success', $newActive
                ? 'Back-in-stock notification enabled.'
                : 'Back-in-stock notification disabled.');

            return $this->redirectToRoute($redirectTo);
        }

        $currentStock = $this->resolveStock($productId, $salesChannelContext);

        $this->alertRepository->create([
            [
                'id' => Uuid::randomHex(),
                'customerId' => $customer->getId(),
                'productId' => $productId,
                'salesChannelId' => $salesChannelContext->getSalesChannelId(),
                'lastKnownStock' => $currentStock,
                'active' => true,
            ],
        ], $salesChannelContext->getContext());

        $this->addFlash('success', 'You will be notified when this product is back in stock.');

        return $this->redirectToRoute($redirectTo);
    }

    private function resolveStock(string $productId, SalesChannelContext $salesChannelContext): int
    {
        $criteria = new Criteria([$productId]);
        /** @var SalesChannelProductEntity|null $product */
        $product = $this->salesChannelProductRepository
            ->search($criteria, $salesChannelContext)
            ->first();

        if ($product === null) {
            return 0;
        }

        return (int) $product->getStock();
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
