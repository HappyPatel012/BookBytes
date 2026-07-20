<?php declare(strict_types=1);

namespace UserReadingInterest\StoreApi\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ReadingInterestApiController extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $readingInterestRepository,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    #[Route(path: '/store-api/reading-interests', name: 'store-api.reading_interest.list', methods: ['GET'])]
    public function list(SalesChannelContext $salesChannelContext): JsonResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return new JsonResponse(['errors' => ['Reading interests are disabled for this sales channel']], 403);
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return new JsonResponse(['errors' => ['Login required']], 401);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerId', $customer->getId()));
        $criteria->addSorting(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting('createdAt', 'DESC'));

        $result = $this->readingInterestRepository->search($criteria, $salesChannelContext->getContext());
        $items = [];

        foreach ($result as $interest) {
            $items[] = [
                'id' => $interest->getId(),
                'name' => $interest->get('name'),
                'description' => $interest->get('description'),
                'createdAt' => $interest->getCreatedAt()?->format(DATE_ATOM),
                'updatedAt' => $interest->getUpdatedAt()?->format(DATE_ATOM),
            ];
        }

        return new JsonResponse(['data' => $items]);
    }

    #[Route(path: '/store-api/reading-interests', name: 'store-api.reading_interest.create', methods: ['POST'])]
    public function create(Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return new JsonResponse(['errors' => ['Reading interests are disabled for this sales channel']], 403);
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return new JsonResponse(['errors' => ['Login required']], 401);
        }

        $payload = $this->getPayload($request);
        $name = trim((string) ($payload['name'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($name === '') {
            return new JsonResponse(['errors' => ['Name is required']], 400);
        }

        $id = Uuid::randomHex();
        $this->readingInterestRepository->create([[ 
            'id' => $id,
            'customerId' => $customer->getId(),
            'name' => $name,
            'description' => $description !== '' ? $description : null,
        ]], $salesChannelContext->getContext());

        return new JsonResponse(['id' => $id], 201);
    }

    #[Route(path: '/store-api/reading-interests/{id}', name: 'store-api.reading_interest.update', methods: ['PATCH'])]
    public function update(string $id, Request $request, SalesChannelContext $salesChannelContext): JsonResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return new JsonResponse(['errors' => ['Reading interests are disabled for this sales channel']], 403);
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return new JsonResponse(['errors' => ['Login required']], 401);
        }

        if (!$this->belongsToCustomer($id, $customer->getId(), $salesChannelContext->getContext())) {
            return new JsonResponse(['errors' => ['Interest not found']], 404);
        }

        $payload = $this->getPayload($request);
        $name = trim((string) ($payload['name'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($name === '') {
            return new JsonResponse(['errors' => ['Name is required']], 400);
        }

        $this->readingInterestRepository->update([[ 
            'id' => $id,
            'name' => $name,
            'description' => $description !== '' ? $description : null,
        ]], $salesChannelContext->getContext());

        return new JsonResponse(['success' => true]);
    }

    #[Route(path: '/store-api/reading-interests/{id}', name: 'store-api.reading_interest.delete', methods: ['DELETE'])]
    public function delete(string $id, SalesChannelContext $salesChannelContext): JsonResponse
    {
        if (!$this->isEnabled($salesChannelContext->getSalesChannelId())) {
            return new JsonResponse(['errors' => ['Reading interests are disabled for this sales channel']], 403);
        }

        $customer = $salesChannelContext->getCustomer();
        if ($customer === null) {
            return new JsonResponse(['errors' => ['Login required']], 401);
        }

        if (!$this->belongsToCustomer($id, $customer->getId(), $salesChannelContext->getContext())) {
            return new JsonResponse(['errors' => ['Interest not found']], 404);
        }

        $this->readingInterestRepository->delete([['id' => $id]], $salesChannelContext->getContext());

        return new JsonResponse(['success' => true]);
    }

    private function belongsToCustomer(string $id, string $customerId, Context $context): bool
    {
        if (!Uuid::isValid($id)) {
            return false;
        }

        $criteria = (new Criteria([$id]))->addFilter(new EqualsFilter('customerId', $customerId));

        return $this->readingInterestRepository->searchIds($criteria, $context)->firstId() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    private function getPayload(Request $request): array
    {
        try {
            return $request->toArray();
        } catch (\JsonException) {
            return $request->request->all();
        }
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
