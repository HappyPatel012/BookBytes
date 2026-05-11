<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1779000200BundleEvent extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1779000200;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `bookbytes_bundle_event` (
            `id` BINARY(16) NOT NULL,
            `product_id` BINARY(16) NOT NULL,
            `related_product_id` BINARY(16) NULL,
            `event_type` VARCHAR(32) NOT NULL,
            `sales_channel_id` BINARY(16) NULL,
            `customer_id` BINARY(16) NULL,
            `payload` JSON NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk.bookbytes_bundle_event.product_id` FOREIGN KEY (`product_id`)
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_bundle_event.related_product_id` FOREIGN KEY (`related_product_id`)
                REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_bundle_event.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_bundle_event.customer_id` FOREIGN KEY (`customer_id`)
                REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            KEY `idx.bookbytes_bundle_event.product_id` (`product_id`),
            KEY `idx.bookbytes_bundle_event.event_type` (`event_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
