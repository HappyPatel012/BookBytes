<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1779000000BundleRule extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1779000000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `bookbytes_bundle_rule` (
            `id` BINARY(16) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `max_items` INT NOT NULL DEFAULT 3,
            `min_stock` INT NOT NULL DEFAULT 1,
            `max_price_delta_percent` DOUBLE NOT NULL DEFAULT 40,
            `score_weight_copurchase` DOUBLE NOT NULL DEFAULT 0.6,
            `score_weight_category` DOUBLE NOT NULL DEFAULT 0.3,
            `score_weight_price` DOUBLE NOT NULL DEFAULT 0.1,
            `excluded_category_ids` JSON NULL,
            `excluded_product_ids` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
