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

use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\CustomerCanceledAsyncPaymentException;
use Shopware\Core\Checkout\Payment\Exception\InvalidTransactionException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Stripe\ShopwarePayment\Payment\StripePaymentHandlerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentIntentPaymentHandler67 extends AbstractPaymentHandler implements StripePaymentHandlerInterface
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
        Request $request,
        PaymentTransactionStruct $transaction,
        Context $context,
        ?Struct $validateStruct
    ): RedirectResponse {
       return $this->paymentIntentHandlerService->pay(
           new RequestDataBag($request->request->all()),
           $transaction->getOrderTransactionId(),
           $transaction->getReturnUrl(),
           $context
       );
    }

    public function finalize(
        Request $request,
        PaymentTransactionStruct $transaction,
        Context $context
    ): void {
       $this->paymentIntentHandlerService->finalize(
           $request,
           $transaction->getOrderTransactionId(),
           $context
       );
    }

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        return false;
    }
}
