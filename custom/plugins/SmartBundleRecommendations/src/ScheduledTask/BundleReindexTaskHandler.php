<?php declare(strict_types=1);

namespace SmartBundleRecommendations\ScheduledTask;

use SmartBundleRecommendations\Service\BundleIndexer;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class BundleReindexTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        protected \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $scheduledTaskRepository,
        private readonly BundleIndexer $bundleIndexer
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public static function getHandledMessages(): iterable
    {
        return [BundleReindexTask::class];
    }

    public function run(): void
    {
        $this->bundleIndexer->rebuild();
    }
}
