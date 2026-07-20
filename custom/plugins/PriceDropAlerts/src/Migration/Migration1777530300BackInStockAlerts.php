<?php declare(strict_types=1);

namespace PriceDropAlerts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1777530300BackInStockAlerts extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1777530300;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `bookbytes_back_in_stock_alert` (
            `id` BINARY(16) NOT NULL,
            `customer_id` BINARY(16) NOT NULL,
            `product_id` BINARY(16) NOT NULL,
            `sales_channel_id` BINARY(16) NOT NULL,
            `last_known_stock` INT NOT NULL DEFAULT 0,
            `last_notified_at` DATETIME(3) NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq.bookbytes_back_in_stock_alert.customer_product_channel` (`customer_id`, `product_id`, `sales_channel_id`),
            CONSTRAINT `fk.bookbytes_back_in_stock_alert.customer_id` FOREIGN KEY (`customer_id`)
                REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_back_in_stock_alert.product_id` FOREIGN KEY (`product_id`)
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_back_in_stock_alert.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
