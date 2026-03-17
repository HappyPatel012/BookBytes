<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\PaymentMethods\Klarna;

use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Locale\LocaleEntity;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentConfig\PaymentIntentConfig;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentConfig\PaymentIntentPaymentConfigurator;
use Stripe\ShopwarePayment\Payment\StripePaymentContext;
use Stripe\ShopwarePayment\StripeApi\StripeApiFactory;

class KlarnaPaymentConfigurator implements PaymentIntentPaymentConfigurator
{
    /**
     * @var EntityRepository
     */
    private $orderTransactionRepository;

    /**
     * @var EntityRepository
     */
    private $languageRepository;

    /**
     * @var StripeApiFactory
     */
    private $stripeApiFactory;

    private PaymentIntentPaymentConfigurator $defaultPaymentIntentPaymentConfigurator;

    public function __construct(
        EntityRepository $orderTransactionRepository,
        PaymentIntentPaymentConfigurator $defaultPaymentIntentPaymentConfigurator,
        EntityRepository $languageRepository,
        StripeApiFactory $stripeApiFactory
    ) {
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->defaultPaymentIntentPaymentConfigurator = $defaultPaymentIntentPaymentConfigurator;
        $this->languageRepository = $languageRepository;
        $this->stripeApiFactory = $stripeApiFactory;
    }

    public function configure(
        PaymentIntentConfig $paymentIntentConfig,
        StripePaymentContext $stripePaymentContext
    ): void {
        $this->defaultPaymentIntentPaymentConfigurator->configure(
            $paymentIntentConfig,
            $stripePaymentContext,
        );

        $criteria = new Criteria([$stripePaymentContext->orderTransactionId]);
        $criteria->addAssociations([
            'order',
            'order.currency',
            'order.lineItems',
            'order.orderCustomer',
            'order.addresses.country',
            'order.salesChannel',
        ]);
        /** @var OrderTransactionEntity $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search(
            $criteria,
            $stripePaymentContext->context,
        )->first();

        $order = $orderTransaction->getOrder();
        $stripeApi = $this->stripeApiFactory->createStripeApiForSalesChannel(
            $stripePaymentContext->context,
            $order->getSalesChannelId(),
        );
        $orderCustomer = $order->getOrderCustomer();
        $billingAddress = $this->getBillingAddress($order);
        $shippingAddress = $this->getShippingAddress($order);

        $paymentMethod = $stripeApi->createPaymentMethod([
            'type' => 'klarna',
            'billing_details' => [
                'email' => $orderCustomer->getEmail(),
                'name' => sprintf('%s %s', $orderCustomer->getFirstName(), $orderCustomer->getLastName()),
                'address' => [
                    'line1' => $billingAddress->getStreet(),
                    'city' => $billingAddress->getCity(),
                    'postal_code' => $billingAddress->getZipcode(),
                    'country' => $billingAddress->getCountry()->getIso(),
                ],
            ]
        ]);

        $paymentIntentConfig->setStripePaymentMethodId($paymentMethod->id);
        $paymentIntentConfig->setMethodSpecificElements([
            'payment_method_types' => ['klarna']
        ]);
    }

    private function getBillingAddress(OrderEntity $order): OrderAddressEntity
    {
        $billingAddressId = $order->getBillingAddressId();

        return $order->getAddresses()->filter(fn ($address) => $address->getId() === $billingAddressId)->first();
    }

    private function getShippingAddress(OrderEntity $order): OrderAddressEntity
    {
        $billingAddressId = $order->getBillingAddressId();

        $shippingAddress = $order->getAddresses()->filter(fn ($address) => $address->getId() !== $billingAddressId)->first();
        if ($shippingAddress) {
            return $shippingAddress;
        }

        return $this->getBillingAddress($order);
    }

    private function getLocaleForLanguage(string $languageId, Context $context): ?LocaleEntity
    {
        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');

        return $this->languageRepository->search($criteria, $context)->get($languageId)->getLocale();
    }
}
