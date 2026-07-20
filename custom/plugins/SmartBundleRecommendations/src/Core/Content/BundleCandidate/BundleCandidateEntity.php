<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleCandidate;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class BundleCandidateEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;
    protected string $relatedProductId;
    protected float $score;
    protected string $source;
    protected ?ProductEntity $product = null;
    protected ?ProductEntity $relatedProduct = null;

    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }

    public function getRelatedProductId(): string { return $this->relatedProductId; }
    public function setRelatedProductId(string $relatedProductId): void { $this->relatedProductId = $relatedProductId; }

    public function getScore(): float { return $this->score; }
    public function setScore(float $score): void { $this->score = $score; }

    public function getSource(): string { return $this->source; }
    public function setSource(string $source): void { $this->source = $source; }

    public function getProduct(): ?ProductEntity { return $this->product; }
    public function setProduct(?ProductEntity $product): void { $this->product = $product; }

    public function getRelatedProduct(): ?ProductEntity { return $this->relatedProduct; }
    public function setRelatedProduct(?ProductEntity $product): void { $this->relatedProduct = $product; }
}
