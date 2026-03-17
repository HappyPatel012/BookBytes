<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\PaymentMethods\Card\Subscriber;

use Shopware\Core\Framework\Struct\Struct;

class CreditCardPageExtension extends Struct
{
    public const PAGE_EXTENSION_NAME = 'stripePaymentCreditCard';

    protected bool $isSavingCreditCardsAllowed = false;
    protected array $availableCards = [];
    protected ?array $selectedCard = null;

    public function isSavingCreditCardsAllowed(): ?bool
    {
        return $this->isSavingCreditCardsAllowed;
    }

    public function getAvailableCards(): array
    {
        return $this->availableCards;
    }

    public function getSelectedCard(): ?array
    {
        return $this->selectedCard;
    }
}
