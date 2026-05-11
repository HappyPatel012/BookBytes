<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleCandidate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(BundleCandidateEntity $entity)
 * @method void                   set(string $key, BundleCandidateEntity $entity)
 * @method BundleCandidateEntity[] getIterator()
 * @method BundleCandidateEntity[] getElements()
 * @method BundleCandidateEntity|null get(string $key)
 * @method BundleCandidateEntity|null first()
 * @method BundleCandidateEntity|null last()
 */
class BundleCandidateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BundleCandidateEntity::class;
    }
}
