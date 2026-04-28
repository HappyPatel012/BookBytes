<?php declare(strict_types=1);

namespace UserReadingInterest\Storefront\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ReadingInterestController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $readingInterestRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    #[Route(path: '/account/reading-interests', name: 'frontend.account.reading_interest.page', methods: ['GET'])]
    public function index(SalesChannelContext $salesChannelContext): Response
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return $this->redirectToRoute('frontend.account.home.page');
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));
        $criteria->addSorting(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting('createdAt', 'DESC'));

        $interests = $this->readingInterestRepository->search($criteria, $salesChannelContext->getContext());

        return $this->renderStorefront('@Storefront/storefront/page/account/reading-interest/index.html.twig', [
            'interests' => $interests,
            'activeRoute' => 'frontend.account.reading_interest.page',
        ]);
    }

    #[Route(path: '/account/reading-interests/create', name: 'frontend.account.reading_interest.create', methods: ['POST'])]
    public function create(Request $request, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return $this->redirectToRoute('frontend.account.home.page');
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        $name = trim((string) $request->request->get('name', ''));
        $description = trim((string) $request->request->get('description', ''));

        if ($name === '') {
            $this->addFlash('danger', 'Interest name is required.');
            return $this->redirectToRoute('frontend.account.reading_interest.page');
        }

        $this->readingInterestRepository->create([
            [
                'id' => Uuid::randomHex(),
                'customerId' => $customer->getId(),
                'name' => $name,
                'description' => $description !== '' ? $description : null,
            ],
        ], $salesChannelContext->getContext());

        $this->addFlash('success', 'Reading interest added.');

        return $this->redirectToRoute('frontend.account.reading_interest.page');
    }

    #[Route(path: '/account/reading-interests/{id}/update', name: 'frontend.account.reading_interest.update', methods: ['POST'])]
    public function update(string $id, Request $request, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return $this->redirectToRoute('frontend.account.home.page');
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        if (!$this->belongsToCustomer($id, $customer->getId(), $salesChannelContext->getContext())) {
            $this->addFlash('danger', 'Interest not found.');
            return $this->redirectToRoute('frontend.account.reading_interest.page');
        }

        $name = trim((string) $request->request->get('name', ''));
        $description = trim((string) $request->request->get('description', ''));

        if ($name === '') {
            $this->addFlash('danger', 'Interest name is required.');
            return $this->redirectToRoute('frontend.account.reading_interest.page');
        }

        $this->readingInterestRepository->update([
            [
                'id' => $id,
                'name' => $name,
                'description' => $description !== '' ? $description : null,
            ],
        ], $salesChannelContext->getContext());

        $this->addFlash('success', 'Reading interest updated.');

        return $this->redirectToRoute('frontend.account.reading_interest.page');
    }

    #[Route(path: '/account/reading-interests/{id}/delete', name: 'frontend.account.reading_interest.delete', methods: ['POST'])]
    public function delete(string $id, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return $this->redirectToRoute('frontend.account.home.page');
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return $this->redirectToRoute('frontend.account.login.page');
        }

        if (!$this->belongsToCustomer($id, $customer->getId(), $salesChannelContext->getContext())) {
            $this->addFlash('danger', 'Interest not found.');
            return $this->redirectToRoute('frontend.account.reading_interest.page');
        }

        $this->readingInterestRepository->delete([
            ['id' => $id],
        ], $salesChannelContext->getContext());

        $this->addFlash('success', 'Reading interest deleted.');

        return $this->redirectToRoute('frontend.account.reading_interest.page');
    }

    private function belongsToCustomer(string $id, string $customerId, Context $context): bool
    {
        if (!Uuid::isValid($id)) {
            return false;
        }

        $criteria = (new Criteria([$id]))->addFilter(new EqualsFilter('customerId', $customerId));

        return $this->readingInterestRepository->searchIds($criteria, $context)->firstId() !== null;
    }

    private function isEnabled(string $salesChannelId): bool
    {
        $allowedSalesChannels = $this->systemConfigService->get('UserReadingInterest.config.enabledSalesChannels');

        if (!\is_array($allowedSalesChannels) || $allowedSalesChannels === []) {
            return false;
        }

        return \in_array($salesChannelId, $allowedSalesChannels, true);
    }
}
