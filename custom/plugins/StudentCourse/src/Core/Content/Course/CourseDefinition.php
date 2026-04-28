<?php declare(strict_types=1);

namespace StudentCourse\Core\Content\Course;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;

class CourseDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'student_course';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return CourseEntity::class;
    }

    public function getCollectionClass(): string
    {
        return CourseCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('title', 'title'))->addFlags(new Required()),
            (new StringField('instructor', 'instructor'))->addFlags(new Required()),
            (new StringField('level', 'level'))->addFlags(new Required()),
            new LongTextField('description', 'description'),
            new DateTimeField('start_date', 'startDate'),
            new BoolField('is_active', 'isActive'),
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
