<?php declare(strict_types=1);

namespace UserReadingInterest\Core\Content\ReadingInterest;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                  add(ReadingInterestEntity $entity)
 * @method void                  set(string $key, ReadingInterestEntity $entity)
 * @method ReadingInterestEntity[] getIterator()
 * @method ReadingInterestEntity[] getElements()
 * @method ReadingInterestEntity|null get(string $key)
 * @method ReadingInterestEntity|null first()
 * @method ReadingInterestEntity|null last()
 */
class ReadingInterestCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ReadingInterestEntity::class;
    }
}
