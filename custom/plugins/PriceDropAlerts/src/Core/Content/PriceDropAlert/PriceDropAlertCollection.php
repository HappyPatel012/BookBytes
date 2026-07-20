<?php declare(strict_types=1);

namespace PriceDropAlerts\Core\Content\PriceDropAlert;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<PriceDropAlertEntity>
 */
class PriceDropAlertCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PriceDropAlertEntity::class;
    }
}
