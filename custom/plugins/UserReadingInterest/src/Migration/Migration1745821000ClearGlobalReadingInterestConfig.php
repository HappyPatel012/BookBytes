<?php declare(strict_types=1);

namespace UserReadingInterest\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1745821000ClearGlobalReadingInterestConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1745821000;
    }

    public function update(Connection $connection): void
    {
        // Ensure channel-only behavior by removing old global fallback setting if present.
        $connection->executeStatement(
            'DELETE FROM `system_config` WHERE `configuration_key` = :configKey AND `sales_channel_id` IS NULL',
            ['configKey' => 'UserReadingInterest.config.enabled']
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
