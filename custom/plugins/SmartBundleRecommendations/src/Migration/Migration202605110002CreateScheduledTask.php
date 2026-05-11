<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration202605110002CreateScheduledTask extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 202605110002;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("INSERT IGNORE INTO scheduled_task (`id`, `name`, `scheduled_task_class`, `run_interval`, `status`, `last_execution_time`, `next_execution_time`, `created_at`) VALUES (UNHEX(REPLACE(UUID(), '-', '')), 'bookbytes.smart_bundle_reindex', 'SmartBundleRecommendations\\ScheduledTask\\BundleReindexTask', 86400, 'scheduled', NULL, NOW(3), NOW(3))");
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
