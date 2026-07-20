<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1779000100BundleCandidate extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1779000100;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('CREATE TABLE IF NOT EXISTS `bookbytes_bundle_candidate` (
            `id` BINARY(16) NOT NULL,
            `product_id` BINARY(16) NOT NULL,
            `related_product_id` BINARY(16) NOT NULL,
            `score` DOUBLE NOT NULL,
            `source` VARCHAR(64) NOT NULL DEFAULT "hybrid",
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk.bookbytes_bundle_candidate.product_id` FOREIGN KEY (`product_id`)
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.bookbytes_bundle_candidate.related_product_id` FOREIGN KEY (`related_product_id`)
                REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE KEY `uniq.bookbytes_bundle_candidate.pair` (`product_id`, `related_product_id`),
            KEY `idx.bookbytes_bundle_candidate.product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
