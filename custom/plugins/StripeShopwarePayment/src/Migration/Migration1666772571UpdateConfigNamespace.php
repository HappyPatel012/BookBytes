<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Stripe\ShopwarePayment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1666772571UpdateConfigNamespace extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1666772571;
    }

    public function update(Connection $connection): void
    {
        $this->updateConfigNamespace($connection, 'stripeSecretKey');
        $this->updateConfigNamespace($connection, 'stripePublicKey');
        $this->updateConfigNamespace($connection, 'stripeAccountCountryIso');
        $this->updateConfigNamespace($connection, 'stripeWebhookSecret');
        $this->updateConfigNamespace($connection, 'stripeWebhookId');
        $this->updateConfigNamespace($connection, 'sendStripeChargeEmails');
        $this->updateConfigNamespace($connection, 'statementDescriptor');
        $this->updateConfigNamespace($connection, 'isSavingCreditCardsAllowed');
        $this->updateConfigNamespace($connection, 'isSavingSepaBankAccountsAllowed');
        $this->updateConfigNamespace($connection, 'shouldShowPaymentProviderLogos');
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function updateConfigNamespace($connection, $configKey): void
    {
        $connection->executeStatement(
            'UPDATE system_config
            SET configuration_key = CONCAT("StripeShopwarePayment.sales-channel-plugin-config.", :configKey)
            WHERE configuration_key = CONCAT("StripeShopwarePayment.config.", :configKey)',
            [
                'configKey' => $configKey,
            ],
        );
    }
}
