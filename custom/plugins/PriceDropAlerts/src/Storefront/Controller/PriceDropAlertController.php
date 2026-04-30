<?php declare(strict_types=1);

namespace PriceDropAlerts\Storefront\Controller;

use PriceDropAlerts\Service\PriceResolver;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class PriceDropAlertController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $alertRepository,
        private readonly PriceResolver $priceResolver
    ) {
    }

    #[Route(path: '/price-drop-alert/toggle', name: 'frontend.price_drop_alert.toggle', methods: ['POST'])]
    public function toggle(Request $request, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        $customer = $salesChannelContext->getCustomer();

        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $productId = (string) $request->request->get('productId');
        $redirectTo = (string) $request->request->get('redirectTo', 'frontend.wishlist.page');

        if (!Uuid::isValid($productId)) {
            $this->addFlash('danger', 'Invalid product selected for price alert.');
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
                ? 'Price-drop notification enabled.'
                : 'Price-drop notification disabled.');

            return $this->redirectToRoute($redirectTo);
        }

        $currentGrossPrice = $this->priceResolver->resolveGrossPrice($productId, $salesChannelContext->getContext());

        if ($currentGrossPrice === null) {
            $this->addFlash('danger', 'Could not enable alert for this product right now.');
            return $this->redirectToRoute($redirectTo);
        }

        $this->alertRepository->create([
            [
                'id' => Uuid::randomHex(),
                'customerId' => $customer->getId(),
                'productId' => $productId,
                'salesChannelId' => $salesChannelContext->getSalesChannelId(),
                'lastKnownGrossPrice' => $currentGrossPrice,
                'active' => true,
            ],
        ], $salesChannelContext->getContext());

        $this->addFlash('success', 'You will be notified when this price drops.');

        return $this->redirectToRoute($redirectTo);
    }
}
