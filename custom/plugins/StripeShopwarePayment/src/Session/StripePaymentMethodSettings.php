<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Session;

use Symfony\Component\HttpFoundation\RequestStack;

class StripePaymentMethodSettings
{
    private const SESSION_KEY_SELECTED_CARD = 'stripe.shopware_payment.payment_method_settings.selected_card';
    private const SESSION_KEY_SAVE_CARD_FOR_FUTURE_CHECKOUTS = 'stripe.shopware_payment.payment_method_settings.save_card_for_future_checkouts';
    private const SESSION_KEY_SELECTED_SEPA_BANK_ACCOUNT = 'stripe.shopware_payment.payment_method_settings.selected_sepa_bank_account';
    private const SESSION_KEY_SAVE_SEPA_BANK_ACCOUNT_FOR_FUTURE_CHECKOUTS = 'stripe.shopware_payment.payment_method_settings.save_sepa_bank_account_for_future_checkouts';

    public function __construct(private RequestStack $requestStack)
    {
//        $this->getSession();
    }

    public function getSession()
    {
        return $this->requestStack->getCurrentRequest()->getSession();
    }

    public function getSelectedCard(): ?array
    {
        $session = $this->getSession();
        return $session->get(self::SESSION_KEY_SELECTED_CARD);
    }

    public function setSelectedCard(?array $selectedCard): void
    {
        $session = $this->getSession();
        $session->set(self::SESSION_KEY_SELECTED_CARD, $selectedCard);
    }

    public function isSaveCardForFutureCheckouts(): bool
    {
        $session = $this->getSession();
        return $session->get(self::SESSION_KEY_SAVE_CARD_FOR_FUTURE_CHECKOUTS, false);
    }

    public function setIsSaveCardForFutureCheckouts(bool $saveCardForFutureCheckouts): void
    {
        $session = $this->getSession();
        $session->set(self::SESSION_KEY_SAVE_CARD_FOR_FUTURE_CHECKOUTS, $saveCardForFutureCheckouts);
    }

    public function getSelectedSepaBankAccount(): ?array
    {
        $session = $this->getSession();
        return $session->get(self::SESSION_KEY_SELECTED_SEPA_BANK_ACCOUNT);
    }

    public function setSelectedSepaBankAccount(?array $selectedSepaBankAccount): void
    {
        $session = $this->getSession();
        $session->set(self::SESSION_KEY_SELECTED_SEPA_BANK_ACCOUNT, $selectedSepaBankAccount);
    }

    public function isSaveSepaBankAccountForFutureCheckouts(): bool
    {
        $session = $this->getSession();
        return $session->get(self::SESSION_KEY_SAVE_SEPA_BANK_ACCOUNT_FOR_FUTURE_CHECKOUTS, false);
    }

    public function setIsSaveSepaBankAccountForFutureCheckouts(bool $saveCardForFutureCheckouts): void
    {
        $session = $this->getSession();
        $session->set(self::SESSION_KEY_SAVE_SEPA_BANK_ACCOUNT_FOR_FUTURE_CHECKOUTS, $saveCardForFutureCheckouts);
    }

    public function reset(): void
    {
        $session = $this->getSession();
        if ($session->isStarted()) {
            $session->set(self::SESSION_KEY_SELECTED_CARD, null);
            $session->set(self::SESSION_KEY_SAVE_CARD_FOR_FUTURE_CHECKOUTS, false);
            $session->set(self::SESSION_KEY_SELECTED_SEPA_BANK_ACCOUNT, null);
            $session->set(self::SESSION_KEY_SAVE_SEPA_BANK_ACCOUNT_FOR_FUTURE_CHECKOUTS, false);
        }
    }
}
