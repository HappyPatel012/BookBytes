<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Logging;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Stripe\ShopwarePayment\Payment\StripePaymentHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AsynchronousPaymentHandlerLoggerDecorator67 extends AbstractPaymentHandler implements StripePaymentHandlerInterface
{
    /**
     * @var AbstractPaymentHandler
     */
    private $decoratedPaymentHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StripePaymentHandlerInterface $decoratedPaymentHandler,
        LoggerInterface $logger
    ) {
        $this->decoratedPaymentHandler = $decoratedPaymentHandler;
        $this->logger = $logger;
    }

    public function pay(
        Request $request,
        PaymentTransactionStruct $transaction,
        Context $context,
        ?Struct $validateStruct
    ): RedirectResponse {
        try {
            return $this->decoratedPaymentHandler->pay($request, $transaction, $context, $validateStruct);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'from' => $exception->getFile().':'.$exception->getLine(),
                'orderTransactionId' => $transaction->getOrderTransactionId(),
                'salesChannelId' => $context->getSource()->getSalesChannelId(),
            ]);

            throw $exception;
        }
    }

    public function finalize(
        Request $request,
        PaymentTransactionStruct $transaction,
        Context $context
    ): void {
        try {
            $this->decoratedPaymentHandler->finalize($request, $transaction, $context);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'from' => $exception->getFile().':'.$exception->getLine(),
                'orderTransactionId' => $transaction->getOrderTransactionId(),
                'salesChannelId' => $context->getSource()->getSalesChannelId(),
            ]);

            throw $exception;
        }
    }

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return false;
    }
}
