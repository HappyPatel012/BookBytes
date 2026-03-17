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

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Payment\PaymentException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class StripePaymentIntentPaymentProcessException
{
    public static function paymentIntentCreationFailed(
        OrderTransactionEntity $orderTransaction,
        ApiErrorException $apiException
    ) {
        return PaymentException::asyncProcessInterrupted(
            $orderTransaction->getId(),
            sprintf(
                "The Stripe payment intent could not be created. Additional information:\n%s",
                $apiException->getMessage(),
            ),
        );
    }

    public static function paymentIntentNextActionMissing(
        OrderTransactionEntity $orderTransaction,
        PaymentIntent $paymentIntent
    ) {
        return PaymentException::asyncProcessInterrupted(
            $orderTransaction->getId(),
            sprintf(
                'The Stripe payment intent %s requires additional authentication but is missing the next action.',
                $paymentIntent->id,
            ),
        );
    }

    public static function paymentIntentNextActionInvalid(
        OrderTransactionEntity $orderTransaction,
        PaymentIntent $paymentIntent
    ) {
        return PaymentException::asyncProcessInterrupted(
            $orderTransaction->getId(),
            sprintf(
                'The Stripe payment intent %s requires an authentication action that is invalid: %s',
                $paymentIntent->id,
                $paymentIntent->next_action->type,
            ),
        );
    }

    public static function unableToProcessPaymentIntent(
        OrderTransactionEntity $orderTransaction,
        PaymentIntent $paymentIntent
    ) {
        return PaymentException::asyncProcessInterrupted(
            $orderTransaction->getId(),
            sprintf(
                'Unable to process the Stripe payment intent %s. Payment intent status: %s',
                $paymentIntent->id,
                $paymentIntent->status,
            ),
        );
    }
}
