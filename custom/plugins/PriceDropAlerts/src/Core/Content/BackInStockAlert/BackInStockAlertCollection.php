<?php declare(strict_types=1);

namespace PriceDropAlerts\Core\Content\BackInStockAlert;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<BackInStockAlertEntity>
 */
class BackInStockAlertCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return BackInStockAlertEntity::class;
    }
}
