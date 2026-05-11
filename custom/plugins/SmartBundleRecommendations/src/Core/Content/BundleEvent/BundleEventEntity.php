<?php declare(strict_types=1);

namespace SmartBundleRecommendations\Core\Content\BundleEvent;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class BundleEventEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;
    protected ?string $relatedProductId = null;
    protected string $eventType;
    protected ?string $salesChannelId = null;
    protected ?string $customerId = null;
    protected ?array $payload = null;
    protected ?ProductEntity $product = null;
    protected ?ProductEntity $relatedProduct = null;
    protected ?SalesChannelEntity $salesChannel = null;
    protected ?CustomerEntity $customer = null;

    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }

    public function getRelatedProductId(): ?string { return $this->relatedProductId; }
    public function setRelatedProductId(?string $relatedProductId): void { $this->relatedProductId = $relatedProductId; }

    public function getEventType(): string { return $this->eventType; }
    public function setEventType(string $eventType): void { $this->eventType = $eventType; }

    public function getSalesChannelId(): ?string { return $this->salesChannelId; }
    public function setSalesChannelId(?string $salesChannelId): void { $this->salesChannelId = $salesChannelId; }

    public function getCustomerId(): ?string { return $this->customerId; }
    public function setCustomerId(?string $customerId): void { $this->customerId = $customerId; }

    public function getPayload(): ?array { return $this->payload; }
    public function setPayload(?array $payload): void { $this->payload = $payload; }

    public function getProduct(): ?ProductEntity { return $this->product; }
    public function setProduct(?ProductEntity $product): void { $this->product = $product; }

    public function getRelatedProduct(): ?ProductEntity { return $this->relatedProduct; }
    public function setRelatedProduct(?ProductEntity $product): void { $this->relatedProduct = $product; }

    public function getSalesChannel(): ?SalesChannelEntity { return $this->salesChannel; }
    public function setSalesChannel(?SalesChannelEntity $salesChannel): void { $this->salesChannel = $salesChannel; }

    public function getCustomer(): ?CustomerEntity { return $this->customer; }
    public function setCustomer(?CustomerEntity $customer): void { $this->customer = $customer; }
}
