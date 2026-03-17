<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Payment;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\ShopwarePayment\StripeApi\StripeApiFactory;

class StripeCustomerService
{
    /**
     * @var StripeApiFactory
     */
    private $stripeApiFactory;

    /**
     * @var EntityRepository
     */
    private $customerRepository;

    /**
     * @var EntityRepository
     */
    private $orderCustomerRepository;

    public function __construct(
        StripeApiFactory $stripeApiFactory,
        EntityRepository $customerRepository,
        EntityRepository $orderCustomerRepository
    ) {
        $this->stripeApiFactory = $stripeApiFactory;
        $this->customerRepository = $customerRepository;
        $this->orderCustomerRepository = $orderCustomerRepository;
    }

    public function getCustomerForOrderTransaction(OrderTransactionEntity $orderTransactionEntity, Context $context): CustomerEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociations(['salesChannel']);
        $criteria->addFilter(new EqualsFilter('orderId', $orderTransactionEntity->getOrderId()));
        $criteria->addFilter(new EqualsFilter('orderVersionId', $orderTransactionEntity->getOrderVersionId()));
        /** @var OrderCustomerEntity $orderCustomer */
        $orderCustomer = $this->orderCustomerRepository->search(
            $criteria,
            $context,
        )->first();

        return $this->getShopwareCustomer($orderCustomer->getCustomerId(), $context);
    }

    public function getStripeCustomerForShopwareCustomer(
        string $shopwareCustomerId,
        string $salesChannelId,
        Context $context
    ): ?Customer {
        $customer = $this->getShopwareCustomer($shopwareCustomerId, $context);

        if (!$customer->getCustomFields() || !isset($customer->getCustomFields()['stripeCustomerId'])) {
            return null;
        }

        $stripeApi = $this->stripeApiFactory->createStripeApiForSalesChannel(
            $context,
            $salesChannelId,
        );

        $stripeCustomer = null;
        try {
            $stripeCustomer = $stripeApi->getCustomer(
                $customer->getCustomFields()['stripeCustomerId'],
            );
        } catch (ApiErrorException $e) {
            // Handle removed or invalid stripe customers below
            if ($e->getStripeCode() !== 'resource_missing') {
                throw $e;
            }
        }
        if (!$stripeCustomer || $stripeCustomer->isDeleted()) {
            return null;
        }

        return $stripeCustomer;
    }

    public function createStripeCustomerForShopwareCustomer(
        string $shopwareCustomerId,
        string $salesChannelId,
        Context $context
    ): Customer {
        $customer = $this->getShopwareCustomer($shopwareCustomerId, $context);

        $stripeApi = $this->stripeApiFactory->createStripeApiForSalesChannel(
            $context,
            $salesChannelId,
        );

        $customerName = $customer->getFirstName() . ' ' . $customer->getLastName();

        return $stripeApi->createCustomer(
            [
                'name' => $customerName,
                'description' => $customerName,
                'email' => $customer->getEmail(),
            ],
        );
    }

    public function attachStripeCustomerToShopwareCustomer(
        string $shopwareCustomerId,
        Customer $stripeCustomer,
        Context $context
    ): void {
        $customerValues = [
            'id' => $shopwareCustomerId,
            'customFields' => [
                'stripeCustomerId' => $stripeCustomer->id,
            ],
        ];

        $this->customerRepository->update([$customerValues], $context);
    }

    private function getShopwareCustomer(string $shopwareCustomerId, Context $context): ?CustomerEntity
    {
        return $this->customerRepository->search(new Criteria([$shopwareCustomerId]), $context)->first();
    }
}
