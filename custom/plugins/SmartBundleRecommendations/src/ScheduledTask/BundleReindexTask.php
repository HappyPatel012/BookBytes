<?php declare(strict_types=1);

namespace SmartBundleRecommendations\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class BundleReindexTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'bookbytes.smart_bundle_reindex';
    }

    public static function getDefaultInterval(): int
    {
        return 86400;
    }
}
