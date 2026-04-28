<?php declare(strict_types=1);

namespace StudentCourse\Core\Content\Course;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void         add(CourseEntity $entity)
 * @method void         set(string $key, CourseEntity $entity)
 * @method CourseEntity[]    getIterator()
 * @method CourseEntity[]    getElements()
 * @method CourseEntity|null get(string $key)
 * @method CourseEntity|null first()
 * @method CourseEntity|null last()
 */
class CourseCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CourseEntity::class;
    }
}
