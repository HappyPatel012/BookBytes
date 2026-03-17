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

use Psr\Log\LoggerInterface;
use Stripe\ShopwarePayment\Logging\AsynchronousPaymentHandlerLoggerDecorator66;
use Stripe\ShopwarePayment\Logging\AsynchronousPaymentHandlerLoggerDecorator67;
use Stripe\ShopwarePayment\Payment\PaymentIntentPaymentConfig\PaymentIntentPaymentConfigurator;
use Stripe\ShopwarePayment\Payment\StripePaymentHandlerInterface;
use Symfony\Component\DependencyInjection\Container;

class PaymentHandlerFactoryLoggingDecorator implements PaymentHandlerFactoryInterface
{
    /**
     * @var PaymentHandlerFactoryInterface
     */
    private $decoratedPaymentHandlerFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Container
     */
    private $container;

    public function __construct(
        Container $container,
        PaymentHandlerFactoryInterface $decoratedPaymentHandlerFactory,
        LoggerInterface $logger
    ) {
        $this->decoratedPaymentHandlerFactory = $decoratedPaymentHandlerFactory;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function createPaymentIntentPaymentHandler(
        PaymentIntentPaymentConfigurator $paymentIntentPaymentConfigurator
    ): StripePaymentHandlerInterface {
        $paymentIntentHandler = $this->decoratedPaymentHandlerFactory->createPaymentIntentPaymentHandler(
            $paymentIntentPaymentConfigurator,
        );

        $version = $this->container->getParameter('kernel.shopware_version');
        if (version_compare($version, '6.7.0', '>=')) {
            return new AsynchronousPaymentHandlerLoggerDecorator67($paymentIntentHandler, $this->logger);
        } else {
            return new AsynchronousPaymentHandlerLoggerDecorator66($paymentIntentHandler, $this->logger);
        }
    }
}
