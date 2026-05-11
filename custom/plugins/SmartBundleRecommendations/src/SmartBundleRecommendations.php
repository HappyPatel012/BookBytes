<?php declare(strict_types=1);

namespace SmartBundleRecommendations;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class SmartBundleRecommendations extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        $this->ensureDefaultRule($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);

            return;
        }

        $connection = $this->container->get('Doctrine\\DBAL\\Connection');
        $connection->executeStatement('DROP TABLE IF EXISTS `bookbytes_bundle_event`');
        $connection->executeStatement('DROP TABLE IF EXISTS `bookbytes_bundle_candidate`');
        $connection->executeStatement('DROP TABLE IF EXISTS `bookbytes_bundle_rule`');

        parent::uninstall($uninstallContext);
    }

    private function ensureDefaultRule(Context $context): void
    {
        /** @var \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository $repository */
        $repository = $this->container->get('bookbytes_bundle_rule.repository');

        if ($repository->searchIds(new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria(), $context)->getTotal() > 0) {
            return;
        }

        $repository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => 'Default Strict Rule',
                'active' => true,
                'filters' => [
                    'minStock' => 1,
                    'requireActive' => true,
                    'requireVisibility' => true,
                    'excludedProductIds' => [],
                ],
                'weights' => [
                    'copurchase' => 0.6,
                    'category' => 0.3,
                    'priceDistance' => 0.1,
                ],
            ],
        ], $context);
    }
}
