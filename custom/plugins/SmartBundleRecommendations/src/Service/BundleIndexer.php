<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Service;

use Doctrine\DBAL\Connection;

class BundleIndexer
{
    public function __construct(
        private readonly Connection $connection,
        private readonly BundleRecommendationService $recommendationService
    ) {
    }

    public function rebuild(): void
    {
        $this->connection->executeStatement('DELETE FROM `bookbytes_bundle_candidate`');

        $sql = <<<'SQL'
INSERT INTO `bookbytes_bundle_candidate` (`id`, `product_id`, `related_product_id`, `score`, `source`, `created_at`)
SELECT
    UNHEX(REPLACE(UUID(), '-', '')) AS id,
    a.product_id,
    b.product_id AS related_product_id,
    COUNT(*) * 1.0 AS score,
    'copurchase' AS source,
    NOW(3) AS created_at
FROM order_line_item a
INNER JOIN `order` o ON o.id = a.order_id
INNER JOIN order_line_item b ON b.order_id = a.order_id AND b.product_id <> a.product_id
WHERE a.product_id IS NOT NULL
  AND b.product_id IS NOT NULL
  AND o.order_date_time >= DATE_SUB(NOW(), INTERVAL 180 DAY)
GROUP BY a.product_id, b.product_id
HAVING COUNT(*) > 0
SQL;

        $this->connection->executeStatement($sql);
        $this->recommendationService->invalidateCache();
    }
}
