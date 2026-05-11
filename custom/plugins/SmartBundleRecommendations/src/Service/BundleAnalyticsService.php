<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Service;

use Doctrine\DBAL\Connection;

class BundleAnalyticsService
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function getSummary(): array
    {
        $impressions = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM bookbytes_bundle_event WHERE event_type = 'impression'");
        $clicks = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM bookbytes_bundle_event WHERE event_type = 'click'");
        $adds = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM bookbytes_bundle_event WHERE event_type = 'add'");
        $purchases = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM bookbytes_bundle_event WHERE event_type = 'purchase'");

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0;
        $addRate = $clicks > 0 ? round(($adds / $clicks) * 100, 2) : 0.0;

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'adds' => $adds,
            'purchases' => $purchases,
            'ctr' => $ctr,
            'addToCartRate' => $addRate,
            'bundleRevenue' => 0.0,
        ];
    }
}
