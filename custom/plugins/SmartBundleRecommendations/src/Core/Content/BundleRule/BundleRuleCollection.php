<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleRule;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class BundleRuleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BundleRuleEntity::class;
    }
}
