<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleRule;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class BundleRuleEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;
    protected bool $active;
    protected ?array $filters = null;
    protected ?array $weights = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters): void
    {
        $this->filters = $filters;
    }

    public function getWeights(): ?array
    {
        return $this->weights;
    }

    public function setWeights(?array $weights): void
    {
        $this->weights = $weights;
    }
}
