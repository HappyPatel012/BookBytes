<?php declare(strict_types=1);

namespace PriceDropAlerts\Core\Content\BackInStockAlert;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class BackInStockAlertEntity extends Entity
{
    use EntityIdTrait;

    protected string $customerId;
    protected string $productId;
    protected string $salesChannelId;
    protected int $lastKnownStock;
    protected ?\DateTimeInterface $lastNotifiedAt = null;
    protected bool $active;
    protected ?CustomerEntity $customer = null;
    protected ?ProductEntity $product = null;
    protected ?SalesChannelEntity $salesChannel = null;

    public function getCustomerId(): string { return $this->customerId; }
    public function setCustomerId(string $customerId): void { $this->customerId = $customerId; }
    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }
    public function getSalesChannelId(): string { return $this->salesChannelId; }
    public function setSalesChannelId(string $salesChannelId): void { $this->salesChannelId = $salesChannelId; }
    public function getLastKnownStock(): int { return $this->lastKnownStock; }
    public function setLastKnownStock(int $lastKnownStock): void { $this->lastKnownStock = $lastKnownStock; }
    public function getLastNotifiedAt(): ?\DateTimeInterface { return $this->lastNotifiedAt; }
    public function setLastNotifiedAt(?\DateTimeInterface $lastNotifiedAt): void { $this->lastNotifiedAt = $lastNotifiedAt; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): void { $this->active = $active; }
    public function getCustomer(): ?CustomerEntity { return $this->customer; }
    public function setCustomer(?CustomerEntity $customer): void { $this->customer = $customer; }
    public function getProduct(): ?ProductEntity { return $this->product; }
    public function setProduct(?ProductEntity $product): void { $this->product = $product; }
    public function getSalesChannel(): ?SalesChannelEntity { return $this->salesChannel; }
    public function setSalesChannel(?SalesChannelEntity $salesChannel): void { $this->salesChannel = $salesChannel; }
}
