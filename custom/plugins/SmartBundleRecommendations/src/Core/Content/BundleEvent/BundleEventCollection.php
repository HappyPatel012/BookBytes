<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleEvent;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(BundleEventEntity $entity)
 * @method void                set(string $key, BundleEventEntity $entity)
 * @method BundleEventEntity[] getIterator()
 * @method BundleEventEntity[] getElements()
 * @method BundleEventEntity|null get(string $key)
 * @method BundleEventEntity|null first()
 * @method BundleEventEntity|null last()
 */
class BundleEventCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BundleEventEntity::class;
    }
}
