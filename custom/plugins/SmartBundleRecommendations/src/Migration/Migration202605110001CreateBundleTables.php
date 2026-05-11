<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration202605110001CreateBundleTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 202605110001;
    }

    public function update(Connection $connection): void
    {
        $columns = $connection->createSchemaManager()->listTableColumns('bookbytes_bundle_rule');

        if (isset($columns['max_items'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `max_items`');
        }
        if (isset($columns['min_stock'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `min_stock`');
        }
        if (isset($columns['max_price_delta_percent'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `max_price_delta_percent`');
        }
        if (isset($columns['score_weight_copurchase'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `score_weight_copurchase`');
        }
        if (isset($columns['score_weight_category'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `score_weight_category`');
        }
        if (isset($columns['score_weight_price'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `score_weight_price`');
        }
        if (isset($columns['excluded_category_ids'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `excluded_category_ids`');
        }
        if (isset($columns['excluded_product_ids'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` DROP COLUMN `excluded_product_ids`');
        }

        if (!isset($columns['filters'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` ADD COLUMN `filters` JSON NULL AFTER `active`');
        }
        if (!isset($columns['weights'])) {
            $connection->executeStatement('ALTER TABLE `bookbytes_bundle_rule` ADD COLUMN `weights` JSON NULL AFTER `filters`');
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
