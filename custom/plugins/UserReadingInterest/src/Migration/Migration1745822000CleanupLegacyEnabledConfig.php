<?php declare(strict_types=1);

namespace UserReadingInterest\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1745822000CleanupLegacyEnabledConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1745822000;
    }

    public function update(Connection $connection): void
    {
        // Remove legacy config key to avoid confusion with old inheritance behavior.
        $connection->executeStatement(
            'DELETE FROM `system_config` WHERE `configuration_key` = :configKey',
            ['configKey' => 'UserReadingInterest.config.enabled']
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
