<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Payment\PaymentIntentPaymentHandler;

use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\CustomerCanceledAsyncPaymentException;
use Shopware\Core\Checkout\Payment\Exception\InvalidTransactionException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Stripe\ShopwarePayment\Payment\StripePaymentHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentIntentPaymentHandler66 implements AsynchronousPaymentHandlerInterface, StripePaymentHandlerInterface
{

    /**
     * @var PaymentIntentHandlerService
     */
    private $paymentIntentHandlerService;

    public function __construct(
        PaymentIntentHandlerService $paymentIntentHandlerService,
    ) {
       $this->paymentIntentHandlerService = $paymentIntentHandlerService;
    }

    public function pay(
        AsyncPaymentTransactionStruct $transaction,
        RequestDataBag $dataBag,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {

        return $this->paymentIntentHandlerService->pay(
            $dataBag,
            $transaction->getOrderTransaction()->getId(),
            $transaction->getReturnUrl(),
            $salesChannelContext->getContext()
        );
    }

    public function finalize(
        AsyncPaymentTransactionStruct $transaction,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): void {
        $this->paymentIntentHandlerService->finalize(
            $request,
            $transaction->getOrderTransaction()->getId(),
            $salesChannelContext->getContext()
        );
    }
}
