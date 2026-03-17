<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Payment\DependencyInjection;

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Stripe\ShopwarePayment\Payment\IdempotentOrderTransactionStateHandler;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentConfig\PaymentIntentPaymentConfigurator;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler\PaymentIntentHandlerService;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler\PaymentIntentPaymentHandler66;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler\PaymentIntentPaymentHandler67;
use Stripe\ShopwarePayment\Payment\StripeCustomerService;
use Stripe\ShopwarePayment\Payment\StripeOrderTransactionService;
use Stripe\ShopwarePayment\Payment\StripePaymentHandlerInterface;
use Stripe\ShopwarePayment\Session\StripePaymentMethodSettings;
use Stripe\ShopwarePayment\StripeApi\StripeApiFactory;
use Symfony\Component\DependencyInjection\Container;

class PaymentHandlerFactory implements PaymentHandlerFactoryInterface
{
    /**
     * @var StripeApiFactory
     */
    private $stripeApiFactory;

    /**
     * @var StripeOrderTransactionService
     */
    private $stripeOrderTransactionService;

    /**
     * @var IdempotentOrderTransactionStateHandler
     */
    private $idempotentOrderTransactionStateHandler;

    /**
     * @var StripeCustomerService
     */
    private $stripeCustomerService;

    /**
     * @var StripePaymentMethodSettings
     */
    private $stripePaymentMethodSettings;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        StripeApiFactory $stripeApiFactory,
        StripeOrderTransactionService $stripeOrderTransactionService,
        IdempotentOrderTransactionStateHandler $idempotentOrderTransactionStateHandler,
        StripeCustomerService $stripeCustomerService,
        StripePaymentMethodSettings $stripePaymentMethodSettings,
        Container $container
    ) {
        $this->stripeApiFactory = $stripeApiFactory;
        $this->stripeOrderTransactionService = $stripeOrderTransactionService;
        $this->idempotentOrderTransactionStateHandler = $idempotentOrderTransactionStateHandler;
        $this->stripeCustomerService = $stripeCustomerService;
        $this->stripePaymentMethodSettings = $stripePaymentMethodSettings;
        $this->container = $container;
    }

    public function createPaymentIntentPaymentHandler(
        PaymentIntentPaymentConfigurator $paymentIntentPaymentConfigurator
    ): StripePaymentHandlerInterface {
        $service = new PaymentIntentHandlerService(
            $this->stripeApiFactory,
            $this->stripeOrderTransactionService,
            $this->idempotentOrderTransactionStateHandler,
            $paymentIntentPaymentConfigurator,
            $this->stripeCustomerService,
            $this->stripePaymentMethodSettings
        );
        $version = $this->container->getParameter('kernel.shopware_version');
        if (version_compare($version, '6.7.0', '>=')) {
            return new PaymentIntentPaymentHandler67($service);
        } else {
            return new PaymentIntentPaymentHandler66($service);
        }
    }
}
