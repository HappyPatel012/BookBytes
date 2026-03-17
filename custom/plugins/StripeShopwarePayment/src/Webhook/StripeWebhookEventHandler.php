<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Webhook;

use OutOfBoundsException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Stripe\Charge;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\ShopwarePayment\OrderTransactionLocking\OrderTransactionLockingService;
use Stripe\ShopwarePayment\Payment\IdempotentOrderTransactionStateHandler;
use Stripe\ShopwarePayment\Payment\StripeCustomerService;
use Stripe\ShopwarePayment\Payment\StripeOrderTransactionService;
use Stripe\ShopwarePayment\Payment\StripePaymentContext;
use Stripe\ShopwarePayment\StripeApi\StripeApiFactory;
use Stripe\Source;

class StripeWebhookEventHandler
{
    /**
     * @var EntityRepository
     */
    private $orderTransactionRepository;

    /**
     * @var StripeOrderTransactionService
     */
    private $stripeOrderTransactionService;

    /**
     * @var StripeApiFactory
     */
    private $stripeApiFactory;

    /**
     * @var StripeCustomerService
     */
    private $stripeCustomerService;

    /**
     * @var IdempotentOrderTransactionStateHandler
     */
    private $idempotentOrderTransactionStateHandler;

    /**
     * @var OrderTransactionLockingService
     */
    private $orderTransactionLockingService;

    public function __construct(
        EntityRepository $orderTransactionRepository,
        IdempotentOrderTransactionStateHandler $idempotentOrderTransactionStateHandler,
        StripeOrderTransactionService $stripeOrderTransactionService,
        StripeCustomerService $stripeCustomerService,
        StripeApiFactory $stripeApiFactory,
        OrderTransactionLockingService $orderTransactionLockingService
    ) {
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->idempotentOrderTransactionStateHandler = $idempotentOrderTransactionStateHandler;
        $this->stripeOrderTransactionService = $stripeOrderTransactionService;
        $this->stripeCustomerService = $stripeCustomerService;
        $this->stripeApiFactory = $stripeApiFactory;
        $this->orderTransactionLockingService = $orderTransactionLockingService;
    }

    public function handleChargeSuccessfulEvent(Event $event, Context $context): void
    {
        $charge = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForCharge($charge, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForCharge($charge);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_PAID) {
            // Already paid, nothing to do
            return;
        }
        $this->idempotentOrderTransactionStateHandler->paid(
            $orderTransaction->getId(),
            $context,
        );
    }

    public function handleChargeCanceledEvent(Event $event, Context $context): void
    {
        $charge = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForCharge($charge, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForCharge($charge);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_CANCELLED) {
            // Already cancelled, nothing to do
            return;
        }
        $this->idempotentOrderTransactionStateHandler->cancel(
            $orderTransaction->getId(),
            $context,
        );
    }

    public function handleChargeFailedEvent(Event $event, Context $context): void
    {
        $charge = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForCharge($charge, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForCharge($charge);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_FAILED) {
            // Already failed, nothing to do
            return;
        }
        $this->idempotentOrderTransactionStateHandler->fail(
            $orderTransaction->getId(),
            $context,
        );
    }

    public function handlePaymentIntentSuccessfulEvent(Event $event, Context $context): void
    {
        $paymentIntent = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForPaymentIntent($paymentIntent, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForPaymentIntent($paymentIntent);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_PAID) {
            // Already paid, nothing to do
            return;
        }

        // Persist charge id and append order number to the payment intent
        $this->stripeOrderTransactionService->saveStripeChargeOnOrderTransaction(
            $orderTransaction,
            $context,
            $paymentIntent->charges->data[0],
        );
        $this->idempotentOrderTransactionStateHandler->paid(
            $orderTransaction->getId(),
            $context,
        );
    }

    public function handlePaymentIntentCanceledEvent(Event $event, Context $context): void
    {
        $paymentIntent = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForPaymentIntent($paymentIntent, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForPaymentIntent($paymentIntent);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_CANCELLED) {
            // Already cancelled, nothing to do
            return;
        }
        $this->idempotentOrderTransactionStateHandler->cancel(
            $orderTransaction->getId(),
            $context,
        );
    }

    public function handlePaymentIntentFailedEvent(Event $event, Context $context): void
    {
        $paymentIntent = $event->data->object;
        $orderTransaction = $this->getOrderTransactionForPaymentIntent($paymentIntent, $context);
        if (!$orderTransaction) {
            throw WebhookException::orderTransactionNotFoundForPaymentIntent($paymentIntent);
        }

        if ($orderTransaction->getStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_FAILED) {
            // Already failed, nothing to do
            return;
        }
        $this->idempotentOrderTransactionStateHandler->fail(
            $orderTransaction->getId(),
            $context,
        );
    }

    private function getOrderTransactionForSourceChargeableEvent(
        string $orderTransactionId,
        Context $context
    ): ?OrderTransactionEntity {
        $criteria = new Criteria([$orderTransactionId]);
        $criteria->addAssociations([
            'order',
            'order.salesChannel',
            'order.orderCustomer.customer',
            'paymentMethod',
            'stateMachineState',
        ]);
        $result = $this->orderTransactionRepository->search($criteria, $context);

        if ($result->getTotal() === 0) {
            return null;
        }

        return $result->first();
    }

    private function getOrderTransactionForSource(Source $source, Context $context): ?OrderTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('stateMachineState');
        $criteria->addFilter(new EqualsFilter(
            'customFields.stripe_payment_context.payment.source_id',
            $source->id,
        ));
        $result = $this->orderTransactionRepository->search($criteria, $context);

        if ($result->getTotal() === 0) {
            return null;
        }

        return $result->first();
    }

    private function getOrderTransactionForCharge(Charge $charge, Context $context): ?OrderTransactionEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'order',
            'stateMachineState',
        ]);
        $criteria->addFilter(new EqualsFilter(
            'customFields.stripe_payment_context.payment.charge_id',
            $charge->id,
        ));
        $result = $this->orderTransactionRepository->search($criteria, $context);

        if ($result->getTotal() === 0) {
            return null;
        }

        return $result->first();
    }

    private function getOrderTransactionForPaymentIntent(
        PaymentIntent $paymentIntent,
        Context $context
    ): ?OrderTransactionEntity {
        $criteria = new Criteria();
        $criteria->addAssociations([
            'order',
            'stateMachineState',
        ]);
        $criteria->addFilter(
            new EqualsFilter(
                'customFields.stripe_payment_context.payment.payment_intent_id',
                $paymentIntent->id,
            ),
        );
        $result = $this->orderTransactionRepository->search($criteria, $context);

        if ($result->getTotal() === 0) {
            return null;
        }

        return $result->first();
    }
}
