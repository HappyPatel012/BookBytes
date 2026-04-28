<?php declare(strict_types=1);

namespace StudentCourse\Core\Content\Course;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CourseEntity extends Entity
{
    protected string $title;

    protected string $instructor;

    protected string $level;

    protected ?string $description = null;

    protected ?\DateTimeInterface $startDate = null;

    protected bool $isActive = true;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getInstructor(): string
    {
        return $this->instructor;
    }

    public function setInstructor(string $instructor): void
    {
        $this->instructor = $instructor;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
